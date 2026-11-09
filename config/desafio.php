<?php

return [
    // Obsoleto: o pagamento agora é gerado pelo Asaas em /pagamento.
    'payment_url' => env('PAYMENT_URL'),

    'price' => (float) env('DESAFIO_PRICE', 79.90),
    'payment_due_days' => (int) env('DESAFIO_PAYMENT_DUE_DAYS', 3),
    'registration_closes_at' => '2026-11-09 10:00:00',
    'payment_last_due_date' => '2026-11-09',
];
