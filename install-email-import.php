<?php

/**
 * Laravel Email Import - Automatisches Installations-Script
 *
 * Dieses Script kopiert alle benötigten Dateien in dein Laravel-Projekt
 * und führt die notwendigen Befehle aus.
 *
 * VERWENDUNG:
 * 1. Lege dieses Script im ROOT-Verzeichnis deines Laravel-Projekts ab
 * 2. Führe aus: php install-email-import.php
 */

class EmailImportInstaller
{
    private $basePath;
    private $sourcePath;
    private $errors = [];
    private $success = [];

    public function __construct()
    {
        $this->basePath = __DIR__;
        $this->sourcePath = __DIR__ . '/laravel-email-import';
    }

    public function run()
    {
        $this->printHeader();

        if (!$this->checkLaravelProject()) {
            $this->error("❌ Dies scheint kein Laravel-Projekt zu sein!");
            $this->error("Bitte führe das Script im Root-Verzeichnis deines Laravel-Projekts aus.");
            exit(1);
        }

        if (!is_dir($this->sourcePath)) {
            $this->error("❌ Quellordner 'laravel-email-import' nicht gefunden!");
            $this->error("Bitte stelle sicher, dass der Ordner 'laravel-email-import' im gleichen Verzeichnis wie dieses Script liegt.");
            exit(1);
        }

        $this->info("📦 Starte Installation...\n");

        // 1. Verzeichnisse erstellen
        $this->createDirectories();

        // 2. Dateien kopieren
        $this->copyFiles();

        // 3. Routes hinzufügen
        $this->addRoutes();

        // 4. .env aktualisieren
        $this->updateEnv();

        // 5. Zusammenfassung
        $this->printSummary();
    }

    private function checkLaravelProject()
    {
        return file_exists($this->basePath . '/artisan') &&
               file_exists($this->basePath . '/composer.json');
    }

