<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="O desafio não termina em 30 dias" />
    <AppLayout :breadcrumbs="[{ title: 'Boas-vindas', href: '/atleta/boas-vindas/video' }, { title: 'O desafio não termina em 30 dias', href: '/atleta/boas-vindas/aproveitar-melhor' }]">
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-8">
            <header class="space-y-3">
                <div class="eyebrow">Boas-vindas</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] text-brand-gold">O desafio não termina em 30 dias.</h1>
                <p class="text-brand-muted">Uma conversa importante antes de começar sua jornada</p>
            </header>
            <section class="surface-card space-y-4 p-4 sm:p-6" aria-label="Vídeo de orientações para aproveitar o desafio">
                <video controls playsinline preload="none" class="max-h-[70dvh] w-full rounded-xl bg-black" aria-label="O desafio não termina em 30 dias">
                    <source src="/videos/comoAproveitar%20melhorEssesDias.mp4" type="video/mp4" />
                    Seu navegador não suporta a reprodução deste vídeo.
                    <a href="/videos/comoAproveitar%20melhorEssesDias.mp4">Abrir vídeo de orientações</a>
                </video>
                <p class="text-sm text-brand-muted">Você pode pausar e rever o vídeo sempre que precisar.</p>
            </section>
            <Link href="/atleta/evolucao/dias" class="inline-flex w-fit rounded-xl bg-brand-gold px-5 py-3 text-sm font-bold text-brand-ink">Ir para o check in do dia</Link>
        </main>
    </AppLayout>
</template>
