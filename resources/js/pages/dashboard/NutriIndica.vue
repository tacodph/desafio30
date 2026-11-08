<script setup lang="ts">
import NutriRecommendations from '@/components/NutriRecommendations.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="A nutri indica!" />
    <AppLayout :breadcrumbs="[{ title: 'Cantinho da Nutri', href: '/cantinho-da-nutri' }, { title: 'A nutri indica!', href: '/cantinho-da-nutri/a-nutri-indica' }]">
        <main class="mx-auto w-full max-w-5xl space-y-5 p-4 md:p-6">
            <h1 class="display-title text-[clamp(36px,5vw,56px)] text-brand-gold">A nutri indica!</h1>
            <NutriRecommendations />
        </main>
    </AppLayout>
</template>
