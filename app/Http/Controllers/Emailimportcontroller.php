<?php

namespace App\Http\Controllers;

use App\Services\ImapEmailImportService;
use App\Services\EmlFileImportService;
use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmailImportController extends Controller
{
    public function __construct(
        protected ImapEmailImportService $imapService,
        protected EmlFileImportService $emlService
    ) {}

    /**
     * Zeigt die Import-Seite
     */
    public function index()
    {
        $recentEmails = Email::with('attachments')
            ->orderBy('email_date', 'desc')
            ->take(20)
            ->get();

        return view('emails.import', compact('recentEmails'));
    }

    /**
     * Importiert E-Mails vom IMAP Server
     */
    public function importFromImap(Request $request): JsonResponse
    {

        $request->validate([
            'folder' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:1000',
            'unread_only' => 'nullable|boolean',
            //'from' => 'required|string|max:255',
        ]);

        try {
            $result = $this->imapService->importFromFolder(
                $request->input('folder', 'INBOX'),
                $request->input('limit'),
                $request->input('unread_only', false),
                $request->input('from')
            );

            return response()->json([
                'success' => true,
                'message' => "{$result['imported']} E-Mails erfolgreich importiert",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Import: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Importiert E-Mails aus einem Datumsbereich
     */
    public function importByDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'folder' => 'nullable|string',
        ]);

        try {
            $from = new \DateTime($request->input('from'));
            $to = new \DateTime($request->input('to'));

            $result = $this->imapService->importByDateRange(
                $from,
                $to,
                $request->input('folder', 'INBOX')
            );

            return response()->json([
                'success' => true,
                'message' => "{$result['imported']} E-Mails erfolgreich importiert",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Import: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload und Import von .eml Dateien
     */
    public function uploadEmlFiles(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|mimes:eml,msg|max:10240', // Max 10MB pro Datei
        ]);

        try {
            $filePaths = [];

            foreach ($request->file('files') as $file) {
                // Temporäre Speicherung der Datei
                $path = $file->store('temp-emails');
                $filePaths[] = storage_path('app/' . $path);
            }

            $result = $this->emlService->importMultipleEmlFiles($filePaths);

            // Lösche temporäre Dateien
            foreach ($filePaths as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$result['imported']} E-Mails erfolgreich importiert",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Upload: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Zeigt eine einzelne E-Mail an
     */
    public function show(Email $email)
    {
        $email->load('attachments');
        return view('emails.show', compact('email'));
    }

    /**
     * Listet alle E-Mails auf
     */
    public function list(Request $request): JsonResponse
    {
        $query = Email::with('attachments');

        // Filter nach Absender
        if ($request->has('from_email')) {
            $query->fromEmail($request->input('from_email'));
        }

        // Filter nach Datum
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateRange($request->input('date_from'), $request->input('date_to'));
        }

        // Filter nach Quelle
        if ($request->has('source')) {
            $query->where('source', $request->input('source'));
        }

        $emails = $query->orderBy('email_date', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json($emails);
    }
}
