<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ponto de partida', href: '#' },
    { title: 'Primeiro dia', href: '/atleta/ponto-de-partida/primeiro-dia' },
    { title: 'Como tirar suas medidas', href: '/atleta/ponto-de-partida/como-tirar-medidas' },
];

const preparo = [
    'Use uma fita métrica flexível.',
    'Fique em pé, com postura natural e abdômen relaxado.',
    'Mantenha a fita encostada na pele, sem apertar.',
    'Faça a leitura com a fita reta e paralela ao chão.',
    'Registre as medidas em centímetros.',
    'Sempre que possível, repita nas mesmas condições e nos mesmos pontos.',
];

const medidas = [
    {
        sigla: 'CC',
        nome: 'Cintura',
        imagem: '/images/medidas/cintura.webp',
        texto: 'Meça na parte mais estreita da cintura, que geralmente fica acima do umbigo e abaixo das costelas.',
    },
    {
        sigla: 'CA',
        nome: 'Abdômen',
        imagem: '/images/medidas/abdomen.webp',
        texto: 'Meça 2 dedos abaixo do umbigo, com o abdômen relaxado, sem prender a respiração.',
    },
    {
        sigla: 'CQ',
        nome: 'Quadril',
        imagem: '/images/medidas/quadril.webp',
        texto: 'Meça na parte mais larga do quadril, que geralmente fica na altura do glúteo.',
    },
];

const fotos = [
    { nome: 'Frente', imagem: '/images/medidas/foto-frente.webp', texto: 'De frente, braços estendidos para a frente, paralelos ao chão.' },
    { nome: 'Lado', imagem: '/images/medidas/foto-lado.webp', texto: 'De lado, braços estendidos para a frente, paralelos ao chão.' },
    { nome: 'Costas', imagem: '/images/medidas/foto-costas.jpg', texto: 'De costas, com os braços relaxados ao lado do corpo.' },
];
</script>

<template>
    <Head title="Como tirar suas medidas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-1">
                <div class="eyebrow">Material de apoio</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)]">Como tirar suas medidas</h1>
                <p class="m-0 text-brand-muted">
                    As medidas ajudam a acompanhar a sua evolução ao longo do desafio. Meça nesses pontos e volte ao Primeiro dia para registrar.
                </p>
            </div>

            <section class="surface-card flex flex-col gap-6 p-6">
                <div class="flex flex-col gap-2">
                    <h2 class="m-0 text-sm font-bold uppercase tracking-wider text-brand-cream">Antes de começar</h2>
                    <ul class="m-0 flex list-none flex-col gap-1.5 p-0 text-sm text-brand-muted-3">
                        <li v-for="item in preparo" :key="item" class="flex gap-2">
                            <span class="text-brand-gold">✓</span>
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>

                <div class="flex flex-col gap-4">
                    <div
                        v-for="(medida, i) in medidas"
                        :key="medida.sigla"
                        class="flex flex-col gap-3 rounded-xl border border-brand-line2 bg-brand-card2 p-3 sm:flex-row"
                    >
                        <img :src="medida.imagem" :alt="`Como medir: ${medida.nome}`" class="h-32 w-full rounded-lg object-cover sm:w-44" loading="lazy" />
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <span class="flex size-7 items-center justify-center rounded-full bg-brand-gold text-sm font-bold text-brand-ink">{{
                                    i + 1
                                }}</span>
                                <span class="font-bold text-brand-cream">{{ medida.sigla }} · {{ medida.nome }}</span>
                            </div>
                            <p class="m-0 text-sm text-brand-muted">{{ medida.texto }}</p>
                            <p class="m-0 text-xs text-brand-muted-2">Lance no campo “{{ medida.nome }} (cm)”.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <h2 class="m-0 text-sm font-bold uppercase tracking-wider text-brand-cream">Foto de evolução</h2>
                    <p class="m-0 text-sm text-brand-muted">
                        Fotos de frente, de lado e de costas, com top e short (mulheres) ou sunga (homens). Use um lugar bem iluminado, fundo neutro,
                        câmera na altura do tronco e postura natural, sem filtros. Repita nas mesmas condições nos próximos registros.
                    </p>
                    <div class="grid grid-cols-3 gap-2">
                        <figure v-for="foto in fotos" :key="foto.nome" class="m-0 flex flex-col gap-1">
                            <img :src="foto.imagem" :alt="`Foto de evolução: ${foto.nome}`" class="aspect-[1/2] w-full rounded-lg object-cover" loading="lazy" />
                            <figcaption class="text-center text-xs font-semibold text-brand-cream">{{ foto.nome }}</figcaption>
                            <span class="text-center text-[11px] leading-tight text-brand-muted-2">{{ foto.texto }}</span>
                        </figure>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
