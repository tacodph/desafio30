<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import NutritionMaterialImage from '@/components/NutritionMaterialImage.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Head } from '@inertiajs/vue3';
import { ChevronDown, Maximize2 } from 'lucide-vue-next';
import { ref } from 'vue';

const openMaterial = ref<string | null>(null);

const yogurtOptions = [
    { id: 'natural', title: 'Opções de iogurte natural', viewBox: '0 0 1199 674', height: 674 },
    { id: 'proteico', title: 'Opções de iogurte proteico', viewBox: '0 678 1199 586', height: 586 },
];

type Material = { id: string; title: string; image: string; cover: string; panel?: 'left' | 'right'; guidance?: string[]; tip: string; combinations: { title: string; ingredients: string }[] };

const materials: Material[] = [
    {
        id: 'iogurte',
        cover: '/images/alimentacao/capa-iogurte-v1.png',
        title: 'Comprei iogurte. Como usar?',
        image: '/images/alimentacao/COMBINANDOaLIMENTOS%20(2).jpeg',
        tip: 'O iogurte é uma ótima fonte de proteína, que ajuda na saciedade. Combine com frutas (carboidrato) e castanhas, chia ou aveia (gordura boa e fibras).',
        combinations: [
            { title: 'Overnight oats', ingredients: 'Iogurte + aveia + fruta + castanhas.' },
            { title: 'Creme de iogurte', ingredients: 'Iogurte batido com fruta congelada, como morango.' },
            { title: 'Iogurte com fruta e castanhas', ingredients: 'Iogurte + fruta picada, como banana + castanhas ou chia.' },
            { title: 'Iogurte com leite em pó', ingredients: 'Iogurte + leite em pó + fruta, como manga ou mamão.' },
            { title: 'Iogurte com fruta e canela', ingredients: 'Iogurte + fruta picada, como maçã + canela + castanhas.' },
        ],
    },
    {
        id: 'queijos',
        cover: '/images/alimentacao/capa-queijos-v1.png',
        title: 'Comprei queijos. Como usar?',
        image: '/images/alimentacao/COMBINANDOaLIMENTOS%20(3).jpeg',
        tip: 'Os queijos adicionam proteína e sabor às refeições. Prefira versões mais magras, como minas, cottage, ricota ou muçarela light.',
        combinations: [
            { title: 'Pão com cottage e geleia de fruta', ingredients: 'Pão integral + cottage + geleia sem açúcar (1 colher de chá).' },
            { title: 'Avocado toast', ingredients: 'Pão integral + abacate amassado + ovo + temperos.' },
            { title: 'Crepioca prática', ingredients: 'Ovo + tapioca + queijo + tomate e outros temperos.' },
            { title: 'Ovos mexidos com queijo', ingredients: 'Ovos + queijo minas ou muçarela + pão integral + fruta.' },
            { title: 'Pão com queijo e tomate', ingredients: 'Pão integral + queijo minas, muçarela ou branco + tomate + orégano.' },
        ],
    },
    {
        id: 'ovos',
        cover: '/images/alimentacao/capa-ovos-v1.png',
        title: 'Como variar os ovos?',
        image: '/images/alimentacao/COMBINANDOaLIMENTOS%20(4).jpeg',
        tip: 'O ovo é uma proteína de alto valor biológico, versátil e prática. Combine com fontes de carboidrato, aveia, tapioca e vegetais para refeições mais completas.',
        combinations: [
            { title: 'Patê de ovos em biscoito de arroz', ingredients: 'Ovos cozidos + creme de ricota ou iogurte + temperos + biscoito de arroz.' },
            { title: 'Panqueca de banana', ingredients: 'Banana + ovo + aveia + canela. Prepare em frigideira antiaderente.' },
            { title: 'Omelete nutritivo', ingredients: 'Ovos + queijo + vegetais, como espinafre, tomate e cebola + pão integral ou outra fonte de carboidrato.' },
            { title: 'Ovos cozidos com salada', ingredients: 'Ovos cozidos + mix de folhas + tomate-cereja + azeite em pequena quantidade.' },
            { title: 'Pão com ovo e folhas', ingredients: 'Pão integral + ovo mexido + folhas, como rúcula ou alface.' },
        ],
    },
    {
        id: 'whey',
        cover: '/images/alimentacao/capa-whey-v1.png',
        title: 'Comprei whey protein. Como usar?',
        image: '/images/alimentacao/COMBINANDOaLIMENTOS%20(1).jpeg',
        tip: 'O whey é uma forma prática de aumentar a ingestão de proteína e pode ser usado em vitaminas, cremes e preparações do dia a dia.',
        combinations: [
            { title: 'Vitamina proteica', ingredients: 'Whey + fruta, como morango + leite ou bebida vegetal (200 ml).' },
            { title: 'Shake de banana e cacau', ingredients: 'Whey + banana + cacau 100% + leite ou bebida vegetal.' },
            { title: 'Vitamina tropical', ingredients: 'Whey + manga ou outra fruta + leite ou bebida vegetal.' },
            { title: 'Iogurte proteico', ingredients: 'Iogurte + whey + fruta picada + castanhas.' },
            { title: 'Creme de aveia proteico', ingredients: 'Aveia + whey + banana + canela. Prepare no fogão ou no micro-ondas.' },
        ],
    },
    {
        id: 'aveia',
        cover: '/images/alimentacao/capa-aveia-v1.png',
        title: 'Comprei aveia. Como usar?',
        image: '/images/alimentacao/comprei-aveia.png',
        tip: 'A aveia pode ser incluída no café da manhã, lanche, pré ou pós-treino, de acordo com a sua rotina, objetivos e prescrição nutricional.',
        combinations: [
            { title: 'Mingau de aveia', ingredients: 'Aveia + leite ou bebida vegetal + canela. Cremoso, prático e sustentador.' },
            { title: 'Vitamina energética', ingredients: 'Aveia + banana + whey protein + leite ou bebida vegetal. Ótima opção no pré ou pós-treino.' },
            { title: 'Overnight oats', ingredients: 'Aveia + iogurte natural ou leite + fruta picada. Deixe na geladeira na noite anterior.' },
            { title: 'Panqueca de aveia', ingredients: 'Aveia + ovo + banana + canela. Uma opção prática e nutritiva.' },
            { title: 'Aveia com iogurte e castanhas', ingredients: 'Aveia + iogurte natural + castanhas + fruta. Combinação com proteína, fibras e gordura boa.' },
        ],
    },
    {
        id: 'pao',
        cover: '/images/alimentacao/capa-pao-v1.png',
        title: 'Como escolher o melhor pão integral?',
        image: '/images/alimentacao/PAO.jpeg',
        tip: '',
        combinations: [],
    },
    {
        id: 'gorduras',
        cover: '/images/alimentacao/capa-gorduras-v1.png',
        title: 'Gorduras boas. Por que incluir na alimentação?',
        image: '/images/alimentacao/GORDURAS.jpeg',
        tip: '',
        combinations: [],
    },
    {
        id: 'substituir-ovos',
        cover: '/images/alimentacao/capa-substituir-ovos-v1.png',
        title: 'Não gosto de ovos. O que posso colocar no lugar?',
        image: '/images/alimentacao/quijos%20e%20substitui%C3%A7%C3%A3o%20ovos%20.jpeg',
        panel: 'right',
        tip: '',
        combinations: [],
    },
    {
        id: 'snacks',
        cover: '/images/alimentacao/capa-snacks-v1.png',
        title: 'Snacks saudáveis',
        image: '/images/alimentacao/snacks-saudaveis-v2.png',
        tip: '',
        combinations: [],
        guidance: [
            'Estas são opções para eventualidades, quando bater a vontade de comer um petisco ou quando não for possível fazer uma refeição mais nutritiva. São alternativas com ingredientes saudáveis que podem ser encaixadas na alimentação, mas isso não significa que sejam de baixa caloria.',
            'Prefira uma porção pequena, em torno de 30 a 40 g, em vez do pacote inteiro. Dependendo do tamanho da embalagem, essa quantidade pode corresponder a cerca de meio pacotinho: confira o peso indicado no rótulo.',
            'Pense nesses snacks como uma alternativa ocasional. No dia a dia, dê prioridade a refeições mais nutritivas e completas.',
        ],
    },
];
</script>

