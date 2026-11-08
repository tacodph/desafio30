<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { nutriRecommendations } from '@/data/nutriRecommendations';
import { Maximize2 } from 'lucide-vue-next';
const products = nutriRecommendations;
const imageUrl = (file: string) => `/images/alimentacao/nutri_indica/${file}`;
</script>

<template>
    <section class="space-y-6 rounded-2xl bg-[#151515] p-4 text-white sm:p-6" aria-label="A nutri indica!">
        <header class="space-y-3">
            <h3 class="font-display text-3xl font-black uppercase text-yellow-400 sm:text-4xl">A nutri indica!</h3>
            <p class="text-sm leading-relaxed text-white/80">Confira os produtos selecionados pela nutri. Toque na imagem para ver a embalagem ampliada.</p>
        </header>
        <p role="status" class="text-sm text-white/60">{{ products.length }} {{ products.length === 1 ? 'produto' : 'produtos' }}</p>
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <li v-for="product in products" :key="product.file" class="flex min-w-0 flex-col overflow-hidden rounded-2xl border border-yellow-400/40">
                <Dialog>
                    <DialogTrigger as-child>
                        <button type="button" class="block w-full bg-white p-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-yellow-400" :aria-label="'Ampliar ' + product.name + ' · ' + product.detail">
                            <img :src="imageUrl(product.file)" :alt="product.name + ' · ' + product.detail" loading="lazy" decoding="async" class="h-60 w-full object-contain" />
                        </button>
                    </DialogTrigger>
                    <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-2xl overflow-y-auto rounded-2xl border-brand-line2 bg-brand-card text-brand-cream">
                        <DialogHeader><DialogTitle class="pr-6 text-brand-gold">{{ product.name }}</DialogTitle><DialogDescription>{{ product.detail }}</DialogDescription></DialogHeader>
                        <img :src="imageUrl(product.file)" :alt="product.name + ' · ' + product.detail" class="max-h-[65dvh] w-full rounded-xl bg-white object-contain" />
                    </DialogContent>
                </Dialog>
                <div class="flex flex-1 flex-col gap-2 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-yellow-400/70">{{ product.category }}</p>
                    <h4 class="font-display text-xl font-black leading-tight text-yellow-400">{{ product.name }}</h4>
                    <p class="text-sm leading-relaxed text-white/85">{{ product.detail }}</p>
                    <p class="mt-auto flex items-center gap-2 pt-3 text-xs text-yellow-400"><Maximize2 class="size-4" aria-hidden="true" />Toque na imagem para ampliar</p>
                </div>
            </li>
        </ul>
    </section>
</template>
