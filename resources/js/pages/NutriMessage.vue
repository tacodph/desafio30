<script setup lang="ts">
import NutriMessage from '@/components/NutriMessage.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Recado importante da nutri" />
    <AppLayout :breadcrumbs="[{ title: 'Alimentação', href: '/meus-objetivos/alimentacao' }, { title: 'Recado importante da nutri', href: '/alimentacao/recado-da-nutri' }]">
        <main class="mx-auto w-full max-w-4xl p-4 md:p-6">
            <NutriMessage />
        </main>
    </AppLayout>
</template>
