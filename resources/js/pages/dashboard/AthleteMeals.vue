<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { handleAccordionClick } from '@/lib/accordionScroll';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';

const title = 'Alimentação dos atletas';
const foodLabels = { no_alcohol: 'Sem álcool', no_fast_food: 'Sem fast food', no_sweet: 'Sem doces', luxury: 'Refeição luxo no planejamento' };

defineProps<{
    athletes: {
        data: { id: number; name: string; days: {
            id: number; day: number; date: string; achieved: boolean; food: Record<keyof typeof foodLabels, boolean>;
            foodNote: string; luxuryCount: number; luxuryNote: string; photos: string[];
        }[] }[];
        total: number;
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}>();
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Painel', href: '/dashboard' }, { title, href: '/equipe/alimentacao' }]">
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 md:p-6" @click="handleAccordionClick">
            <h1 class="display-title text-4xl text-brand-gold">{{ title }}</h1>
            <p class="text-brand-muted">Consulte os registros de alimentação, refeições luxo e fotos por atleta.</p>
            <details v-for="athlete in athletes.data" :key="athlete.id" name="athlete-meals" class="surface-card group overflow-hidden">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-5 [&::-webkit-details-marker]:hidden">
                    <span class="min-w-0 break-words font-bold">{{ athlete.name }}</span>
                    <ChevronDown class="size-5 shrink-0 text-brand-gold group-open:rotate-180" aria-hidden="true" />
                </summary>
                <div class="divide-y divide-brand-line border-t border-brand-line px-5">
                    <article v-for="day in athlete.days" :key="day.id" class="space-y-3 py-4">
                        <h2 class="font-bold text-brand-gold">Dia {{ day.day }} · {{ day.date }}</h2>
                        <p v-if="day.achieved" class="text-sm font-semibold">Meta de alimentação atingida</p>
                        <ul v-if="Object.values(day.food).some(Boolean)" class="flex flex-wrap gap-2 text-sm">
                            <template v-for="(label, key) in foodLabels" :key="key">
                                <li v-if="day.food[key]" class="rounded-lg bg-brand-card2 px-3 py-1">{{ label }}</li>
                            </template>
                        </ul>
                        <p v-if="day.foodNote" class="whitespace-pre-line break-words text-sm">{{ day.foodNote }}</p>
                        <div v-if="day.luxuryCount || day.luxuryNote" class="space-y-1 text-sm">
                            <h3 class="font-bold">Refeição luxo<span v-if="day.luxuryCount"> · {{ day.luxuryCount }}</span></h3>
                            <p v-if="day.luxuryNote" class="whitespace-pre-line break-words">{{ day.luxuryNote }}</p>
                        </div>
                        <ul v-if="day.photos.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3" aria-label="Fotos das refeições">
                            <li v-for="(photo, index) in day.photos" :key="index">
                                <a :href="photo" target="_blank" rel="noopener noreferrer" :aria-label="'Ampliar foto da refeição ' + (index + 1)">
                                    <img :src="photo" :alt="'Foto da refeição ' + (index + 1)" loading="lazy" class="aspect-square w-full rounded-xl object-cover" />
                                </a>
                            </li>
                        </ul>
                    </article>
                </div>
            </details>
            <p v-if="!athletes.data.length" class="surface-card p-5 text-brand-muted">Nenhum registro de alimentação encontrado.</p>
            <nav v-if="athletes.last_page > 1" class="flex flex-wrap items-center gap-4 text-sm" aria-label="Paginação dos atletas">
                <Link v-if="athletes.prev_page_url" :href="athletes.prev_page_url" class="font-bold text-brand-gold">Anterior</Link>
                <span>Página {{ athletes.current_page }} de {{ athletes.last_page }}</span>
                <Link v-if="athletes.next_page_url" :href="athletes.next_page_url" class="font-bold text-brand-gold">Próxima</Link>
            </nav>
        </main>
    </AppLayout>
</template>
