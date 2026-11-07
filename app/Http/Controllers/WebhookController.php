<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAsaasWebhook;
use App\Models\AsaasWebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /** Eventos que mudam o acesso ou a cobrança. Os demais recebem 200 e são ignorados. */
    public const HANDLED_EVENTS = [
        'PAYMENT_UPDATED',
        'PAYMENT_RESTORED',
        'PAYMENT_CONFIRMED',
        'PAYMENT_RECEIVED',
        'PAYMENT_OVERDUE',
        'PAYMENT_DELETED',
        'PAYMENT_REFUNDED',
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $expected = (string) config('services.asaas.webhook_token');
        $given = (string) $request->header('asaas-access-token');

        if ($expected === '' || ! hash_equals($expected, $given)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $body = $request->json()->all();
        $event = $body['event'] ?? null;
        $paymentId = $body['payment']['id'] ?? null;

        if (! is_string($event) || ! in_array($event, self::HANDLED_EVENTS, true) || ! is_string($paymentId)) {
            return response()->json(['received' => true]);
        }

        $eventId = is_string($body['id'] ?? null)
            ? $body['id']
            : 'sha1_'.sha1($event.'|'.$paymentId.'|'.($body['payment']['status'] ?? '').'|'.($body['dateCreated'] ?? ''));

        $record = AsaasWebhookEvent::query()->firstOrCreate(
            ['event_id' => $eventId],
            ['event' => $event, 'payload' => $body],
        );

        // Evento repetido já processado: nada a fazer. Evento que falhou antes volta para a fila.
        if (! $record->processed_at) {
            ProcessAsaasWebhook::dispatch($record->id);
        }

        return response()->json(['received' => true]);
    }
}
