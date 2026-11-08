<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{ status: number; title: string; description: string; action: string; href: string }>();
</script>

<template>
    <Head :title="title"><meta name="robots" content="noindex, nofollow" /></Head>
    <div class="grid min-h-screen place-items-center bg-brand-ink px-6 py-12 text-brand-cream">
        <main aria-labelledby="error-title" class="w-full max-w-xl rounded-3xl border border-brand-gold/20 bg-brand-gold/[0.03] p-7 shadow-2xl sm:p-12">
            <img src="/images/logo-mark-30-virada.png" alt="Desafio da Virada — 30 dias" class="size-24 object-contain" />
            <p class="my-5 text-xs font-extrabold uppercase tracking-[2px] text-brand-gold">A gente cresce junto</p>
            <span class="inline-block rounded-full border border-brand-gold/30 px-3 py-1.5 text-sm font-bold text-brand-gold">Erro {{ status }}</span>
            <h1 id="error-title" class="my-5 text-3xl font-black leading-tight sm:text-4xl">{{ title }}</h1>
            <p class="leading-relaxed text-brand-cream/70">{{ description }}</p>
            <nav aria-label="Opções para continuar" class="mt-8 flex flex-wrap gap-3">
                <a :href="href" class="rounded-xl bg-brand-gold px-5 py-3 font-bold text-brand-ink hover:brightness-110 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-gold">{{ action }}</a>
                <a href="/suporte" class="rounded-xl border border-brand-gold/30 px-5 py-3 font-bold text-brand-gold hover:bg-brand-gold/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-gold">Falar com o suporte</a>
            </nav>
            <p class="mt-7 text-sm leading-relaxed text-brand-cream/60">Se precisar de ajuda, informe ao suporte o código {{ status }} e o que estava tentando fazer.</p>
        </main>
    </div>
</template>
