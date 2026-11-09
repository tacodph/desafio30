<script setup lang="ts">
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

type Rule = { id: number; key: string; group: string; label: string; points: number };

const props = defineProps<{
    daily: Rule[];
    bonus: Rule[];
    canEdit: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Painel', href: '/dashboard' },
    { title: 'Pontuação', href: '/pontuacao' },
];

const form = useForm({
    daily: props.daily,
    bonus: props.bonus,
});

const save = () => form.put(route('pontuacao.update'), { preserveScroll: true });

const fieldClass = 'h-11 rounded-xl border border-brand-line2 bg-brand-card2 px-3 font-normal text-brand-cream';
</script>

<template>
    <Head title="Pontuação" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6" @submit.prevent="canEdit ? save() : undefined">
            <h1 class="display-title text-[clamp(36px,5vw,56px)] text-brand-gold">Confira sua pontuação no desafio.</h1>

            <section class="surface-card grid gap-4 p-5 sm:p-6">
                <h2 class="font-display m-0 text-2xl font-extrabold uppercase">Pontuação diária</h2>
                <template v-if="canEdit">
                    <div v-for="item in form.daily" :key="item.id" class="grid gap-3 border-b border-brand-line2 py-3 last:border-0 last:pb-0 sm:grid-cols-[1fr_6rem] sm:items-center">
                        <input v-model="item.label" required maxlength="160" :class="fieldClass" />
                        <input v-model.number="item.points" type="number" min="0" max="50" required :class="fieldClass + ' text-center font-display text-xl font-black text-brand-gold'" />
                    </div>
                </template>
                <div v-else v-for="item in daily" :key="item.id" class="flex items-baseline justify-between gap-4 border-b border-brand-line2 py-3 last:border-0 last:pb-0">
                    <span class="text-sm leading-snug">{{ item.label }}</span>
                    <span class="font-display text-xl font-black text-brand-gold">+{{ item.points }}</span>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-t border-brand-line2 pt-3">
                    <span class="text-sm leading-snug">Não usei refeição luxo essa semana</span>
                    <span class="shrink-0 font-display text-xl font-black text-brand-gold">+5 pontos</span>
                </div>
            </section>

            <Button v-if="canEdit" type="submit" class="w-fit" :disabled="form.processing">Salvar pontuação</Button>
        </form>
    </AppLayout>
</template>
