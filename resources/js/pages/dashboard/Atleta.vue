<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import ProgressSummary from '@/components/ProgressSummary.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Painel',
        href: '/dashboard',
    },
];

defineProps<{
    total: number;
    days: { week: number }[];
    weeks: {
        week: number;
        period: string;
        checkins: number;
        daily: number;
        strength: number;
        skill: number;
        plus: number;
        plus_bonus: number;
        consistency: number;
        goals_bonus: number;
        achievements: number;
        total: number;
        athletes_done: number;
        athletes_total: number;
    }[];
    dailyScores: { id: number; label: string; points: number }[];
    bonusScores: { id: number; label: string; points: number }[];
}>();
</script>

<template>
    <Head title="Painel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <section class="surface-card flex flex-col gap-5 p-5 sm:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <span class="eyebrow">Resumo</span>
                        <h1 class="m-0 mt-1 font-display text-3xl font-black text-brand-gold">{{ total }} pts</h1>
                    </div>
                    <Link :href="route('athlete.progress.tab', 'resumo')" class="text-sm font-bold text-brand-gold hover:underline">Ver resumo completo →</Link>
                </div>
                <ProgressSummary :days="days" :weeks="weeks" />
            </section>

            <section class="grid gap-4">
                <div class="surface-card flex flex-col gap-3 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="m-0 font-display text-xl font-extrabold uppercase">Pontuação diária</h2>
                        <Link :href="route('pontuacao')" class="shrink-0 text-sm font-bold text-brand-gold hover:underline">Ver regras →</Link>
                    </div>
                    <div v-for="item in dailyScores" :key="item.id" class="flex items-baseline justify-between gap-3 border-b border-brand-line2 pb-2 last:border-0 last:pb-0">
                        <span class="text-sm text-brand-muted">{{ item.label }}</span>
                        <span class="font-display font-black text-brand-gold">+{{ item.points }}</span>
                    </div>
                    <div class="flex items-baseline justify-between gap-3 border-t border-brand-line2 pt-2">
                        <span class="text-sm text-brand-muted">Não usei refeição luxo essa semana</span>
                        <span class="shrink-0 font-display font-black text-brand-gold">+5 pontos</span>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
