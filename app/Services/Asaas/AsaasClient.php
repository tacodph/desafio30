<?php

namespace App\Services\Asaas;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cliente enxuto da API v3 do Asaas. Nunca registra chave, token ou CPF nos logs.
 */
class AsaasClient
{
    /** @param array<string, mixed> $data */
    public function createCustomer(array $data): array
    {
        return $this->send('POST', '/customers', $data);
    }

    /** @param array<string, mixed> $data */
    public function createPayment(array $data): array
    {
        return $this->send('POST', '/payments', $data);
    }

    public function getPayment(string $id): array
    {
        return $this->send('GET', '/payments/'.$id);
    }

    public function getBalance(): ?float
    {
        $balance = $this->send('GET', '/finance/balance')['balance'] ?? null;

        return is_numeric($balance) ? (float) $balance : null;
    }

    /** @param array<string, mixed> $data */
    private function send(string $method, string $path, array $data = []): array
    {
        $request = $this->request();

        // Retry só em leitura: um POST repetido poderia duplicar cliente ou cobrança.
        if ($method === 'GET') {
            $request = $request->retry(2, 300, fn ($e) => $e instanceof ConnectionException, throw: false);
        }

        try {
            /** @var Response $response */
            $response = $method === 'GET'
                ? $request->get($path)
                : $request->post($path, $data);
        } catch (ConnectionException $e) {
            Log::warning('Asaas: falha de conexão', ['method' => $method, 'path' => $path]);

            throw new AsaasException('Falha de conexão com o Asaas.', previous: $e);
        }

        if ($response->failed()) {
            Log::warning('Asaas: resposta com erro', [
                'method' => $method,
                'path' => $path,
                'status' => $response->status(),
                'errors' => $response->json('errors'),
            ]);

            throw new AsaasException('O Asaas respondeu com erro HTTP '.$response->status().'.');
        }

        return (array) $response->json();
    }

    private function request(): PendingRequest
    {
        $key = config('services.asaas.api_key');

        if (! is_string($key) || $key === '') {
            throw new AsaasException('ASAAS_API_KEY não configurada.');
        }

        return Http::baseUrl((string) config('services.asaas.base_url'))
            ->withHeaders([
                'access_token' => $key,
                'User-Agent' => (string) config('services.asaas.user_agent'),
            ])
            ->acceptJson()
            ->asJson()
            ->timeout(15);
    }
}
