# Prompt: Página de pagamento do Desafio 30 Firme (Asaas)

> **Como usar:** abra o Claude Code na raiz do projeto (`D:\projetos_herd\desafio45`) e cole tudo o que está abaixo da linha. Antes de codar, ele deve ler o código existente citado na seção 2 e seguir os padrões que já estão lá.

---

## 1. Papel

Você é um desenvolvedor sênior de SaaS, especialista em Laravel, Inertia/Vue e integrações de pagamento no Brasil. Vai implementar o fluxo de cobrança da inscrição do **Desafio 30 Firme** usando a **API v3 do Asaas**. Priorize segurança, idempotência e uma experiência simples para quem está pagando.

## 2. Contexto do projeto (já existe, não recriar)

- **Stack:** Laravel 12, PHP 8.2+, Inertia v2, Vue 3 + TypeScript, Tailwind 3, Ziggy, Pest 3. Fila em banco (`QUEUE_CONNECTION`, tabela `jobs` já existe).
- **Site público:** https://www.desafio45firme.com.br/
- **Desafio:** 30 dias, de **9/nov/2026 a 8/dez/2026**. Inscrição única de **R$ 79,90** (hoje o valor aparece fixo em `resources/js/pages/Participar.vue`).
- **Usuários e papéis:** `app/Enums/UserRole.php` (administrador, nutricionista, coach, marketing, atleta) e tabela `user_types`. Quem se inscreve vira **atleta**.
- **Cadastro:** `app/Http/Controllers/Auth/RegisteredUserController.php` (nome, e-mail, senha) e `GoogleAuthController.php` (login com Google). Os dois criam o usuário como atleta e fazem login.
- **Fluxo atual:** `Participar.vue` leva a `paymentUrl || route('register')`, onde `paymentUrl` vem de `config('desafio.payment_url')` (env `PAYMENT_URL`). Depois do login, `DashboardController` manda o atleta para `athlete.start.first-day` e `athlete.start.goals`.
- **Chamadas HTTP externas:** já usamos o facade `Http` do Laravel (veja `GoogleAuthController`). **Não instalar SDK do Asaas**, usar `Http::`.
- **Visual:** tema escuro da marca (`bg-brand-ink`, `text-brand-cream`, `bg-brand-gold`, `font-display`, classe `display-title`, cards `rounded-[22px]` e `rounded-[28px]`). Use `Participar.vue` e `resources/js/components/ui` como referência. Textos em **português do Brasil**.

Leia antes de começar: `routes/web.php`, `routes/auth.php`, `bootstrap/app.php`, `app/Models/User.php`, `app/Http/Controllers/DashboardController.php`, `config/desafio.php`, `config/services.php`, `resources/js/pages/Participar.vue`, `resources/js/pages/auth/Register.vue`.

## 3. Objetivo

Quando a pessoa topa o desafio e se registra, no **primeiro acesso** o sistema gera uma cobrança no Asaas e mostra uma **página de pagamento** com o link para pagar por **Pix, boleto ou cartão**. O acesso ao app só é liberado depois que o Asaas confirmar o pagamento, via webhook.

Fluxo:

1. Pessoa clica em "Quem topa" e se registra (e-mail/senha ou Google).
2. Após o login, se for atleta e ainda não pagou, é redirecionada para `/pagamento`.
3. Na primeira visita a `/pagamento`, o sistema pede o **CPF** (necessário para cobrar no Asaas), cria o cliente e a cobrança, e mostra o resumo com o botão **Pagar R$ 79,90**.
4. O botão abre a fatura hospedada do Asaas (`invoiceUrl`), onde a pessoa escolhe Pix, boleto ou cartão.
5. O Asaas avisa por webhook. O sistema marca o pagamento como confirmado e libera o acesso.
6. A pessoa volta ao app e segue o fluxo normal (primeiro dia, metas, dashboard).

## 4. Decisões já tomadas (não reabrir)

