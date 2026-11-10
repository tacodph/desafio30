<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const title = 'Conquistas dos atletas';

defineProps<{
    athletes: {
        data: { id: number; name: string; achievements_count: number; records: { id: number; detail: string; date: string; day: number }[] }[];
        total: number;
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    categories: Record<string, string>;
    category: string;
    canViewProgress: boolean;
}>();
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Painel', href: '/dashboard' }, { title, href: '/equipe/conquistas' }]">
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 md:p-6">
            <h1 class="display-title text-4xl text-brand-gold">{{ title }}</h1>
            <nav class="flex flex-wrap gap-2" aria-label="Categorias de conquistas">
                <Link v-for="(label, key) in categories" :key="key" :href="route('equipe.achievements', { categoria: key })"
                    :aria-current="category === key ? 'page' : undefined" class="rounded-xl border border-brand-gold px-4 py-2 text-sm font-bold"
                    :class="category === key ? 'bg-brand-gold text-brand-ink' : 'text-brand-gold hover:bg-brand-gold/10'">
                    {{ label }}
                </Link>
            </nav>
            <p class="text-brand-muted">{{ athletes.total }} {{ athletes.total === 1 ? 'atleta com registros nesta categoria' : 'atletas com registros nesta categoria' }}</p>
            <ul v-if="athletes.data.length" class="grid gap-4">
                <li v-for="athlete in athletes.data" :key="athlete.id" class="surface-card space-y-4 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="min-w-0 break-words font-bold">{{ athlete.name }}</h2>
                        <span class="text-sm font-bold text-brand-gold">{{ athlete.achievements_count }} {{ athlete.achievements_count === 1 ? 'registro' : 'registros' }}</span>
                    </div>
                    <ul class="grid gap-3">
                        <li v-for="record in athlete.records" :key="record.id" class="space-y-3 rounded-xl border border-brand-line p-4">
                            <p class="text-sm font-bold text-brand-muted">{{ record.date }}</p>
                            <p class="whitespace-pre-line break-words">{{ record.detail }}</p>
                            <Link v-if="canViewProgress" :href="route('equipe.athlete.progress', { athlete: athlete.id, dia: record.day })" class="inline-flex rounded-lg bg-brand-gold px-4 py-2 text-sm font-bold text-brand-ink">Ver evolução do dia</Link>
                        </li>
                    </ul>
                </li>
            </ul>
            <p v-else class="surface-card p-5 text-brand-muted">Nenhum atleta registrou conquistas nesta categoria até agora.</p>
            <nav v-if="athletes.last_page > 1" class="flex flex-wrap items-center gap-4 text-sm" aria-label="Paginação dos atletas">
                <Link v-if="athletes.prev_page_url" :href="athletes.prev_page_url" class="font-bold text-brand-gold">Anterior</Link>
                <span>Página {{ athletes.current_page }} de {{ athletes.last_page }}</span>
                <Link v-if="athletes.next_page_url" :href="athletes.next_page_url" class="font-bold text-brand-gold">Próxima</Link>
            </nav>
        </main>
    </AppLayout>
</template>
