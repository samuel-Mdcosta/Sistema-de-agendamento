<?php

namespace App\Notifications;

use App\Models\Agendamento;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;
use App\Models\Appointment;
use Illuminate\Support\Facades\Log;

class NewBookingAdmin extends Notification
{
    use Queueable;

    public $appointment;

    public function __construct(Agendamento $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable)
    {
        // Não usaremos canais padrão do Laravel, faremos envio direto no construtor ou método customizado
        // Mas para seguir o padrão, vamos usar um canal 'custom' ou apenas chamar a lógica.
        // Para simplificar este tutorial, vamos disparar o envio direto aqui.
        $this->sendTwilioSms();
        return []; // Não salva no banco
    }

    private function sendTwilioSms()
    {
        $sid    = env('TWILIO_SID');
        $token  = env('TWILIO_TOKEN');
        $from   = env('TWILIO_FROM');
        $to     = env('MANICURE_PHONE'); // Envia para a DONA

        $msg = "💅 Nova Agenda!\nCliente: {$this->appointment->client_name}\nData: {$this->appointment->start_time->format('d/m H:i')}\nTipo: {$this->appointment->type}";

        try {
            $client = new Client($sid, $token);
            $client->messages->create($to, [
                'from' => $from,
                'body' => $msg
            ]);
        } catch (\Exception $e) {
            // Logar erro se falhar, para não travar o sistema
            Log::error("Erro Twilio Admin: " . $e->getMessage());
        }
    }
}