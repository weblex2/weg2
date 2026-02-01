# Installations-Checkliste

## ✅ Schritt für Schritt

### 1. Packages installieren
```bash
composer require webklex/laravel-imap
composer require php-mime-mail-parser/php-mime-mail-parser
```

### 2. Dateien kopieren
- [ ] `app/Models/Email.php`
- [ ] `app/Models/EmailAttachment.php`
- [ ] `app/Services/ImapEmailImportService.php`
- [ ] `app/Services/EmlFileImportService.php`
- [ ] `app/Http/Controllers/EmailImportController.php`
- [ ] `app/Console/Commands/ImportEmailsCommand.php`
- [ ] `database/migrations/2024_01_31_000001_create_emails_table.php`
- [ ] `resources/views/emails/import.blade.php`
- [ ] `resources/views/emails/show.blade.php`
- [ ] `config/imap.php`

### 3. Routes hinzufügen
Füge in `routes/web.php` hinzu:
```php
use App\Http\Controllers\EmailImportController;

Route::prefix('emails')->group(function () {
    Route::get('/import', [EmailImportController::class, 'index'])->name('emails.import');
    Route::post('/import/imap', [EmailImportController::class, 'importFromImap'])->name('emails.import.imap');
    Route::post('/import/imap/daterange', [EmailImportController::class, 'importByDateRange'])->name('emails.import.daterange');
    Route::post('/import/upload', [EmailImportController::class, 'uploadEmlFiles'])->name('emails.import.upload');
    Route::get('/{email}', [EmailImportController::class, 'show'])->name('emails.show');
    Route::get('/', [EmailImportController::class, 'list'])->name('emails.list');
});
```

### 4. .env konfigurieren
```env
IMAP_HOST=imap.strato.de
IMAP_PORT=993
IMAP_ENCRYPTION=ssl
IMAP_VALIDATE_CERT=true
IMAP_USERNAME=deine@email.de
IMAP_PASSWORD=deinPasswort
IMAP_PROTOCOL=imap
```

### 5. Datenbank einrichten
```bash
php artisan migrate
php artisan storage:link
```

### 6. Ordner-Berechtigungen prüfen
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 7. Testen
```bash
# Web-Interface öffnen
php artisan serve
# Dann: http://localhost:8000/emails/import

# CLI-Test
php artisan emails:import --limit=1
```

## 🎯 Optionale Schritte

### Automatischer Import einrichten

In `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    // Jeden Tag um 8:00 Uhr neue E-Mails importieren
    $schedule->command('emails:import --unread')
             ->dailyAt('08:00');
             
    // Oder jede Stunde
    $schedule->command('emails:import --limit=10 --unread')
             ->hourly();
}
```

Dann Cron aktivieren:
```bash
* * * * * cd /pfad-zu-deinem-projekt && php artisan schedule:run >> /dev/null 2>&1
```

### Queue Worker für große Imports (optional)

1. Job erstellen:
```bash
php artisan make:job ImportEmailsJob
```

2. In `ImportEmailsJob.php`:
```php
public function handle(ImapEmailImportService $service)
{
    $service->importFromFolder('INBOX', 100);
}
```

3. Dispatchen:
```php
ImportEmailsJob::dispatch();
```

4. Worker starten:
```bash
php artisan queue:work
```

## 🧪 Testdaten erstellen (optional)

Factory für Testdaten:
```bash
php artisan make:factory EmailFactory
```

## 📊 Performance-Tipps

1. **Für große Datenmengen**: Nutze Queues
2. **Indizes sind bereits gesetzt**: message_id, from_email, email_date
3. **Anhänge**: Werden separat gespeichert für bessere Performance
4. **Duplikat-Check**: message_id verhindert doppelte Imports

## 🔒 Sicherheits-Check

- [ ] `.env` ist nicht im Git-Repository
- [ ] IMAP-Passwort ist sicher
- [ ] `storage/app/email-attachments` ist nicht öffentlich
- [ ] File-Upload-Validierung ist aktiv
- [ ] CSRF-Protection ist aktiviert
