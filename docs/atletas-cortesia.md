# Atletas cortesia

Somente contas do tipo administrador podem conceder ou remover cortesia. A permissão é validada no servidor e não apenas na interface.

## Novo cadastro

Em Administração > Usuários > Cadastrar usuário, selecione o tipo atleta e marque Atleta cortesia. O cadastro é criado com acesso ao desafio sem gerar cliente ou cobrança no Asaas.

Sem essa marcação, o novo atleta segue o pagamento normal. O cadastro público não permite conceder cortesia.

## Atleta existente

Na lista Administração > Usuários, use Conceder cortesia. A inscrição passa a exibir Cortesia. A opção Remover cortesia retira essa liberação; um pagamento já confirmado continua garantindo o acesso.

A concessão registra a data e o administrador responsável, separadamente da data de pagamento. Cobranças existentes no Asaas não são canceladas por essa ação.

O atleta com cortesia entra no painel normalmente, permanece sujeito ao calendário de liberação do desafio e não consegue gerar uma cobrança pelo fluxo de pagamento. O perfil informa Cortesia — participação sem cobrança.

## Publicação

Execute a migração em cada ambiente após publicar os arquivos:

    php artisan migrate --path=database/migrations/2026_11_03_000000_add_challenge_courtesy_to_users.php --force

Publique também os arquivos compilados de public/build.
