<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Sugestões de jantar" />
    <AppLayout :breadcrumbs="[{ title: 'Alimentação', href: '/meus-objetivos/alimentacao' }, { title: 'Sugestões de jantar', href: '/alimentacao/sugestoes-de-jantar' }]">
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-2">
                <div class="eyebrow">Alimentação</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] leading-tight text-brand-gold">Sugestões de jantar</h1>
            </header>

            <article class="surface-card space-y-5 p-5 leading-relaxed text-brand-cream sm:p-8">
                <h2 class="font-display text-2xl font-bold text-brand-gold sm:text-3xl">🍽️ JANTAR SAUDÁVEL: VAMOS FALAR SOBRE ISSO?</h2>
                <p>Muita gente chega ao final do dia sem o hábito de jantar e acaba substituindo essa refeição por lanches improvisados, ultraprocessados ou pedidos de iFood.</p>
                <p>Se você prefere um lanche no jantar, tudo bem! Mas ele também precisa ser nutritivo e completo.</p>
                <p>A ideia é seguir uma lógica parecida com a de um prato de comida: incluir uma fonte de proteína, vegetais e outros ingredientes que contribuam para uma refeição equilibrada.</p>
                <p>Um pão com queijo, por exemplo, pode até fazer parte da alimentação, mas não precisa ser a única composição do seu jantar.</p>
                <h3 class="text-xl font-bold text-brand-gold">E qual seria o cenário ideal?</h3>
                <p>Construir o hábito de fazer uma refeição de verdade à noite, variando os alimentos e evitando repetir sempre as mesmas opções do café da manhã ou do lanche da tarde.</p>
                <p>Mas também precisamos considerar a vida real: se um lanche bem planejado facilita sua adesão e ajuda você a manter uma alimentação organizada por mais tempo, ele pode ser uma alternativa muito melhor do que improvisar com ultraprocessados ou recorrer frequentemente ao delivery.</p>
                <p>O objetivo é melhorar suas escolhas, sem criar uma rotina impossível de manter.</p>
                <p class="font-bold text-brand-gold">Agora confira as sugestões de jantares saudáveis disponíveis nesta aba! 🧡</p>
            </article>

            <section class="surface-card space-y-4 p-5 sm:p-8" aria-labelledby="dinner-video-title">
                <h2 id="dinner-video-title" class="font-display text-2xl font-bold text-brand-gold">Assista ao vídeo</h2>
                <video controls playsinline preload="metadata" class="max-h-[75vh] w-full rounded-2xl bg-black" aria-label="Mensagem sobre jantar saudável">
                    <source src="/videos/mensagemAbaJantar.mp4" type="video/mp4" />
                    Seu navegador não suporta a reprodução de vídeo. <a href="/videos/mensagemAbaJantar.mp4" class="underline">Abra o vídeo aqui.</a>
                </video>
            </section>
        </main>
    </AppLayout>
</template>
