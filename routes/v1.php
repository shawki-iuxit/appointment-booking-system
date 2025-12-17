<?php

use App\Http\Controllers\V1\ClinicController;
use App\Http\Controllers\V1\DoctorController;
use Illuminate\Support\Facades\Route;

Route::prefix('clinics')->group(function () {
    Route::get('/', [ClinicController::class, 'index']);
    Route::post('/', [ClinicController::class, 'store']);
    Route::get('/{id}', [ClinicController::class, 'show']);
    Route::put('/{id}', [ClinicController::class, 'update']);
    Route::delete('/{id}', [ClinicController::class, 'destroy']);

    // Clinic-specific doctor routes
    Route::get('/{clinicId}/doctors', [DoctorController::class, 'getByClinic']);
});

Route::prefix('doctors')->group(function () {
    Route::get('/', [DoctorController::class, 'index']);
    Route::post('/', [DoctorController::class, 'store']);
    Route::get('/{id}', [DoctorController::class, 'show']);
    Route::put('/{id}', [DoctorController::class, 'update']);
    Route::delete('/{id}', [DoctorController::class, 'destroy']);
});
