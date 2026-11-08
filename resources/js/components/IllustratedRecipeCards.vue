<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

defineProps<{
    image: string;
    width: number;
    height: number;
    meals: { title: string; ingredients: string; viewBox: string }[];
}>();
</script>

<template>
    <ol class="grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <li v-for="meal in meals" :key="meal.title" class="min-w-0 overflow-hidden rounded-2xl border border-[#d7bf8c]/60">
            <Dialog>
                <DialogTrigger as-child>
                    <button type="button" class="block w-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-yellow-400" :aria-label="'Ampliar imagem de ' + meal.title">
                        <svg :viewBox="meal.viewBox" :width="meal.viewBox.split(' ')[2]" :height="meal.viewBox.split(' ')[3]" class="block h-auto w-full" role="img" :aria-label="meal.title" overflow="hidden"><image :href="image" :width="width" :height="height" /></svg>
                    </button>
                </DialogTrigger>
                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-lg overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                    <DialogHeader><DialogTitle>{{ meal.title }}</DialogTitle><DialogDescription>{{ meal.ingredients }}</DialogDescription></DialogHeader>
                    <svg :viewBox="meal.viewBox" :width="meal.viewBox.split(' ')[2]" :height="meal.viewBox.split(' ')[3]" class="block h-auto w-full rounded-xl" role="img" :aria-label="meal.title" overflow="hidden"><image :href="image" :width="width" :height="height" /></svg>
                </DialogContent>
            </Dialog>
            <div class="space-y-3 p-4 text-center">
                <h4 class="font-display text-lg font-black uppercase leading-tight">{{ meal.title }}</h4>
                <p class="text-sm leading-relaxed text-white/90">{{ meal.ingredients }}</p>
            </div>
        </li>
    </ol>
</template>
