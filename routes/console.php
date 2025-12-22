use Illuminate\Support\Facades\Schedule;
use App\Models\Appointment;
use Twilio\Rest\Client;

Schedule::call(function () {
    // Busca agendamentos daqui a 1 hora que AINDA NÃO foram avisados
    $appointments = Appointment::where('start_time', '>=', now())
        ->where('start_time', '<=', now()->addHour())
        ->where('reminder_sent', false)
        ->get();

    if ($appointments->isEmpty()) return;

    $sid    = env('TWILIO_SID');
    $token  = env('TWILIO_TOKEN');
    $from   = env('TWILIO_FROM');

    $clientTwilio = new Client($sid, $token);

    foreach ($appointments as $app) {
        try {
            $msg = "Olá {$app->client_name}, lembrete: seu horário é às {$app->start_time->format('H:i')}!";

            $clientTwilio->messages->create($app->client_phone, [
                'from' => $from,
                'body' => $msg
            ]);

            // Marca como enviado
            $app->update(['reminder_sent' => true]);

        } catch (\Exception $e) {
            \Log::error("Erro SMS Cliente: " . $e->getMessage());
        }
    }
})->everyMinute();