- **Valor fixo no servidor:** `R$ 79,90`, vindo de `config('desafio.price')` (env `DESAFIO_PRICE`, padrão `79.90`). Nunca aceitar valor vindo do navegador. A tela de pagamento e `Participar.vue` devem exibir o valor a partir dessa config.
- **Forma de pagamento:** criar a cobrança com `billingType: "UNDEFINED"`, para a pessoa escolher Pix, boleto ou cartão na fatura do Asaas.
- **Vencimento:** hoje + 3 dias (`dueDate` no formato `AAAA-MM-DD`). Se a cobrança vencer sem pagamento, gerar uma nova no próximo acesso.
- **Uma cobrança ativa por usuário.** Reutilizar a existente enquanto estiver `PENDING` e dentro do prazo. Nunca criar duplicada em retry ou recarregamento da página.
- **Confirmação só por webhook** (com checagem na API). Nunca liberar acesso porque a pessoa voltou do checkout.
- **Não guardar CPF** no banco. Enviar ao Asaas e guardar só o `asaas_customer_id`.
- **Sem SDK:** cliente HTTP próprio em `app/Services/Asaas/AsaasClient.php`.
- **Identificação da cobrança:** `externalReference` = UUID interno do registro de pagamento.
- **Marca:** tudo que a pessoa vê em relação ao pagamento deve usar o nome **Desafio 30 Firme**: título da aba, cabeçalho, textos, descrição da cobrança, e-mails e links. O **cabeçalho** da página de pagamento usa **"Desafio 30 dias · Virada"** (mesmo estilo `font-display` e destaque dourado no 30; se o texto ficar longo no mobile, quebrar "Virada" em uma segunda linha menor). Já o título da aba, a descrição da cobrança e o domínio dos links seguem como Desafio 30 Firme. Não alterar as demais telas do app nesta tarefa.
- **Links em nome do Desafio:** a pessoa só vê links do nosso domínio (`https://www.desafio45firme.com.br/pagamento`, `/pagamento/pagar`, `/pagamento/retorno`). O endereço da fatura do Asaas (`invoiceUrl`, domínio do Asaas) **não** é exibido nem enviado por nós: o botão "Pagar" aponta para `/pagamento/pagar`, que redireciona (302) para a fatura. O domínio da fatura em si não dá para trocar; o que se personaliza é a aparência (ver seção 5).

## 5. Integração Asaas (API v3)

**Ambientes**

| Ambiente | Base URL |
|---|---|
| Sandbox | `https://api-sandbox.asaas.com/v3` |
| Produção | `https://api.asaas.com/v3` |

**Headers em toda chamada:** `access_token: <chave>`, `User-Agent: <nome da aplicação>` (obrigatório), `Content-Type: application/json`. A chave de um ambiente não funciona no outro. Timeout de 15 s e retry apenas em erro de rede ou 5xx (nunca repetir um POST sem checar se já criou).

**Endpoints usados**

- `POST /customers`: `name`, `email`, `cpfCnpj`, `externalReference` (id do usuário), `notificationDisabled: false`. Guardar o `id` (`cus_...`) em `users.asaas_customer_id` e reaproveitar nas próximas cobranças.
- `POST /payments`: `customer`, `billingType: "UNDEFINED"`, `value`, `dueDate`, `description` ("Inscrição - Desafio 30 Firme"), `externalReference`, `callback: { successUrl: route('pagamento.retorno'), autoRedirect: true }`. Guardar `id` (`pay_...`), `invoiceUrl`, `status`, `dueDate`.
- `GET /payments/{id}`: usado para conferir o status real antes de liberar o acesso e na reconciliação.

