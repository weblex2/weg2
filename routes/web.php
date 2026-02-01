<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\EmailImportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-imap', function() {
    try {
        $service = new \App\Services\ImapEmailImportService();
        return 'IMAP Service funktioniert!';
    } catch (\Exception $e) {
        return 'Fehler: ' . $e->getMessage();
    }
});

Route::get('/deploy', [DeployController::class, 'index'])->name('deploy.index');
Route::post('/deploy', [DeployController::class, 'deploy'])->name('deploy.run');


Route::prefix('emails')->group(function () {
    // Import-Seite anzeigen
    Route::get('/import', [EmailImportController::class, 'index'])->name('emails.import');

    // IMAP Import
    Route::post('/import/imap', [EmailImportController::class, 'importFromImap'])->name('emails.import.imap');
    Route::post('/import/imap/daterange', [EmailImportController::class, 'importByDateRange'])->name('emails.import.daterange');

    // Upload Import (.eml Dateien)
    Route::post('/import/upload', [EmailImportController::class, 'uploadEmlFiles'])->name('emails.import.upload');

    // E-Mail anzeigen
    Route::get('/{email}', [EmailImportController::class, 'show'])->name('emails.show');

    // E-Mails auflisten (API)
    Route::get('/', [EmailImportController::class, 'list'])->name('emails.list');
});
