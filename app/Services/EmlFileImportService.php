<?php

namespace App\Services;

use App\Models\Email;
use App\Models\EmailAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpMimeMailParser\Parser;

class EmlFileImportService
{
    /**
     * Importiert eine .eml Datei
     */
    public function importEmlFile(string $filePath): Email
    {
        $parser = new Parser();
        $parser->setPath($filePath);

        // Extrahiere E-Mail-Daten
        $messageId = $parser->getHeader('message-id');
        $subject = $parser->getHeader('subject');
        $from = $parser->getAddresses('from');
        $to = $parser->getAddresses('to');
        $cc = $parser->getAddresses('cc');
        $bcc = $parser->getAddresses('bcc');
        $date = $parser->getHeader('date');

        // Prüfe ob E-Mail bereits existiert
        if ($messageId) {
            $existingEmail = Email::where('message_id', $messageId)->first();
            if ($existingEmail) {
                return $existingEmail;
            }
        }

        // Erstelle Email-Eintrag
        $email = Email::create([
            'message_id' => $messageId,
            'subject' => $subject ?: '(Kein Betreff)',
            'from_email' => $from[0]['address'] ?? 'unknown',
            'from_name' => $from[0]['display'] ?? null,
            'to' => $this->formatAddresses($to),
            'cc' => $this->formatAddresses($cc),
            'bcc' => $this->formatAddresses($bcc),
            'text_body' => $parser->getMessageBody('text'),
            'html_body' => $parser->getMessageBody('html'),
            'email_date' => $date ? new \DateTime($date) : now(),
            'source' => 'upload',
            'has_attachments' => count($parser->getAttachments()) > 0,
            'headers' => $parser->getHeaders(),
        ]);

        // Verarbeite Anhänge
        $attachments = $parser->getAttachments();
        foreach ($attachments as $attachment) {
            $this->saveAttachment($attachment, $email);
        }

        return $email;
    }

    /**
     * Formatiert E-Mail-Adressen für die Datenbank
     */
    protected function formatAddresses(array $addresses): array
    {
        $result = [];
        foreach ($addresses as $address) {
            $result[] = [
                'email' => $address['address'] ?? null,
                'name' => $address['display'] ?? null,
            ];
        }
        return $result;
    }

    /**
     * Speichert einen Anhang
     */
    protected function saveAttachment($attachment, Email $email): void
    {
        $filename = $attachment->getFilename();
        $content = $attachment->getContent();

        // Generiere eindeutigen Dateinamen
        $storagePath = 'email-attachments/' . $email->id . '/' . Str::random(10) . '_' . $filename;

        // Speichere Datei
        Storage::disk('public')->put($storagePath, $content);

        // Erstelle Attachment-Eintrag
        EmailAttachment::create([
            'email_id' => $email->id,
            'filename' => $filename,
            'mime_type' => $attachment->getContentType(),
            'size' => strlen($content),
            'path' => $storagePath,
        ]);
    }

    /**
     * Importiert mehrere .eml Dateien
     */
    public function importMultipleEmlFiles(array $filePaths): array
    {
        $imported = [];
        $errors = [];

        foreach ($filePaths as $filePath) {
            try {
                $email = $this->importEmlFile($filePath);
                $imported[] = $email->id;
            } catch (\Exception $e) {
                $errors[] = [
                    'file' => $filePath,
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
