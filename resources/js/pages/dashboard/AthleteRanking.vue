<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Trophy } from 'lucide-vue-next';

const title = 'Ranking dos atletas';

defineProps<{
    ranking: { id: number; name: string; points: number; checkins: number; position: number }[];
    canViewProgress: boolean;
}>();
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Painel', href: '/dashboard' }, { title, href: '/equipe/ranking' }]">
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 md:p-6">
            <header class="space-y-2">
                <h1 class="display-title text-4xl text-brand-gold">{{ title }}</h1>
                <p class="text-brand-muted">Classificação geral pela pontuação acumulada no desafio. Atletas empatados compartilham a mesma posição.</p>
            </header>
            <ol v-if="ranking.length" class="surface-card divide-y divide-brand-line px-4 sm:px-5">
                <li v-for="athlete in ranking" :key="athlete.id" class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex min-w-12 shrink-0 items-center justify-center gap-1 rounded-xl bg-brand-gold/10 px-2 py-2 font-black text-brand-gold" :aria-label="`${athlete.position}º lugar`">
                            <Trophy v-if="athlete.position === 1" class="size-4" aria-hidden="true" />{{ athlete.position }}º
                        </span>
                        <div class="min-w-0">
                            <h2 class="break-words font-bold">{{ athlete.name }}</h2>
                            <p class="text-sm text-brand-muted">{{ athlete.checkins }} {{ athlete.checkins === 1 ? 'check-in' : 'check-ins' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="font-display text-xl font-black text-brand-gold">{{ athlete.points }} pts</span>
                        <Link v-if="canViewProgress" :href="route('equipe.athlete.progress', athlete.id)" class="text-sm font-bold underline">Ver evolução</Link>
                    </div>
                </li>
            </ol>
            <p v-else class="surface-card p-5 text-brand-muted">Nenhum atleta com acesso liberado ao desafio.</p>
        </main>
    </AppLayout>
</template>
