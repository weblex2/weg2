<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeployController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/deploy', [DeployController::class, 'index'])->name('deploy.index');
Route::post('/deploy', [DeployController::class, 'deploy'])->name('deploy.run');
