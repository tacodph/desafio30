<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

defineProps<{ adminPreview?: boolean }>();

const form = useForm({});
const colors = ['#fbbf24', '#fb923c', '#f472b6', '#34d399', '#60a5fa'];
const confetti = ref<Record<string, string>[]>([]);

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    confetti.value = Array.from({ length: 56 }, (_, index) => ({
        left: Math.random() * 100 + '%',
        backgroundColor: colors[index % colors.length],
        animationDelay: Math.random() * 0.5 + 's',
        animationDuration: 2 + Math.random() + 's',
        '--drift': Math.random() * 180 - 90 + 'px',
    }));
});
</script>

<template>
    <Head title="Boas-vindas ao Desafio da Virada" />

    <main class="min-h-svh bg-brand-bg px-4 py-8 text-brand-cream sm:py-12">
        <div class="pointer-events-none fixed inset-0 z-10 overflow-hidden" aria-hidden="true">
            <span v-for="(style, index) in confetti" :key="index" class="confetti-piece" :style="style" />
        </div>

        <div class="surface-card mx-auto max-w-3xl space-y-7 p-5 sm:p-9">
            <header class="space-y-3">
                <h1 class="text-2xl font-bold sm:text-4xl">🎉 SEJA BEM-VINDA AO DESAFIO DA VIRADA! 🧡</h1>
                <p class="font-semibold">Sua inscrição está confirmada! Que bom ter você com a gente nessa jornada!</p>
                <p>Agora, algumas informações importantes para você aproveitar os nossos 30 dias:</p>
            </header>

            <section class="space-y-2">
                <h2 class="text-lg font-bold">📲 1. ACESSO À PLATAFORMA</h2>
                <p>Você receberá os dados de acesso à plataforma exclusiva do Desafio da Virada. É por lá que toda a experiência vai acontecer.</p>
                <p>Ao acessar, você poderá acompanhar suas metas, registrar seus check-ins diários, fotografar suas refeições, fazer anotações e acompanhar suas conquistas e evolução.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold">🎯 2. QUANDO COMEÇA?</h2>
                <p>No domingo, dia 08/11, os objetivos, as metas e os materiais do desafio serão liberados na plataforma.</p>
                <p>Esse será o momento de conhecer as propostas, entender como funciona cada objetivo e se organizar para começar!</p>
                <p>Na segunda-feira, dia 09/11, começa oficialmente o nosso desafio. Serão 30 dias de comprometimento com você e com os hábitos que deseja construir.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold">📚 3. MATERIAIS DE APOIO</h2>
                <p>Durante o desafio, você terá acesso a materiais, orientações e direcionamentos preparados por mim, como nutricionista, para ajudar na organização e na execução das metas.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold">📸 4. SEU COMPROMISSO</h2>
                <p>A plataforma será seu diário durante esses 30 dias!</p>
                <p>É importante acessar diariamente, realizar os check-ins, registrar suas refeições, acompanhar suas metas e anotar suas pequenas vitórias.</p>
                <p>Não existe um grupo de WhatsApp ou acompanhamento individual por mensagens. A proposta é que você utilize as ferramentas e orientações disponibilizadas para monitorar sua própria evolução.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-lg font-bold">❤️ 5. UM RECADO IMPORTANTE</h2>
                <p>Não espere fazer tudo perfeitamente. O objetivo é participar, experimentar, aprender a se organizar e construir hábitos possíveis de manter.</p>
                <p>Não precisamos mudar tudo de uma vez. Precisamos começar!</p>
                <p>Prepare-se para 30 dias de movimento, escolhas e comprometimento.</p>
                <p>Vamos virar essa chave juntas?</p>
                <p class="font-bold">Bem-vinda ao Desafio da Virada! 🧡</p>
            </section>

            <Link v-if="adminPreview" :href="route('dashboard')" class="inline-flex rounded-lg border border-brand-line2 px-4 py-2 text-sm font-semibold">
                Voltar ao painel
            </Link>
            <form v-else @submit.prevent="form.post(route('athlete.welcome.acknowledge'))">
                <Button type="submit" :disabled="form.processing">OK, continuar</Button>
            </form>
        </div>
    </main>
</template>

<style scoped>
.confetti-piece {
    position: absolute;
    top: -1rem;
    width: 0.5rem;
    height: 0.75rem;
    animation: confetti-fall linear forwards;
}

@keyframes confetti-fall {
    to {
        transform: translate(var(--drift), calc(100vh + 2rem)) rotate(720deg);
        opacity: 0;
    }
}
</style>
