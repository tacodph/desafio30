# Liberações e visualização administrativa

As regras dos atletas ficam em Administração > Liberações do desafio. As opções são Seguir calendário e Bloquear. Salvar exige a senha da conta de administrador.

O antigo modo Liberar para testes foi removido. Valores antigos open no banco são interpretados como Seguir calendário; não liberam atletas antes da data nem lançamentos em outros dias.

## Atletas

As páginas de Evolução e Monte seu Cardápio ficam disponíveis a partir de 08/11/2026, pelo horário de Brasília. Antes da liberação, os submenus permanecem visíveis com cadeado e sem navegação. Tentativas de acessar Evolução diretamente voltam ao painel. O primeiro preenchimento das metas também retorna ao painel.

Os lançamentos e as fotos ficam disponíveis somente na data correspondente a cada dia: de 09/11/2026 até 08/12/2026. O controle Bloquear pode suspender páginas ou lançamentos; não existe opção para liberar todos os dias globalmente.

## Administrador

Com seu login de administrador, use Evolução (visualizar), os submenus Dias, Conquistas e Resumo, ou Monte seu Cardápio (visualizar). Também há atalhos em Liberações do desafio.

O administrador pode acessar essas páginas em qualquer data, sem se cadastrar como atleta, preencher metas ou pagar inscrição. A prévia é somente para consulta, mostra um aviso e não permite salvar lançamentos, fotos, conquistas ou listas de um atleta. Ela não altera liberações, dados ou pontuação de outros usuários.

O calendário continua obrigatório para atletas. Outros perfis não recebem a permissão de prévia administrativa.

## Instalação

A tabela de controles foi criada por:

    php artisan migrate --path=database/migrations/2026_11_02_000000_create_challenge_access_settings_table.php --force

Esta mudança de visualização administrativa não exige uma tabela nova. Publique os arquivos PHP, Vue e public/build atualizados.
