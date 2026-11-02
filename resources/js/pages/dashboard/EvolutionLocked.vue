<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
defineProps<{
    feature: string;
    access: { releaseDate: string };
}>();
</script>
<template>
    <Head title="Acesso ao desafio" />
    <AppLayout :breadcrumbs="[{ title: 'Painel', href: '/dashboard' }, { title: feature, href: '#' }]">
        <div class="mx-auto flex w-full max-w-2xl flex-col gap-5 p-4 md:p-6">
            <div class="surface-card flex flex-col gap-4 p-6">
                <div class="eyebrow">Desafio da Virada</div>
                <h1 class="display-title text-4xl">{{ feature }}</h1>
                <p class="m-0 leading-relaxed text-brand-muted">Esta página ainda não está disponível. A liberação pelo calendário começa em {{ access.releaseDate }}, um dia antes do desafio. A administração também pode controlar a disponibilidade.</p>
                <p class="m-0 text-sm text-brand-muted">Os lançamentos serão habilitados apenas na data correspondente a cada dia, pelo horário de Brasília.</p>
                <Link :href="route('dashboard')" class="w-fit rounded-full bg-brand-gold px-5 py-3 font-bold text-brand-ink">Voltar ao painel</Link>
            </div>
        </div>
    </AppLayout>
</template>