> Verifique na documentação oficial (https://docs.asaas.com) os campos exatos e a regra do `callback.successUrl`. Pelo que sei, o domínio de retorno precisa estar cadastrado na conta Asaas. Se der erro, o retorno automático é só conveniência: a tela precisa funcionar sem ele, por polling.

**Marca na fatura do Asaas (configuração manual na conta, não é código)**

- Em *Configurações da conta*, cadastrar o nome comercial/fantasia como **Desafio 30 Firme** e subir a logo e as cores da marca na personalização da fatura/checkout, para a página do Asaas sair com a identidade do desafio.
- Nome do remetente e textos das notificações ao cliente também em nome do Desafio 30 Firme. Se for usar só os nossos e-mails, desligar as notificações do Asaas por e-mail.
- Cadastrar `desafio45firme.com.br` como domínio de retorno, se a conta exigir.
- Listar esses itens no passo a passo final (seção 12).

**Webhook**

- Rota `POST /webhooks/asaas`, **sem** auth de sessão e **excluída do CSRF** (`$middleware->validateCsrfTokens(except: ['webhooks/asaas'])` em `bootstrap/app.php`).
- Validar o header `asaas-access-token` contra `config('services.asaas.webhook_token')` com `hash_equals`. Token inválido → 401. O token deve ter 32 a 255 caracteres, sem espaços, e **não pode ser a API key**.
- Eventos a tratar: `PAYMENT_CONFIRMED`, `PAYMENT_RECEIVED` (liberam acesso), `PAYMENT_OVERDUE`, `PAYMENT_DELETED` (cobrança precisa ser refeita), `PAYMENT_REFUNDED` (bloqueia acesso de novo). Ignorar os demais com resposta 200.
- O Asaas pode **reenviar o mesmo evento** e **não garante ordem** entre eventos (ex.: `CONFIRMED` e `RECEIVED` do mesmo pagamento). Implementar **idempotência**: tabela `asaas_webhook_events` com o `id` do evento (`evt_...`) único, e transições de status que aceitem chegada fora de ordem sem regredir (um pagamento já `RECEIVED` não volta para `PENDING`).
- Responder **200 rápido**: validar, gravar o evento, despachar um job (`ProcessAsaasWebhook`) e retornar. Respostas fora da família 2xx fazem o Asaas reenviar e podem pausar a fila.
- No job, buscar `GET /payments/{id}` e usar o status retornado pela API como fonte da verdade.
- Localizar o pagamento por `asaas_payment_id`; se não achar, por `externalReference`. Evento de cobrança desconhecida: logar e responder 200.

## 6. Banco de dados (migrations novas, estilo das existentes em `database/migrations`)

- `users`: adicionar `asaas_customer_id` (string, nullable, unique) e `challenge_paid_at` (timestamp, nullable).
- `challenge_payments`:
  - `id`, `uuid` (unique, usado como `externalReference`), `user_id` (FK)
  - `asaas_payment_id` (nullable, unique), `status` (string: `PENDING`, `RECEIVED`, `CONFIRMED`, `OVERDUE`, `REFUNDED`, `DELETED`)
  - `billing_type` (nullable, preenchido pelo webhook), `value` (decimal 10,2), `due_date` (date)
  - `invoice_url` (text, nullable), `confirmed_at` (nullable), timestamps
  - índices em `user_id` e `status`
- `asaas_webhook_events`: `event_id` (unique), `event` (string), `payload` (json), `processed_at` (nullable), timestamps.

Models: `ChallengePayment` (com `belongsTo User`, escopos `pending()`, `isPaid()`) e `AsaasWebhookEvent`. Em `User`, adicionar os campos ao `$fillable`, a relação `payments()` e o helper `hasPaidChallenge()` (`challenge_paid_at !== null`).

**Usuários já existentes:** na migration, fazer backfill de `challenge_paid_at = now()` para todos os usuários que já existem (testes, equipe e atletas atuais), para ninguém ser bloqueado no deploy. Só novos cadastros passam pelo pagamento.

## 7. Backend (Laravel)

1. **Config:** em `config/services.php`, `asaas` com `env`, `api_key`, `webhook_token`, `user_agent`, `base_url` (derivada do ambiente). Em `config/desafio.php`, `price` e `payment_due_days` (3). Adicionar ao `.env.example` (valores vazios) `ASAAS_ENV=sandbox`, `ASAAS_API_KEY=`, `ASAAS_WEBHOOK_TOKEN=`, `ASAAS_USER_AGENT=`, `DESAFIO_PRICE=79.90`. **Nunca** ler, imprimir nem commitar o `.env` real.
2. **`AsaasClient`:** métodos `createCustomer`, `createPayment`, `getPayment`. Lançar `AsaasException` com mensagem amigável e detalhe técnico só no log. Nunca logar a chave, o token do webhook nem CPF completo (mascarar).
3. **`ChallengePaymentService`:** `ensureCharge(User, string $cpf): ChallengePayment` (cria o cliente se faltar, reaproveita cobrança `PENDING` válida, cria nova se vencida ou deletada; use `DB::transaction` + `lockForUpdate` para evitar duplicidade em cliques duplos) e `applyStatus(ChallengePayment, array $asaasPayment)` (atualiza status e, se pago, preenche `confirmed_at` e `users.challenge_paid_at`).
4. **`PaymentController`:**
   - `GET /pagamento` (`pagamento.show`): renderiza `Pagamento` com o estado atual. Se já pagou, redireciona para `dashboard`. Aceita só `auth`.
   - `POST /pagamento` (`pagamento.store`): valida CPF (regra de CPF válido, com dígitos verificadores; crie `app/Rules/Cpf.php`), chama `ensureCharge`, redireciona de volta para `pagamento.show`.
   - `GET /pagamento/pagar` (`pagamento.pagar`): só `auth`; busca a cobrança `PENDING` do próprio usuário e faz `redirect()->away($invoiceUrl)`. Sem cobrança válida, volta para `pagamento.show`. Assim o link exibido e compartilhável é sempre do nosso domínio.
   - `GET /pagamento/retorno` (`pagamento.retorno`): volta do checkout. Consulta `GET /payments/{id}` uma vez, aplica o status e redireciona para `pagamento.show` ou `dashboard`.
   - Limitar `POST /pagamento` com `throttle:6,1`.
5. **`WebhookController`** e job `ProcessAsaasWebhook`, como na seção 5.
6. **Middleware `EnsureChallengePaid`** (alias `challenge.paid`): se o usuário for **atleta** e `! hasPaidChallenge()`, redireciona para `pagamento.show`. Papéis de equipe (administrador, nutricionista, coach, marketing) passam direto. Aplicar no grupo `auth`/`verified` de `routes/web.php` (dashboard, atleta/*, objetivos do app, compras), **sem** bloquear `/pagamento`, `/settings/*`, logout e `/webhooks/asaas`.
7. **Pós-cadastro:** em `RegisteredUserController` e `GoogleAuthController`, atleta novo vai para `pagamento.show` em vez de `dashboard`. Login de atleta que ainda não pagou também cai na página de pagamento (o middleware já resolve).
8. **`Participar.vue`:** o botão passa a ir para `route('register')` (ou `pagamento.show` se já logado). A prop `paymentUrl` e a env `PAYMENT_URL` ficam obsoletas: remover a lógica, mantendo a tela funcionando. Exibir o valor via prop vinda da config.
9. **Comando de reconciliação** `php artisan desafio:reconcile-payments`, agendado a cada 15 minutos em `routes/console.php`: para cobranças `PENDING` com mais de 10 minutos, consultar `GET /payments/{id}` e aplicar o status. É a rede de segurança caso um webhook se perca.

## 8. Frontend (`resources/js/pages/Pagamento.vue`)

Página no layout de `Participar.vue` (cabeçalho com logo, `max-w-[760px]`, fundo `brand-ink`), com `<Head title="Pagamento | Desafio 30 Firme" />` o cabeçalho "Desafio 30 dias · Virada" e o nome Desafio 30 Firme nos textos de apoio. Props: `payment` (status, valor, vencimento; sem `invoiceUrl`), `price`, `needsCpf`.

Estados:

- **Sem cobrança:** título "Falta só o pagamento para entrar no Desafio 30 Firme", resumo do desafio (30 dias, 9 nov a 8 dez), campo de **CPF** com máscara e validação, botão "Gerar pagamento". Texto curto explicando que o CPF é exigido para emitir a cobrança e não fica salvo aqui.
- **Pendente:** card dourado com **R$ 79,90**, botão **Pagar R$ 79,90** (abre `route('pagamento.pagar')` em nova aba com `rel="noopener"`; nunca expor o `invoiceUrl` no HTML), texto "Pix, boleto ou cartão" e o vencimento. Enquanto estiver pendente, fazer polling com `router.reload({ only: ['payment'] })` a cada 5 s, parando quando mudar o estado. Link secundário "Já paguei, verificar agora".
- **Confirmado:** mensagem de sucesso e redirecionamento automático para o dashboard em 3 s.
- **Vencido ou cancelado:** aviso e botão "Gerar novo pagamento".
- **Erro do Asaas:** mensagem amigável ("Não conseguimos gerar o pagamento agora. Tente novamente em instantes."), sem expor detalhe técnico.

Acessibilidade e mobile primeiro: foco visível, botão grande, `aria-live` para mudanças de status, funcionar bem em 360 px. Reutilizar componentes de `components/ui`.

## 9. Segurança e LGPD

- Segredos só em variáveis de ambiente. Nada de chave em código, log, resposta ou front.
- Webhook autenticado por token com `hash_equals`. Se houver proxy/Cloudflare, liberar os IPs do Asaas (sandbox e produção têm IPs diferentes).
- Valor, vencimento e `customer` definidos só no servidor. Autorização: cada usuário só enxerga a própria cobrança.
- Dados pessoais mínimos, CPF não persistido, logs sem dados sensíveis.
- Mensagem de erro ao usuário nunca repete a resposta bruta do Asaas.

## 10. Testes (Pest, `Http::fake()`; nunca chamar o Asaas de verdade)

- Cria cliente + cobrança no primeiro acesso, com payload correto (`UNDEFINED`, 79.90, `externalReference`).
- Segundo acesso e duplo clique **não** criam outra cobrança.
- Cobrança vencida gera nova.
- CPF inválido é rejeitado.
- Webhook: token inválido → 401; `PAYMENT_RECEIVED` libera acesso; evento repetido não duplica efeito; `CONFIRMED` depois de `RECEIVED` não regride; `REFUNDED` bloqueia; evento desconhecido → 200.
- Middleware: atleta sem pagamento é redirecionado; atleta pago e papéis de equipe passam.
- Usuários anteriores ao deploy não são bloqueados (backfill).
- Valor nunca vem do request.
- `/pagamento/pagar` redireciona para a fatura só do dono da cobrança; a resposta de `/pagamento` não contém `invoiceUrl`.

Rode `php artisan test`, `./vendor/bin/pint` e `npm run build` (ou `npx vue-tsc --noEmit` e `npx eslint`) antes de dizer que terminou.

## 11. Critérios de aceite

1. Registrar um atleta novo leva direto a `/pagamento`.
2. Informar um CPF válido gera uma cobrança de R$ 79,90 no sandbox, com link funcionando para Pix, boleto e cartão.
3. Pagar no sandbox libera o acesso sem intervenção manual em até alguns segundos (webhook) ou no máximo 15 minutos (reconciliação).
4. Nenhuma ação do usuário cria cobrança duplicada.
5. Atleta não pago não acessa nenhuma rota do app, exceto pagamento, configurações e logout.
6. Equipe e usuários antigos continuam acessando normalmente.
7. O cabeçalho da página mostra "Desafio 30 dias · Virada"; título da aba, descrição da cobrança e links exibidos usam o nome Desafio 30 Firme e o domínio `desafio45firme.com.br`; o `invoiceUrl` não aparece no HTML nem em e-mails nossos.
8. Todos os testes passam e o build não tem erros.

## 12. Como trabalhar

1. Leia os arquivos da seção 2 e faça um **plano curto** (arquivos que vai criar e alterar) antes de codar.
2. Implemente nesta ordem: config e migrations → `AsaasClient` e service → controllers, rotas e middleware → webhook e job → front → comando de reconciliação → testes.
3. Faça commits pequenos e descritivos.
4. No fim, entregue: resumo do que mudou, **passo a passo para configurar o sandbox** (criar a chave, configurar a marca Desafio 30 Firme na conta Asaas (nome, logo, cores, remetente), cadastrar o webhook em `https://www.desafio45firme.com.br/webhooks/asaas` com os eventos da seção 5 e o token, domínio de retorno) e a lista do que falta para ir à produção (trocar `ASAAS_ENV`, chave de produção, novo webhook).

## 13. Fora de escopo (por enquanto)

Parcelamento no cartão, cupom de desconto, assinatura recorrente, emissão de nota fiscal, área de reembolso para o usuário, Pix com QR Code exibido dentro do nosso site (hoje o QR fica na fatura do Asaas) e qualquer gestão financeira no painel admin.

## 14. Pontos para confirmar com o Sandro antes da produção

- Pegar o CPF na própria tela de pagamento está ok, ou prefere incluir no cadastro?
- Política de reembolso: o `PAYMENT_REFUNDED` deve mesmo bloquear o acesso na hora?
- Quantos dias de vencimento para boleto (padrão 3)? A cobrança precisa estar paga antes de 9/nov?
- E-mails do Asaas ao cliente: manter ligados ou usar só os nossos?
