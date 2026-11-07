# Sincronização de pagamentos Asaas

O painel usa os registros locais de challenge_payments. Webhooks autenticados atualizam esses registros pela API do Asaas. A reconciliação agendada é a recuperação quando um evento não chega ou não é processado.

## Recuperar registros em produção

Após publicar a correção, execute na pasta do projeto no servidor:

```sh
php artisan desafio:reconcile-payments --all
```

O comando consulta cobranças já vinculadas pelo asaas_payment_id, sem criar ou modificar cobranças no Asaas. Atualiza status e forma de pagamento no site e libera a inscrição quando a API confirma o pagamento. Também pode revogar o acesso caso a API informe estorno. Exibe os totais de conferências e falhas, retornando código de erro se alguma consulta falhar. Registros locais já estornados ou excluídos não entram na conferência.

## Atualizações automáticas

- O webhook de produção deve apontar para https://www.desafio30firme.com.br/webhooks/asaas e ter token igual ao ASAAS_WEBHOOK_TOKEN do servidor.
- Eventos tratados: PAYMENT_UPDATED, PAYMENT_RESTORED, PAYMENT_CONFIRMED, PAYMENT_RECEIVED, PAYMENT_OVERDUE, PAYMENT_DELETED e PAYMENT_REFUNDED. Selecione esses eventos no webhook do Asaas.
- Com QUEUE_CONNECTION=database ou outra fila assíncrona, mantenha o worker do Laravel ativo. Após publicar, use php artisan queue:restart para os workers recarregarem o código; esse comando não inicia um worker parado.
- Mantenha o agendador do Laravel executando a cada minuto (php artisan schedule:run via cron). A aplicação agenda desafio:reconcile-payments a cada 15 minutos.
- A conferência automática inclui pendentes, vencidas, confirmadas e formas indefinidas, sem o antigo limite de idade de 30 dias. A opção --all inclui também recebidas com forma definida para corrigir diferenças históricas.

Não confundir o número exibido no endereço /payment/show/... do painel Asaas com o identificador da API armazenado em asaas_payment_id.
