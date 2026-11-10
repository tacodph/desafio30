<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CheckinReaction from '@/components/CheckinReaction.vue';
import { Head, Link } from '@inertiajs/vue3';

const title = 'Check-ins dos atletas';

defineProps<{
    athletes: {
        data: { id: number; name: string; checkins: number; lastDay: number; lastDate: string; days: { day: number; date: string; reaction: string | null }[] }[];
        total: number;
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    canViewProgress: boolean;
}>();
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Painel', href: '/dashboard' }, { title, href: '/equipe/check-ins' }]">
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 md:p-6">
            <header class="space-y-2">
                <h1 class="display-title text-4xl text-brand-gold">{{ title }}</h1>
                <p class="text-brand-muted">{{ athletes.total }} {{ athletes.total === 1 ? 'atleta com check-in registrado' : 'atletas com check-in registrado' }}</p>
            </header>
            <ul v-if="athletes.data.length" class="surface-card divide-y divide-brand-line px-5">
                <li v-for="athlete in athletes.data" :key="athlete.id" class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <div class="min-w-0">
                        <h2 class="break-words font-bold">{{ athlete.name }}</h2>
                        <p class="text-sm text-brand-muted">Último check-in: dia {{ athlete.lastDay }} · {{ athlete.lastDate }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="text-sm font-bold text-brand-gold">{{ athlete.checkins }} {{ athlete.checkins === 1 ? 'check-in' : 'check-ins' }}</span>
                        <Link v-if="canViewProgress" :href="route('equipe.athlete.progress', athlete.id)" class="text-sm font-bold underline">Ver evolução</Link>
                    </div>
                    <details class="w-full rounded-xl border border-brand-line p-3">
                        <summary class="cursor-pointer text-sm font-bold text-brand-gold">Ver check-ins</summary>
                        <ul class="mt-2 divide-y divide-brand-line">
                            <li v-for="day in athlete.days" :key="day.day" class="flex flex-wrap items-center justify-between gap-2 py-3">
                                <span class="text-sm font-semibold">Dia {{ day.day }} · {{ day.date }}</span>
                                <CheckinReaction :athlete-id="canViewProgress ? athlete.id : null" :day="day.day" target="checkin" :reaction="day.reaction" :label="`Check-in do dia ${day.day}`" />
                            </li>
                        </ul>
                    </details>
                </li>
            </ul>
            <p v-else class="surface-card p-5 text-brand-muted">Nenhum atleta registrou check-in até agora.</p>
            <nav v-if="athletes.last_page > 1" class="flex flex-wrap items-center gap-4 text-sm" aria-label="Paginação dos atletas">
                <Link v-if="athletes.prev_page_url" :href="athletes.prev_page_url" class="font-bold text-brand-gold">Anterior</Link>
                <span>Página {{ athletes.current_page }} de {{ athletes.last_page }}</span>
                <Link v-if="athletes.next_page_url" :href="athletes.next_page_url" class="font-bold text-brand-gold">Próxima</Link>
            </nav>
        </main>
    </AppLayout>
</template>
