<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/slots', [AppointmentController::class, 'getSlots']);

Route::post('/appointments', [AppointmentController::class, 'store']);

Route::get('/appointments', [AppointmentController::class, 'index']);

Route::get('/test-twilio', function () {
    $sid = env('TWILIO_SID');
    $token = env('TWILIO_TOKEN');
    $from = env('TWILIO_FROM');
    $to = env('MANICURE_PHONE');

    $client = new \Twilio\Rest\Client($sid, $token);

    $client->messages->create($to, [
        'from' => $from,
        'body' => '✅ Teste Twilio OK!'
    ]);

    return response()->json(['status' => 'Mensagem enviada']);
});
