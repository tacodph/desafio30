# Liberações do desafio

O painel em /admin/liberacoes permite a um usuário do tipo administrador controlar as páginas Dias, Conquistas e Resumo e os lançamentos/fotos dos dias.

## Regras padrão

- As páginas ficam disponíveis a partir de 08/11/2026 às 00h, no fuso America/Sao_Paulo.
- Os 30 dias ficam visíveis na página Dias. Somente o dia correspondente à data atual aceita lançamentos ou alterações de fotos.
- Dia 1: 09/11/2026. Dia 30: 08/12/2026. A janela diária vai de 00h até 23h59, pelo horário de Brasília.
- A proteção também vale nas rotas do servidor, inclusive no envio e na exclusão de fotos. Uma requisição com um lote que inclui um dia bloqueado não grava nenhum lançamento.
- A seleção prévia das habilidades semanais continua disponível com a página Dias aberta, inclusive na véspera.
- O acesso da nutricionista à consulta dos atletas não é bloqueado pelo calendário.

## Controles

- Seguir calendário: aplica as regras de data acima.
- Liberar para testes: abre a página antes da data ou permite lançar em todos os dias, conforme o controle escolhido.
- Bloquear: suspende a página ou os lançamentos, mesmo dentro da data.

Para testar dias fora do calendário, libere tanto Página de dias quanto Lançamentos e fotos dos dias. Uma página de dias bloqueada continua impedindo os lançamentos mesmo se o controle de lançamentos estiver liberado.

As mudanças são globais para os atletas no ambiente conectado e persistem no banco. O ambiente aparece no cabeçalho do painel. Cada salvamento exige a senha da conta de administrador conectada. O tipo de usuário também é conferido no servidor; a senha de um atleta não permite administrar as liberações.

O botão Restaurar calendário no formulário preenche as opções com Seguir calendário; confirme a senha e salve para aplicar.

## Instalação

Execute a nova migração em cada ambiente:

    php artisan migrate --path=database/migrations/2026_11_02_000000_create_challenge_access_settings_table.php --force

Antes da migração, as regras automáticas funcionam com o calendário padrão e o painel informa que o banco precisa ser atualizado para salvar opções.

## Validação

Tests/Feature/ChallengeAccessTest.php cobre a véspera, a data de cada dia, o fuso de Brasília, o fim do desafio, os bloqueios de gravação e fotos, as opções de teste e a autorização com senha de administrador.
