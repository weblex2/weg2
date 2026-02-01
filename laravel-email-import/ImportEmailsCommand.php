<?php

namespace App\Console\Commands;

use App\Services\ImapEmailImportService;
use Illuminate\Console\Command;

class ImportEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'emails:import 
                            {--folder=INBOX : Der IMAP Ordner}
                            {--limit= : Maximale Anzahl E-Mails}
                            {--unread : Nur ungelesene E-Mails}
                            {--from= : Start-Datum (Y-m-d)}
                            {--to= : End-Datum (Y-m-d)}';

    /**
     * The console command description.
     */
    protected $description = 'Importiert E-Mails vom IMAP Server';

    /**
     * Execute the console command.
     */
    public function handle(ImapEmailImportService $service): int
    {
        $this->info('Starte E-Mail Import...');

        try {
            if ($this->option('from') && $this->option('to')) {
                // Import nach Datumsbereich
                $from = new \DateTime($this->option('from'));
                $to = new \DateTime($this->option('to'));
                
                $this->info("Importiere E-Mails von {$from->format('d.m.Y')} bis {$to->format('d.m.Y')}");
                
                $result = $service->importByDateRange(
                    $from,
                    $to,
                    $this->option('folder')
                );
            } else {
                // Normaler Import
                $result = $service->importFromFolder(
                    $this->option('folder'),
                    $this->option('limit') ? (int) $this->option('limit') : null,
                    $this->option('unread')
                );
            }

            $this->info("✓ {$result['imported']} E-Mails erfolgreich importiert");
            
            if ($result['errors'] > 0) {
                $this->warn("⚠ {$result['errors']} Fehler beim Import");
                
                if ($this->option('verbose')) {
                    foreach ($result['error_details'] as $error) {
                        $this->error("  - {$error['message_id']}: {$error['error']}");
                    }
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Fehler beim Import: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
