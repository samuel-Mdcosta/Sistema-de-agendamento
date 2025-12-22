<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/slots', [AppointmentController::class, 'getSlots']);

Route::post('/appointments', [AppointmentController::class, 'store']);