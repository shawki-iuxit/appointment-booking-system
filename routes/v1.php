<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\ClinicController;

Route::prefix('clinics')->group(function () {
    Route::get('/', [ClinicController::class, 'index']);
    Route::post('/', [ClinicController::class, 'store']);
    Route::get('/{id}', [ClinicController::class, 'show']);
    Route::put('/{id}', [ClinicController::class, 'update']);
    Route::delete('/{id}', [ClinicController::class, 'destroy']);
});
