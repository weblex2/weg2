<?php

namespace App\Services;

use App\Models\Email;
use App\Models\EmailAttachment;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImapEmailImportService
{
    protected $client;

    public function __construct()
    {
        $cm = new ClientManager();
        $this->client = $cm->make([
            'host' => config('imap.accounts.default.host'),
            'port' => config('imap.accounts.default.port'),
            'encryption' => config('imap.accounts.default.encryption'),
            'validate_cert' => config('imap.accounts.default.validate_cert'),
            'username' => config('imap.accounts.default.username'),
            'password' => config('imap.accounts.default.password'),
            'protocol' => config('imap.accounts.default.protocol'),
        ]);
    }

    /**
     * Importiert E-Mails aus einem bestimmten Ordner
     */
    public function importFromFolder(string $folderName = 'INBOX', ?int $limit = null, bool $unreadOnly = false, string $from = null): array
    {
        $from  = 'noreply@bahn.de';
        $this->client->connect();

        $folder = $this->client->getFolder($folderName);

        $query = $folder->query();


        if ($unreadOnly) {
            $query->unseen();
        }

        if ($from !== null) {
            $query->from($from);
        }

        $messages = $query->get();

        if ($limit) {
            $messages = $messages->take($limit);
        }

        $imported = [];
        $errors = [];

        foreach ($messages as $message) {
            try {
                $email = $this->processMessage($message);
                $imported[] = $email->id;
            } catch (\Exception $e) {
                $errors[] = [
                    'message_id' => $message->getMessageId(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => count($imported),
            'errors' => count($errors),
            'email_ids' => $imported,
            'error_details' => $errors,
        ];
    }

    /**
     * Verarbeitet eine einzelne E-Mail-Nachricht
     */
    protected function processMessage(Message $message): Email
    {
        $messageId = $message->getMessageId();

        // Prüfe ob E-Mail bereits existiert
        $existingEmail = Email::where('message_id', $messageId)->first();
        if ($existingEmail) {
            return $existingEmail;
        }

        // Extrahiere Empfänger
        $to = $this->extractAddresses($message->getTo());
        $cc = $this->extractAddresses($message->getCc());
        $bcc = $this->extractAddresses($message->getBcc());

        // Erstelle Email-Eintrag
        $email = Email::create([
            'message_id' => $messageId,
            'subject' => $message->getSubject(),
            'from_email' => $message->getFrom()[0]->mail ?? 'unknown',
            'from_name' => $message->getFrom()[0]->personal ?? null,
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'text_body' => $message->getTextBody(),
            'html_body' => $message->getHTMLBody(),
            'email_date' => $message->getDate(),
            'source' => 'imap',
            'has_attachments' => $message->hasAttachments(),
            'headers' => $message->getHeaders()->toArray(),
        ]);

        // Verarbeite Anhänge
        if ($message->hasAttachments()) {
            $this->processAttachments($message, $email);
        }

        return $email;
    }

    /**
     * Extrahiert E-Mail-Adressen
     */
    protected function extractAddresses($addresses): array
    {
        if (!$addresses) {
            return [];
        }

        $result = [];
        foreach ($addresses as $address) {
            $result[] = [
                'email' => $address->mail,
                'name' => $address->personal,
            ];
        }

        return $result;
    }

    /**
     * Verarbeitet Anhänge
     */
    protected function processAttachments(Message $message, Email $email): void
    {
        $attachments = $message->getAttachments();

        foreach ($attachments as $attachment) {
            $filename = $attachment->getName();
            $content = $attachment->getContent();

            // Generiere eindeutigen Dateinamen
            $storagePath = 'email-attachments/' . $email->id . '/' . Str::random(10) . '_' . $filename;

            // Speichere Datei
            Storage::disk('public')->put($storagePath, $content);

            // Erstelle Attachment-Eintrag
            EmailAttachment::create([
                'email_id' => $email->id,
                'filename' => $filename,
                'mime_type' => $attachment->getMimeType(),
                'size' => strlen($content),
                'path' => $storagePath,
            ]);
        }
    }

    /**
     * Importiert E-Mails aus einem Datumsbereich
     */
    public function importByDateRange(\DateTime $from, \DateTime $to, string $folderName = 'INBOX'): array
    {
        $this->client->connect();

        $folder = $this->client->getFolder($folderName);

        $messages = $folder->query()
            ->since($from)
            ->before($to)
            ->get();

        $imported = [];
        $errors = [];

        foreach ($messages as $message) {
            try {
                $email = $this->processMessage($message);
                $imported[] = $email->id;
            } catch (\Exception $e) {
                $errors[] = [
                    'message_id' => $message->getMessageId(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'imported' => count($imported),
            'errors' => count($errors),
            'email_ids' => $imported,
            'error_details' => $errors,
        ];
    }
}
