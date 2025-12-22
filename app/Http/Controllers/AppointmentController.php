<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use App\Notifications\admmanicure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AppointmentController extends Controller
{
    // 1. Listar Slots Disponíveis (Sua lógica de horários dinâmicos)
    public function getSlots(Request $request)
    {
        $date = $request->query('date'); // YYYY-MM-DD
        if (!$date) return response()->json([], 400);

        // Horários Padrão (Intervalo de 2h)
        $baseSlots = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00'];
        $finalSlots = [];

        // Busca agendamentos do dia
        $appointments = Agendamento::whereDate('start_time', $date)->get();

        foreach ($baseSlots as $slot) {
            $slotTime = Carbon::parse("$date $slot");

            // Checa colisão do horário base (se está livre por pelo menos 1h)
            if (!$this->isBlocked($slotTime, $appointments)) {
                $finalSlots[] = $slot;
            }

            // LÓGICA DINÂMICA:
            // Se existe agendamento neste slot base, mas é curto (1h), libera o próximo
            $occupyingApp = $appointments->filter(function($app) use ($slotTime) {
                return $app->start_time->eq($slotTime);
            })->first();

            if ($occupyingApp && $occupyingApp->type !== 'ambos') {
                // O slot base (ex 08:00) está ocupado por mão ou pé (1h).
                // O fim dele é 09:00. Vamos ver se 09:00 está livre.
                $dynamicTime = $occupyingApp->end_time;

                if (!$this->isBlocked($dynamicTime, $appointments)) {
                    $finalSlots[] = $dynamicTime->format('H:i');
                }
            }
        }

        sort($finalSlots);
        return response()->json($finalSlots);
    }

    // 2. Salvar Agendamento e Avisar Manicure
    public function store(Request $request)
    {
        $data = $request->validate([
            'client_name' => 'required',
            'client_phone' => 'required', // Formato +55...
            'type' => 'required|in:mao,pe,ambos',
            'date' => 'required|date',
            'time' => 'required'
        ]);

        $start = Carbon::parse("{$data['date']} {$data['time']}");
        $duration = ($data['type'] === 'ambos') ? 2 : 1;
        $end = $start->copy()->addHours($duration);

        // Verificação final de colisão (Segurança)
        if ($this->checkCollisionRange($start, $end)) {
            return response()->json(['error' => 'Horário indisponível'], 422);
        }

        // Salva
        $appointment = Agendamento::create([
            'client_name' => $data['client_name'],
            'client_phone' => $data['client_phone'],
            'type' => $data['type'],
            'start_time' => $start,
            'end_time' => $end
        ]);

        // --- ENVIA SMS PARA A MANICURE ---
        try {
            // Instanciamos a notificação e chamamos o método 'via' manualmente ou usamos notify
            // Como não temos um User model para a manicure, instanciamos a classe direto
            (new admmanicure($appointment))->via(null);
        } catch (\Exception $e) {
            // Ignora erro de SMS para não falhar o request
        }

        return response()->json($appointment, 201);
    }

    // Helpers Privados
    private function isBlocked($time, $appointments) {
        // Verifica se o horário 'time' colide com algum agendamento existente
        return $appointments->filter(function ($app) use ($time) {
            return $time >= $app->start_time && $time < $app->end_time;
        })->isNotEmpty();
    }

    private function checkCollisionRange($start, $end) {
        return Agendamento::where(function($q) use ($start, $end) {
            $q->whereBetween('start_time', [$start, $end])
              ->orWhereBetween('end_time', [$start, $end]);
            // Ajuste fino: Se o start for igual ao end_time de outro, não é colisão
        })->where('start_time', '<', $end)
          ->where('end_time', '>', $start)
          ->exists();
    }
}