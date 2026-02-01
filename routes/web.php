<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\EmailImportController;
use App\Http\Controllers\TileController;
use App\Http\Controllers\ESP32DashboardController;


Route::get('/', [TileController::class, 'index'])->name('dashboard.index');
Route::post('/tiles', [TileController::class, 'store'])->name('tiles.store');
Route::delete('/tiles/{tile}', [TileController::class, 'destroy'])->name('tiles.destroy');

Route::get('/welcome', function () {
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



Route::prefix('esp32')->name('esp32.')->group(function () {
    // Dashboard
    Route::get('/', [ESP32DashboardController::class, 'index'])->name('dashboard');

    // Device Details
    Route::get('/{id}', [ESP32DashboardController::class, 'show'])->name('show');

    // Intervall Update
    Route::put('/{id}', [ESP32DashboardController::class, 'update'])->name('update');

    // Device aktivieren/deaktivieren
    Route::post('/{id}/toggle', [ESP32DashboardController::class, 'toggleActive'])->name('toggle');

    // Device löschen
    Route::delete('/{id}', [ESP32DashboardController::class, 'destroy'])->name('destroy');
});
