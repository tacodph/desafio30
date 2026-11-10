<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const title = 'Sugestões de refeições reais em consultório';
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Alimentação', href: '/meus-objetivos/alimentacao' }, { title, href: '/alimentacao/refeicoes-reais-em-consultorio' }]">
        <main class="mx-auto w-full max-w-5xl space-y-5 p-4 md:p-6">
            <h1 class="display-title text-4xl text-brand-gold">{{ title }}</h1>
            <p class="surface-card p-5 text-brand-muted">As sugestões de refeições serão disponibilizadas aqui.</p>
        </main>
    </AppLayout>
</template>
