<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

const situations = [
    { title: 'Bar / espetinho', tips: [
        'Espetinho de carne magra (patinho, alcatra) ou queijo coalho.',
        'Troque a batata frita por mandioca cozida, vinagrete ou salada.',
        'Suco natural sem açúcar (adoce a gosto, se precisar).',
    ] },
    { title: 'Açaí', tips: [
        'Prefira açaí puro, sem xarope de guaraná.',
        'Peça para bater com banana.',
        'Acompanhamentos: leite em pó, whey, amendoim ou castanhas.',
        'Evite adicionais açucarados, como confeitos e caldas.',
    ] },
    { title: 'Opções práticas de mercado', tips: [
        'Pote de salada de frutas + iogurte proteico.',
        'Bebida láctea proteica (YoPRO, Piracanjuba, etc.).',
        'Iogurte natural + fruta + sachê de whey.',
        'Biscoito de arroz (porção individual) com pasta de amendoim.',
        'Castanhas em porção individual.',
        'Frutas práticas: banana, maçã e uva.',
    ] },
    { title: 'Árabe', tips: [
        'Boas escolhas: carnes magras (kafta, frango), pasta de grão-de-bico (homus), tabule e saladas.',
        'Evite frituras e molhos muito calóricos, como maionese e pastas cremosas.',
    ] },
    { title: 'Café da manhã / padaria', tips: [
        'Boas opções: ovos mexidos, pão com ovo, café com leite e vitamina de frutas sem açúcar.',
        'Sempre que possível, acrescente uma fonte de proteína.',
        'Evite salgados fritos e peça preparações sem manteiga.',
    ] },
    { title: 'Restaurante / churrasco', tips: [
        'Escolha carnes magras: alcatra, contrafilé, patinho e maminha.',
        'Evite costela, linguiça, pão de alho e maionese.',
        'Opte por acompanhamentos mais leves: arroz, feijão, salada ou legumes grelhados.',
    ] },
    { title: 'Massas', tips: [
        'Prefira massas integrais.',
        'Combine com uma boa fonte de proteína: frango, carne magra ou atum.',
        'Inclua vegetais.',
        'Pode adicionar queijo em pequena quantidade.',
        'Evite molhos brancos, muito cremosos ou com excesso de queijo.',
    ] },
    { title: 'Opções no shopping', tips: [
        'Monte um prato no estilo bowl: proteína + carboidrato + salada ou legumes.',
        'Boas escolhas: grelhados, saladas completas, bowls (frango, carne ou peixe com grãos) e comida a quilo.',
    ] },
];
const panels = ['8 255 528 254', '544 255 534 254', '8 519 528 283', '544 519 534 283', '8 815 528 255', '544 815 534 255', '8 1082 528 272', '544 1082 534 272'];
</script>

<template>
    <Head title="Escolhendo melhor fora de casa" />
    <AppLayout :breadcrumbs="[{ title: 'Alimentação', href: '#' }, { title: 'Escolhendo melhor fora de casa', href: '/alimentacao/escolhendo-melhor-fora-de-casa' }]">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-2">
                <div class="eyebrow">Alimentação</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] text-brand-gold">Escolhendo melhor fora de casa</h1>
                <p class="text-sm text-brand-muted">Sugestões para fazer boas escolhas mesmo se alimentando fora de casa</p>
            </header>
            <section class="grid items-start gap-5 md:grid-cols-2" aria-label="Orientações ilustradas para comer fora de casa">
                <article v-for="(situation, index) in situations" :key="situation.title" class="surface-card min-w-0 space-y-3 p-3">
                    <h2 class="text-lg font-bold">{{ situation.title }}</h2>
                    <Dialog>
                        <DialogTrigger as-child>
                            <button type="button" class="block w-full overflow-hidden rounded-xl transition-opacity hover:opacity-90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-gold" :aria-label="`Ampliar ${situation.title}`">
                                <svg :viewBox="panels[index]" class="block h-auto w-full" role="img" :aria-label="`Guia ilustrado: ${situation.title}`" overflow="hidden">
                                    <image href="/images/alimentacao/escolhendo-melhor-fora-de-casa.png" width="1086" height="1448" />
                                </svg>
                            </button>
                        </DialogTrigger>
                        <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-3xl overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                            <DialogHeader>
                                <DialogTitle class="pr-6 text-xl leading-snug text-brand-gold">{{ situation.title }}</DialogTitle>
                                <DialogDescription class="text-brand-muted">Escolhendo melhor fora de casa · Orientações para o desafio.</DialogDescription>
                            </DialogHeader>
                            <svg :viewBox="panels[index]" class="h-auto w-full rounded-xl" role="img" :aria-label="`Guia ilustrado: ${situation.title}`" overflow="hidden">
                                <image href="/images/alimentacao/escolhendo-melhor-fora-de-casa.png" width="1086" height="1448" />
                            </svg>
                            <ul class="list-disc space-y-2 pl-5 text-sm leading-relaxed">
                                <li v-for="tip in situation.tips" :key="tip">{{ tip }}</li>
                            </ul>
                        </DialogContent>
                    </Dialog>
                </article>
            </section>
        </main>
    </AppLayout>
</template>
