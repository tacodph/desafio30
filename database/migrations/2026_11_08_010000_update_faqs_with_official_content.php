<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $questions = [
            ['Como funciona o desafio?', "O Desafio da Virada é uma experiência de 30 dias com metas e objetivos para colocar em prática no dia a dia.\n\nVocê terá objetivos diários, materiais de apoio e orientações gerais para ajudar na organização da rotina e na construção de hábitos."],
            ['Onde acontece o desafio? Preciso baixar algum aplicativo?', "O desafio acontece em uma plataforma online, acessada diretamente pelo site. Você não precisa baixar nenhum aplicativo.\n\nBasta acessar o link, fazer seu login e acompanhar suas atividades."],
            ['Quando o acesso será liberado?', "Você já pode acessar a plataforma e criar sua senha.\n\nOs objetivos, as metas, os materiais de apoio e os check-ins serão liberados no domingo, às 20h, para começarmos oficialmente na segunda-feira, dia 09/11."],
            ['Teremos grupo de WhatsApp?', 'Não teremos grupo de WhatsApp. Toda a experiência acontecerá pela plataforma, onde você poderá acompanhar seus objetivos, realizar os check-ins e registrar seu progresso individualmente.'],
            ['O desafio inclui dieta ou cardápio individualizado?', "Não. O Desafio da Virada não é um acompanhamento nutricional individualizado.\n\nNão teremos dietas personalizadas, cardápios ou gramaturas de alimentos.\n\nVocê receberá orientações gerais e materiais de apoio, incluindo sugestões de combinações de alimentos organizadas por grupos alimentares, para ajudar na organização das refeições e na aplicação dos conhecimentos sobre alimentação saudável."],
            ['Vou receber materiais de apoio?', 'Sim! Serão disponibilizados materiais preparados para orientar você durante o desafio, com sugestões práticas e informações relacionadas aos objetivos propostos.'],
            ['Como faço para acompanhar minhas metas?', 'Diariamente, você poderá acessar a plataforma e realizar seus check-ins, marcando os objetivos cumpridos e acompanhando sua pontuação e evolução ao longo dos 30 dias.'],
            ['Posso fazer acompanhamento nutricional durante o desafio?', "Sim! Mas esse é um serviço separado.\n\nCaso queira um plano alimentar individualizado, avaliação e acompanhamento profissional, também ofereço consultas nutricionais nas modalidades presencial e online."],
            ['Qual é o período do desafio?', 'Serão 30 dias de atividades, de 09 de novembro a 08 de dezembro.'],
        ];

        DB::transaction(function () use ($questions) {
            // Preserva as perguntas anteriores no painel administrativo, sem exibi-las ao público.
            DB::table('faqs')->update(['published' => false, 'updated_at' => now()]);

            foreach ($questions as $index => [$question, $answer]) {
                $existing = DB::table('faqs')->where('question', $question)->first();
                $values = ['answer' => $answer, 'sort_order' => $index + 1, 'published' => true, 'updated_at' => now()];

                if ($existing) {
                    DB::table('faqs')->where('id', $existing->id)->update($values);
                } else {
                    DB::table('faqs')->insert(['question' => $question, 'created_at' => now(), ...$values]);
                }
            }
        });
    }

    public function down(): void
    {
        // Atualização editorial: não remove respostas nem desfaz edições posteriores do administrador.
    }
};
