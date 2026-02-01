<?php

use App\Http\Controllers\Api\ESP32IntervalController;
use Illuminate\Support\Facades\Route;


Route::prefix('esp32')->group(function () {

    // ESP meldet sich an / Heartbeat
    Route::post('/register', [ESP32IntervalController::class, 'register']);

    // ESP holt Intervall
    Route::get('/interval', [ESP32IntervalController::class, 'getInterval']);

    // Intervall setzen (z.B. Home Assistant)
    Route::post('/interval', [ESP32IntervalController::class, 'setInterval']);

    // Status eines Geräts
    Route::get('/status', [ESP32IntervalController::class, 'getStatus']);

    // Alle Devices
    Route::get('/', [ESP32IntervalController::class, 'index']);
});

// Optional: Mit API-Token absichern
// Route::middleware('auth:sanctum')->prefix('api/esp32')->group(function () {
//     Route::post('/interval', [ESP32IntervalController::class, 'setInterval']);
// });
