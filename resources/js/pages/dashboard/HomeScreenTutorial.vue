<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import guideImage from '@/assets/tutoriais/adicionar-atalho-iphone-v2.png';
import { Head } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { CheckCircle2, Maximize2, Smartphone } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const siteUrl = ref('');
onMounted(() => { siteUrl.value = window.location.origin; });
const cropStyle = (viewBox: string) => {
    const [x, y, width, height] = viewBox.split(' ').map(Number);
    return {
        width: `${1199 / width * 100}%`,
        height: `${1312 / height * 100}%`,
        left: `${-x / width * 100}%`,
        top: `${-y / height * 100}%`,
    };
};
const cropRatio = (viewBox: string) => viewBox.split(' ').slice(2).join(' / ');
const steps = [
    { title: 'Abra o Safari', text: 'Acesse o site do Desafio da Virada no Safari do seu iPhone.', viewBox: '14 276 290 516' },
    { title: 'Toque em Compartilhar', text: 'Toque no ícone de um quadrado com uma seta para cima, destacado na imagem.', viewBox: '320 276 289 516' },
    { title: 'Adicionar à Tela de Início', text: 'Role o menu e selecione “Adicionar à Tela de Início”.', viewBox: '620 276 290 516' },
    { title: 'Confirme em Adicionar', text: 'Confira o nome “Desafio da Virada” e toque em “Adicionar”.', viewBox: '919 276 280 516' },
];
</script>

<template>
    <Head title="Adicionar atalho" />
    <AppLayout :breadcrumbs="[{ title: 'Boas-vindas', href: '/atleta/boas-vindas/video' }, { title: 'Tutoriais', href: '#' }, { title: 'Adicionar atalho', href: '/atleta/boas-vindas/tutoriais/tela-inicial' }]">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-8">
            <header class="space-y-3">
                <div class="eyebrow">Boas-vindas · Tutoriais</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] leading-tight text-brand-gold">Como adicionar o site à tela inicial do celular.</h1>
                <p class="text-brand-muted">Como criar o atalho no iPhone.</p>
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-gold/30 bg-brand-gold/10 px-4 py-2 text-sm font-bold text-brand-gold"><Smartphone class="size-4" aria-hidden="true" />Faça pelo Safari</span>
            </header>
            <ol class="grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Passo a passo para adicionar o desafio no iPhone">
                <li v-for="(step, index) in steps" :key="step.title" class="surface-card flex min-w-0 flex-col gap-4 p-4">
                    <div class="relative mx-auto w-full max-w-60 overflow-hidden rounded-3xl bg-brand-cream" :style="{ aspectRatio: cropRatio(step.viewBox) }">
                        <img :src="guideImage" :alt="'Exemplo ilustrado: ' + step.title" :style="cropStyle(step.viewBox)" class="absolute block max-w-none" decoding="async" />
                    </div>
                    <div class="space-y-3 text-center">
                        <span class="mx-auto flex size-10 items-center justify-center rounded-full bg-brand-gold text-xl font-black text-brand-ink">{{ index + 1 }}</span>
                        <h2 class="text-lg font-black">{{ step.title }}</h2>
                        <p class="text-sm leading-relaxed text-brand-cream/80">{{ step.text }}</p>
                        <a v-if="index === 0 && siteUrl" :href="siteUrl" class="inline-block break-all text-sm font-bold text-brand-gold underline">{{ siteUrl }}</a>
                    </div>
                </li>
            </ol>
            <aside class="flex flex-col items-start gap-5 rounded-3xl border border-brand-gold/40 bg-brand-gold/5 p-5 sm:flex-row sm:items-center sm:p-7">
                <img src="/images/logo-mark-30-virada.png" alt="Ícone do Desafio da Virada" width="80" height="80" class="size-20 shrink-0 rounded-2xl object-contain" />
                <div class="space-y-2"><h2 class="flex items-center gap-2 text-3xl font-black text-brand-gold"><CheckCircle2 class="size-8" aria-hidden="true" />Pronto!</h2><p class="text-base leading-relaxed">O ícone do <strong>Desafio da Virada</strong> já estará na tela inicial do seu celular, como um aplicativo. Toque nele para acessar a plataforma.</p></div>
            </aside>
            <Dialog>
                <DialogTrigger as-child><button type="button" class="flex w-fit items-center gap-2 rounded-xl border border-brand-gold/40 bg-brand-gold/10 px-4 py-3 text-sm font-bold text-brand-gold"><Maximize2 class="size-4" aria-hidden="true" />Ver guia ilustrado completo</button></DialogTrigger>
                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-4xl overflow-y-auto rounded-2xl border-brand-line2 bg-brand-card text-brand-cream">
                    <DialogHeader><DialogTitle>Adicionar atalho no iPhone</DialogTitle><DialogDescription>Quatro etapas para deixar o Desafio da Virada na tela inicial.</DialogDescription></DialogHeader>
                    <img :src="guideImage" alt="Guia ilustrado: abrir o site no Safari, compartilhar, adicionar à tela de início e confirmar." class="h-auto w-full rounded-xl" />
                </DialogContent>
            </Dialog>
            <section class="surface-card space-y-4 p-4 sm:p-6" aria-label="Tutorial de atalho na tela inicial">
                <h2 class="text-xl font-bold text-brand-gold">Veja também o vídeo do tutorial</h2>
                <video controls playsinline preload="none" class="max-h-[70dvh] w-full rounded-xl bg-black" aria-label="Como adicionar o site à tela inicial do celular">
                    <source src="/videos/atalhoSafari.mp4" type="video/mp4" />
                    Seu navegador não suporta a reprodução deste vídeo.
                    <a href="/videos/atalhoSafari.mp4">Abrir vídeo do tutorial</a>
                </video>
                <p class="text-sm text-brand-muted">Pause e reveja o vídeo para acompanhar cada etapa no seu celular.</p>
            </section>
        </main>
    </AppLayout>
</template>
