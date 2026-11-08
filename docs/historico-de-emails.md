# Histórico de emails

O painel fica em **Mensagens → Histórico de emails**, em `/admin/mensagens/emails`, com acesso exclusivo de administrador.

Cada registro contém destinatários, remetente, email de resposta, assunto, texto, serviço de envio, número de tentativas, datas, identificador da mensagem e erro quando disponível. Conteúdos de autenticação não são armazenados para evitar a exposição de links e tokens de acesso.

## Implantação em produção

1. Publicar os arquivos PHP e os assets compilados.
2. Executar `php artisan migrate --force` antes de liberar o novo código de envio.
3. Conferir o `.env`: `MAIL_MAILER` deve apontar para o serviço real de envio. `log` e `array` não enviam emails externamente. Configurar também `SUPPORT_EMAIL`.
4. Para SMTP, os esquemas aceitos são `smtp` e `smtps`, conforme a configuração do provedor. Não usar `MAIL_SCHEME=tls`; a negociação TLS é feita pelo transporte SMTP.
5. Recarregar a configuração com `php artisan config:clear` ou recriar o cache com `php artisan config:cache`, conforme a rotina de implantação.
6. Manter um processador de filas permanente, gerenciado pelo serviço de hospedagem ou Supervisor, usando a conexão e a fila configuradas. Exemplo de comando do processo: `php artisan queue:work --tries=1 --timeout=60`.
7. Se o processador já estiver ativo, executar `php artisan queue:restart` após a implantação para que ele carregue o novo código. Esse comando não inicia um processador que esteja parado.

O processamento de filas pode enviar os lembretes que já estejam pendentes. Conferir os registros existentes antes de reenviar manualmente, para evitar duplicações.

## Significado dos estados

- **Na fila:** solicitação registrada, aguardando processamento; não comprova envio.
- **Em processamento:** uma tentativa de envio foi iniciada.
- **Enviado ao provedor:** o transporte aceitou a mensagem. Não confirma recebimento nem leitura; verificar o provedor para eventos de entrega, rejeição ou spam.
- **Falhou:** houve erro no envio ou no processamento. O detalhe aparece no registro.
- **Somente log / teste:** processado por `log` ou `array`, sem envio externo.
- **Processado pelo transporte:** transporte composto, como failover, processou a mensagem; o painel não afirma entrega externa, pois pode ter ocorrido fallback para log.
- **Não enviado: deixou de ser elegível:** o atleta já não tem cobrança pendente no momento de processar o lembrete.

Lembretes novos são registrados no banco antes da execução da fila. Jobs antigos também geram registro quando chegam à tentativa de envio com o código atualizado. Envios anteriores à implantação não têm histórico retroativo automático.

## Diagnóstico do incidente anterior

O código anterior apenas confirmava a inclusão na fila. Sem acesso ao servidor de produção, não é possível determinar se o incidente veio de fila parada, configuração de email ou recusa pelo provedor. Consultar os jobs pendentes, `php artisan queue:failed`, logs da aplicação e o painel do provedor antes de concluir a causa.
