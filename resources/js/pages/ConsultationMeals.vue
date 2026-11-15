<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DinnerSuggestionsContent from '@/components/DinnerSuggestionsContent.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { consultationMealGroups, consultationMealImage } from '@/data/consultationMeals';
import { Head } from '@inertiajs/vue3';
import { Maximize2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const title = 'Sugestões de refeições reais - Jantar';
const activeGroup = ref('todos');
const visibleGroups = computed(() => {
    let number = 0;
    return consultationMealGroups
        .filter((group) => activeGroup.value === 'todos' || group.id === activeGroup.value)
        .map((group) => ({ ...group, meals: group.meals.map((meal) => ({ ...meal, number: ++number })) }));
});
const totalMeals = consultationMealGroups.reduce((total, group) => total + group.meals.length, 0);
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Alimentação', href: '/meus-objetivos/alimentacao' }, { title, href: '/alimentacao/refeicoes-reais-em-consultorio' }]">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-3">
                <div class="eyebrow">Alimentação</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] leading-tight text-brand-gold">{{ title }}</h1>
            </header>

            <DinnerSuggestionsContent />

            <aside class="surface-card space-y-4 p-5 leading-relaxed text-brand-muted sm:p-6">
                <h2 class="font-display text-2xl font-bold text-brand-gold">FOTOS DE REFEIÇÕES REAIS PARA INSPIRAR SUAS ESCOLHAS</h2>
                <p>Aqui você encontra fotos de refeições minhas e dos meus pacientes, com combinações e preparações do nosso dia a dia.</p>
                <p>São exemplos reais, com alimentos simples, acessíveis e escolhas possíveis de reproduzir na sua rotina.</p>
                <p>Use essas referências para buscar ideias, experimentar combinações e deixar sua alimentação mais variada!</p>
                <p>Lembre-se de fazer a lista de compras de acordo com suas escolhas da semana.</p>
            </aside>

            <div class="flex flex-wrap gap-2" role="group" aria-label="Filtrar por tipo de prato">
                <button type="button" :aria-pressed="activeGroup === 'todos'" class="min-h-11 rounded-full border px-4 py-2 text-sm font-bold" :class="activeGroup === 'todos' ? 'border-yellow-400 bg-yellow-400 text-black' : 'border-brand-line2 bg-brand-card text-brand-cream hover:border-brand-gold'" @click="activeGroup = 'todos'">Todos ({{ totalMeals }})</button>
                <button v-for="group in consultationMealGroups" :key="group.id" type="button" :aria-pressed="activeGroup === group.id" class="min-h-11 rounded-full border px-4 py-2 text-sm font-bold" :class="activeGroup === group.id ? 'border-yellow-400 bg-yellow-400 text-black' : 'border-brand-line2 bg-brand-card text-brand-cream hover:border-brand-gold'" @click="activeGroup = group.id">{{ group.title }} ({{ group.meals.length }})</button>
            </div>

            <section v-for="group in visibleGroups" :key="group.id" class="space-y-5 rounded-3xl border border-yellow-400/60 bg-[#191919] p-4 text-white sm:p-5" :aria-labelledby="`meals-${group.id}`">
                <h2 :id="`meals-${group.id}`" class="rounded-3xl bg-yellow-400 px-4 py-3 text-center font-display text-2xl font-black uppercase leading-tight text-black">{{ group.title }}</h2>
                <ul class="grid items-start gap-4 lg:grid-cols-2">
                    <li v-for="meal in group.meals" :key="meal.id" class="flex h-full flex-col items-center gap-3 rounded-3xl border border-white/10 bg-gradient-to-r from-[#131313] to-[#252525] p-3 sm:flex-row">
                        <Dialog>
                            <DialogTrigger as-child>
                                <button type="button" class="group relative w-full shrink-0 overflow-hidden rounded-2xl bg-black/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-yellow-400 sm:w-40 xl:w-48" :aria-label="`Ampliar foto: ${meal.title}`">
                                    <img :src="consultationMealImage(meal.id)" :alt="meal.title" loading="lazy" decoding="async" class="aspect-square w-full object-contain transition-transform group-hover:scale-105" />
                                    <span class="absolute bottom-2 right-2 rounded-full bg-black/80 p-2 text-yellow-400"><Maximize2 class="size-4" aria-hidden="true" /></span>
                                </button>
                            </DialogTrigger>
                            <DialogContent class="max-h-[90dvh] max-w-3xl overflow-y-auto border-brand-line2 bg-[#191919] text-white">
                                <DialogHeader>
                                    <DialogTitle class="pr-6 text-brand-gold">{{ meal.title }}</DialogTitle>
                                    <DialogDescription class="text-brand-muted">{{ meal.sides.join(' · ') }}. Descrição aproximada com base na foto.</DialogDescription>
                                </DialogHeader>
                                <img :src="consultationMealImage(meal.id)" :alt="meal.title" loading="lazy" class="mx-auto max-h-[65dvh] w-full rounded-xl object-contain" />
                            </DialogContent>
                        </Dialog>
                        <div class="flex w-full items-start gap-3 py-2">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-yellow-400 text-xl font-black text-black" aria-hidden="true">{{ meal.number }}</span>
                            <div class="min-w-0 space-y-1 text-base leading-relaxed">
                                <h3 class="font-bold">{{ meal.title }}</h3>
                                <p v-for="side in meal.sides" :key="side">+ {{ side }}</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </section>
        </main>
    </AppLayout>
</template>