<template>
    <Head title="Cantinho da Nutri" />

    <AppLayout :breadcrumbs="[
        { title: 'Cantinho da Nutri', href: '/cantinho-da-nutri' },
    ]">
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-3">
                <div class="eyebrow">Materiais e orientações</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] text-brand-gold">Cantinho da Nutri</h1>
            </header>

            <div class="divide-y divide-brand-line">
                <section v-for="(material, index) in materials" :key="material.id" class="py-5 first:pt-0">
                    <button
                        type="button"
                        class="grid w-full grid-cols-[5rem_1fr_auto] items-center gap-3 rounded-xl text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold sm:grid-cols-[9rem_1fr_auto] sm:gap-6"
                        :aria-expanded="openMaterial === material.id"
                        :aria-controls="'combinations-' + material.id"
                        @click="openMaterial = openMaterial === material.id ? null : material.id"
                    >
                        <img :src="material.cover" alt="" width="144" height="144" loading="lazy" decoding="async" class="aspect-square w-full rounded-xl object-cover" />
                        <span class="min-w-0 space-y-3">
                            <span class="block text-lg font-bold leading-snug sm:text-2xl">{{ index + 1 }}. {{ material.title }}</span>
                            <span class="inline-block text-sm font-semibold text-brand-gold underline decoration-dotted underline-offset-4">
                                {{ openMaterial === material.id ? 'Fechar material' : 'Abrir material' }}
                            </span>
                        </span>
                        <ChevronDown class="size-5 shrink-0 text-brand-gold transition-transform" :class="{ 'rotate-180': openMaterial === material.id }" aria-hidden="true" />
                    </button>

                    <div :id="'combinations-' + material.id" v-show="openMaterial === material.id" class="mt-5 space-y-4">
                        <aside v-if="material.guidance" class="space-y-3 rounded-xl border border-brand-gold/30 bg-brand-gold/5 p-4 sm:p-5">
                            <h3 class="font-bold text-brand-gold">Como encaixar essas opções na rotina?</h3>
                            <p v-for="paragraph in material.guidance" :key="paragraph" class="text-sm leading-relaxed text-brand-cream">{{ paragraph }}</p>
                        </aside>

                        <div v-if="material.id === 'iogurte'" class="flex flex-wrap gap-3">
                            <Dialog v-for="option in yogurtOptions" :key="option.id">
                                <DialogTrigger as-child>
                                    <button type="button" class="inline-flex items-center justify-center rounded-xl bg-brand-gold px-4 py-3 text-sm font-bold text-brand-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold focus-visible:ring-offset-2">
                                        {{ option.title }}
                                    </button>
                                </DialogTrigger>
                                <DialogContent class="max-h-[95dvh] w-[calc(100vw-1.5rem)] max-w-6xl overflow-y-auto border-brand-line2 bg-brand-card p-3 text-brand-cream sm:p-5">
                                    <DialogHeader>
                                        <DialogTitle class="pr-6">{{ option.title }}</DialogTitle>
                                        <DialogDescription class="text-brand-muted">Confira as opções no material. No celular, deslize a imagem para visualizar os detalhes.</DialogDescription>
                                    </DialogHeader>
                                    <div class="max-w-full overflow-x-auto rounded-xl" tabindex="0" :aria-label="option.title">
                                        <svg :viewBox="option.viewBox" width="1199" :height="option.height" role="img" :aria-label="option.title" overflow="hidden" class="h-auto w-full min-w-[756px]">
                                            <image href="/images/alimentacao/opcoes-iogurtes.png" width="1199" height="1312" />
                                        </svg>
                                    </div>
                                </DialogContent>
                            </Dialog>
                        </div>

                        <Dialog v-if="material.id === 'queijos'">
                            <DialogTrigger as-child>
                                <button type="button" class="inline-flex items-center gap-2 rounded-xl bg-brand-gold px-4 py-3 text-sm font-bold text-brand-ink focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold focus-visible:ring-offset-2">
                                    Como escolher o queijo?
                                </button>
                            </DialogTrigger>
                            <DialogContent class="max-h-[95dvh] w-[calc(100vw-1.5rem)] max-w-4xl overflow-y-auto border-brand-line2 bg-brand-card p-3 text-brand-cream sm:p-5">
                                <DialogHeader>
                                    <DialogTitle class="pr-6">Como escolher o queijo?</DialogTitle>
                                    <DialogDescription class="text-brand-muted">Informações sobre os tipos de queijo e como escolher e usar no dia a dia. No celular, deslize a imagem para consultar o material completo.</DialogDescription>
                                </DialogHeader>
                                <div class="max-w-full overflow-x-auto rounded-xl" tabindex="0" aria-label="Informações ampliadas sobre queijos">
                                    <NutritionMaterialImage src="/images/alimentacao/quijos%20e%20substitui%C3%A7%C3%A3o%20ovos%20.jpeg" panel="left" alt="Queijos na rotina: como escolher e usar?" class="h-auto w-full min-w-[756px]" />
                                </div>
                            </DialogContent>
                        </Dialog>

                        <Dialog>
                            <DialogTrigger as-child>
                                <button type="button" class="group block w-full space-y-2 rounded-xl text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold" :aria-label="'Ampliar material · ' + material.title">
                                    <NutritionMaterialImage :src="material.image" :panel="material.panel" :alt="material.title" loading="lazy" class="h-auto w-full rounded-xl" />
                                    <span class="inline-flex items-center gap-2 text-sm font-semibold text-brand-gold">
                                        <Maximize2 class="size-4" aria-hidden="true" /> Clique na imagem para ampliar
                                    </span>
                                </button>
                            </DialogTrigger>
                            <DialogContent class="max-h-[95dvh] w-[calc(100vw-1.5rem)] max-w-6xl overflow-y-auto border-brand-line2 bg-brand-card p-3 text-brand-cream sm:p-5">
                                <DialogHeader>
                                    <DialogTitle class="pr-6">{{ material.title }}</DialogTitle>
                                    <DialogDescription class="text-brand-muted">{{ material.combinations.length ? 'Cinco combinações práticas e saborosas.' : 'Material com orientações e exemplos visuais.' }} No celular, deslize a imagem para consultar o material completo.</DialogDescription>
                                </DialogHeader>
                                <div class="max-w-full overflow-x-auto rounded-xl" tabindex="0" :aria-label="'Material ampliado · ' + material.title">
                                    <NutritionMaterialImage :src="material.image" :panel="material.panel" :alt="material.title" class="h-auto w-full min-w-[756px]" />
                                </div>
                            </DialogContent>
                        </Dialog>

                        <details v-if="material.combinations.length" class="surface-card p-4">
                            <summary class="cursor-pointer text-sm font-semibold text-brand-gold">Ver receitas em texto</summary>
                            <ol class="mt-4 grid gap-3 sm:grid-cols-2">
                                <li v-for="(combination, combinationIndex) in material.combinations" :key="combination.title" class="flex items-start gap-3 rounded-lg border border-brand-line2 p-3">
                                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-brand-gold text-sm font-bold text-brand-ink">{{ combinationIndex + 1 }}</span>
                                    <div class="min-w-0 space-y-2">
                                        <h3 class="font-bold">{{ combination.title }}</h3>
                                        <p class="text-sm leading-relaxed text-brand-muted">{{ combination.ingredients }}</p>
                                    </div>
                                </li>
                            </ol>
                            <p class="mt-4 text-sm leading-relaxed text-brand-muted"><strong class="text-brand-gold">Dica da Nutri</strong> · {{ material.tip }}</p>
                        </details>
                    </div>
                </section>
            </div>

            <p class="rounded-xl border border-brand-line2 p-4 text-xs leading-relaxed text-brand-muted">
                Sugestões de receitas. As quantidades podem variar de acordo com a sua prescrição individual, seus objetivos e rotina.
            </p>
        </main>
    </AppLayout>
</template>
