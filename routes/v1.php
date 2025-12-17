<?php

use App\Http\Controllers\V1\ClinicController;
use App\Http\Controllers\V1\DoctorController;
use App\Http\Controllers\V1\PatientController;
use App\Http\Controllers\V1\TimeslotController;
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

    // Doctor-specific timeslot routes
    Route::get('/{doctorId}/timeslots', [TimeslotController::class, 'getTimeslotByDoctor']);
    Route::get('/{doctorId}/timeslots/available', [TimeslotController::class, 'getAvailableByDoctor']);
});

Route::prefix('timeslots')->group(function () {
    Route::get('/', [TimeslotController::class, 'index']);
    Route::post('/', [TimeslotController::class, 'store']);
    Route::get('/{id}', [TimeslotController::class, 'show']);
    Route::put('/{id}', [TimeslotController::class, 'update']);
});

Route::prefix('patients')->group(function () {
    Route::get('/', [PatientController::class, 'index']);
    Route::post('/', [PatientController::class, 'store']);
    Route::get('/{id}', [PatientController::class, 'show']);
    Route::put('/{id}', [PatientController::class, 'update']);
    Route::delete('/{id}', [PatientController::class, 'destroy']);
});
