<?php

namespace App\Notifications;

use App\Models\Agendamento;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class AdmManicure extends Notification
{
    use Queueable;

    public $appointment;

    public function __construct(Agendamento $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        // Envio direto via Twilio
        $this->sendTwilioSms();
        return [];
    }

    private function sendTwilioSms()
    {
        $sid   = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $from  = env('TWILIO_FROM');
        $to    = env('MANICURE_PHONE');

        $msg = "Nova agenda\n"
            . "Cliente: {$this->appointment->client_name}\n"
            . "Data: {$this->appointment->start_time->format('d/m H:i')}\n"
            . "Tipo: {$this->appointment->type}";

        try {
            $client = new Client($sid, $token);
            $client->messages->create($to, [
                'from' => $from,
                'body' => $msg
            ]);
        } catch (\Exception $e) {
            Log::error("Erro Twilio Admin: " . $e->getMessage());
        }
    }
}
