<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });

        $questions = [
            ['Quando acontece o Desafio da Virada?', 'São 30 dias, de 9 de novembro a 8 de dezembro de 2026.'],
            ['Quais hábitos acompanho no desafio?', 'Você acompanha alimentação, treino ou movimento, hidratação, sono e uma habilidade. As regras ficam nos menus de objetivos e os registros em Evolução → Dias.'],
            ['Por onde começo?', 'Acesse Ponto de partida → Primeiro dia para registrar suas medidas e fotos. Depois, preencha suas metas em Ponto de partida → Metas.'],
            ['Como registro meus objetivos do dia?', 'Acesse Evolução → Dias, escolha a semana e preencha os registros do dia. Salve cada objetivo pelo botão correspondente e confira a confirmação de salvamento.'],
            ['Posso preencher qualquer dia do calendário?', 'Normalmente os lançamentos e fotos ficam disponíveis apenas na data correspondente, no horário de Brasília. A administração pode alterar essas liberações. Se um dia estiver bloqueado, confira a mensagem exibida na página.'],
            ['Onde vejo meus objetivos cumpridos?', 'Acesse Cantinho da Nutri → Monitoramento 30 dias. Os ícones coloridos indicam os objetivos cumpridos. Clique em um deles para abrir o resumo daquele dia.'],
            ['Como escolho minha habilidade?', 'Em Evolução → Dias, selecione a habilidade da semana. Depois, registre sua prática no objetivo Habilidade de cada dia.'],
            ['Onde consulto minha pontuação?', 'Consulte a página Pontuação e o resumo em Evolução. As regras e valores exibidos no sistema são definidos pela administração.'],
            ['Onde encontro ideias para organizar minha alimentação?', 'No menu Alimentação você encontra a lista de compras inteligente, Monte seu prato e Escolhendo melhor fora de casa. O Cantinho da Nutri também reúne materiais e orientações.'],
            ['Por que uma página está bloqueada?', 'O acesso pode depender da confirmação do pagamento, da data de liberação e das permissões definidas pela administração. Consulte a informação exibida na tela e procure o suporte se precisar.'],
            ['Apareceu “Sua sessão expirou”. O que faço?', 'Clique em Entrar novamente, acesse sua conta e refaça a última ação. Informações que ainda não tinham sido enviadas podem precisar ser preenchidas novamente.'],
            ['Como falo com o suporte?', 'No menu Ajuda → Suporte, informe seu nome, email, assunto e mensagem. Descreva o que aconteceu e, se houver, o código de erro. A resposta será enviada para o email informado.'],
        ];

        foreach ($questions as $index => [$question, $answer]) {
            DB::table('faqs')->insert([
                'question' => $question, 'answer' => $answer,
                'sort_order' => $index + 1, 'published' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
