<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const title = 'Como funciona a plataforma';
const videoUrl = '/videos/WhatsApp%20Video%202026-11-09%20at%2014.05.31.mp4';
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Boas-vindas', href: '/atleta/boas-vindas/video' }, { title, href: '/atleta/boas-vindas/plataforma' }]">
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-8">
            <header class="space-y-3">
                <div class="eyebrow">Boas-vindas</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] text-brand-gold">{{ title }}</h1>
                <p class="text-brand-muted">Assista ao vídeo para conhecer a plataforma do Desafio da Virada.</p>
            </header>
            <section class="surface-card p-4 sm:p-6" aria-label="Vídeo sobre a plataforma">
                <video controls playsinline preload="none" class="max-h-[70dvh] w-full rounded-xl bg-black" :aria-label="title">
                    <source :src="videoUrl" type="video/mp4" />
                    Seu navegador não suporta a reprodução deste vídeo.
                    <a :href="videoUrl">Abrir vídeo sobre a plataforma</a>
                </video>
            </section>
        </main>
    </AppLayout>
</template>