    private function createDirectories()
    {
        $this->info("📁 Erstelle Verzeichnisse...");

        $directories = [
            'app/Services',
            'resources/views/emails',
        ];

        foreach ($directories as $dir) {
            $fullPath = $this->basePath . '/' . $dir;
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
                $this->success("   ✓ Erstellt: $dir");
            } else {
                $this->info("   ↪ Existiert bereits: $dir");
            }
        }
        echo "\n";
    }

    private function copyFiles()
    {
        $this->info("📄 Kopiere Dateien...");

        $files = [
            // Models
            'app/Models/Email.php' => 'app/Models/Email.php',
            'app/Models/EmailAttachment.php' => 'app/Models/EmailAttachment.php',

            // Services
            'app/Services/ImapEmailImportService.php' => 'app/Services/ImapEmailImportService.php',
            'app/Services/EmlFileImportService.php' => 'app/Services/EmlFileImportService.php',

            // Controller
            'app/Http/Controllers/EmailImportController.php' => 'app/Http/Controllers/EmailImportController.php',

            // Console
            'app/Console/Commands/ImportEmailsCommand.php' => 'app/Console/Commands/ImportEmailsCommand.php',

            // Config
            'config/imap.php' => 'config/imap.php',

            // Views
            'resources/views/emails/import.blade.php' => 'resources/views/emails/import.blade.php',
            'resources/views/emails/show.blade.php' => 'resources/views/emails/show.blade.php',

            // Migration
            'database/migrations/2024_01_31_000001_create_emails_table.php' =>
                'database/migrations/' . date('Y_m_d_His') . '_create_emails_table.php',
        ];

        foreach ($files as $source => $destination) {
            $sourcePath = $this->sourcePath . '/' . $source;
            $destPath = $this->basePath . '/' . $destination;

            if (!file_exists($sourcePath)) {
                $this->errors[] = "Quelldatei nicht gefunden: $source";
                continue;
            }

            // Backup erstellen falls Datei existiert
            if (file_exists($destPath)) {
                $backupPath = $destPath . '.backup.' . date('YmdHis');
                copy($destPath, $backupPath);
                $this->info("   ⚠ Backup erstellt: " . basename($destination) . ".backup.*");
            }

            if (copy($sourcePath, $destPath)) {
                $this->success("   ✓ Kopiert: $destination");
            } else {
                $this->errors[] = "Fehler beim Kopieren: $destination";
            }
        }
        echo "\n";
    }

    private function addRoutes()
    {
        $this->info("🛣️  Aktualisiere Routes...");

        $routesFile = $this->basePath . '/routes/web.php';
        $routesContent = file_get_contents($routesFile);

        // Prüfe ob Routes bereits existieren
        if (strpos($routesContent, 'EmailImportController') !== false) {
            $this->info("   ↪ Routes sind bereits vorhanden\n");
            return;
        }

        $routesToAdd = <<<'PHP'


// Email Import Routes
use App\Http\Controllers\EmailImportController;

Route::prefix('emails')->group(function () {
    Route::get('/import', [EmailImportController::class, 'index'])->name('emails.import');
    Route::post('/import/imap', [EmailImportController::class, 'importFromImap'])->name('emails.import.imap');
    Route::post('/import/imap/daterange', [EmailImportController::class, 'importByDateRange'])->name('emails.import.daterange');
    Route::post('/import/upload', [EmailImportController::class, 'uploadEmlFiles'])->name('emails.import.upload');
    Route::get('/{email}', [EmailImportController::class, 'show'])->name('emails.show');
    Route::get('/', [EmailImportController::class, 'list'])->name('emails.list');
});

PHP;

        // Backup erstellen
        $backupFile = $routesFile . '.backup.' . date('YmdHis');
        copy($routesFile, $backupFile);
        $this->info("   ⚠ Backup erstellt: routes/web.php.backup.*");

        // Routes hinzufügen
        file_put_contents($routesFile, $routesContent . $routesToAdd);
        $this->success("   ✓ Routes hinzugefügt\n");
    }

    private function updateEnv()
    {
        $this->info("⚙️  Aktualisiere .env Datei...");

        $envFile = $this->basePath . '/.env';
        $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';

        // Prüfe ob IMAP-Config bereits existiert
        if (strpos($envContent, 'IMAP_HOST') !== false) {
            $this->info("   ↪ IMAP-Konfiguration ist bereits vorhanden\n");
            return;
        }

        $envToAdd = <<<'ENV'


# Email Import - IMAP Configuration (Strato)
IMAP_HOST=imap.strato.de
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
IMAP_VALIDATE_CERT=true
IMAP_USERNAME=deine@email.de
IMAP_PASSWORD=deinPasswort
IMAP_PROTOCOL=imap

ENV;

        // Backup erstellen
        if (file_exists($envFile)) {
            $backupFile = $envFile . '.backup.' . date('YmdHis');
            copy($envFile, $backupFile);
            $this->info("   ⚠ Backup erstellt: .env.backup.*");
        }

        // Konfiguration hinzufügen
        file_put_contents($envFile, $envContent . $envToAdd);
        $this->success("   ✓ IMAP-Konfiguration hinzugefügt");
        $this->info("   ⚠ Bitte aktualisiere die IMAP-Zugangsdaten in der .env Datei!\n");
    }

    private function printSummary()
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "                    INSTALLATIONS-ZUSAMMENFASSUNG               \n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

        if (count($this->errors) > 0) {
            echo "❌ FEHLER:\n";
            foreach ($this->errors as $error) {
                echo "   • $error\n";
            }
            echo "\n";
        }

        echo "✅ Installation abgeschlossen!\n\n";

        echo "📋 NÄCHSTE SCHRITTE:\n\n";
        echo "1. Composer-Packages installieren:\n";
        echo "   composer require webklex/laravel-imap\n";
        echo "   composer require php-mime-mail-parser/php-mime-mail-parser\n\n";

        echo "2. IMAP-Zugangsdaten in .env konfigurieren:\n";
        echo "   IMAP_USERNAME=deine@email.de\n";
        echo "   IMAP_PASSWORD=deinPasswort\n\n";

        echo "3. Migration ausführen:\n";
        echo "   php artisan migrate\n\n";

        echo "4. Storage Link erstellen:\n";
        echo "   php artisan storage:link\n\n";

        echo "5. Cache leeren:\n";
        echo "   php artisan route:clear\n";
        echo "   php artisan config:clear\n\n";

        echo "6. Server starten und testen:\n";
        echo "   php artisan serve\n";
        echo "   Dann öffne: http://localhost:8000/emails/import\n\n";

        echo "═══════════════════════════════════════════════════════════════\n\n";

        echo "📚 Weitere Informationen:\n";
        echo "   • README.md für vollständige Dokumentation\n";
        echo "   • INSTALLATION_CHECKLIST.md für detaillierte Schritte\n\n";

        echo "💡 CLI-Befehle:\n";
        echo "   php artisan emails:import --limit=10\n";
        echo "   php artisan emails:import --unread\n";
        echo "   php artisan emails:import --from=2024-01-01 --to=2024-01-31\n\n";
    }

    private function printHeader()
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "         Laravel Email Import - Automatische Installation       \n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
    }

    private function info($message)
    {
        echo "\033[36m$message\033[0m\n";
    }

    private function success($message)
    {
        echo "\033[32m$message\033[0m\n";
        $this->success[] = $message;
    }

    private function error($message)
    {
        echo "\033[31m$message\033[0m\n";
        $this->errors[] = $message;
    }
}

// Script ausführen
$installer = new EmailImportInstaller();
$installer->run();
