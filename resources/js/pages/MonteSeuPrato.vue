<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

const materials = [
    {
        title: 'Monte seu prato',
        image: '/images/alimentacao/monteseuprato.jpeg',
        alt: 'Referência para montar o prato com 50% de vegetais, 25% de proteína magra e 25% de carboidrato.',
    },
    {
        title: '5 ideias de pratos',
        image: '/images/alimentacao/5ideias.jpeg',
        alt: 'Cinco ideias de pratos com frango, peixe, carne moída, sobrecoxa e lombo suíno, acompanhados de vegetais e carboidratos.',
    },
    {
        title: 'Mais 5 ideias de pratos',
        image: '/images/alimentacao/5ideiaspratos.jpeg',
        alt: 'Mais cinco ideias de pratos com frango desfiado, omelete, peixe, lombo suíno e sobrecoxa, acompanhados de vegetais e carboidratos.',
    },
];

const tips = [
    {
        label: 'IMPORTANTE',
        title: 'Pequenos detalhes que fazem diferença na sua evolução!',
        description: 'Cuidados com a gordura adicionada e os molhos no preparo das refeições.',
        image: null,
        imageAlt: '',
        intro: 'Você não precisa seguir um cardápio com tudo pesado na balança para fazer boas escolhas. Mas atenção: alguns detalhes do preparo podem aumentar bastante o valor calórico da sua refeição e passar despercebidos.',
        sections: [
            {
                title: '1. Cuidado com a gordura adicionada',
                text: 'A quantidade de óleo, azeite e outras gorduras utilizadas no preparo faz diferença!',
                items: [
                    'Use o mínimo possível de óleo apenas para untar a panela ou frigideira, dourar alho e cebola ou evitar que os alimentos grudem.',
                    'Em preparações que não precisam de gordura, como carnes cozidas e alimentos preparados na Air Fryer, evite adicionar óleo.',
                    'Se desejar, utilize uma pequena quantidade de azeite para finalizar a preparação, depois de pronta. Lembre-se de que o azeite também acrescenta calorias.',
                ],
                closing: '',
            },
            {
                title: '2. Atenção aos molhos!',
                text: 'Maionese, molhos prontos para salada, molhos cremosos e outros acompanhamentos podem adicionar bastante gordura à refeição.',
                items: [],
                closing: 'Prefira temperos simples, como limão, vinagre, ervas e especiarias.',
            },
        ],
    },
    {
        label: 'Como escolher no self-service',
        title: 'Vai almoçar em um self-service?',
        description: 'O que colocar no prato e como equilibrar.',
        image: '/images/alimentacao/self-service.png',
        imageAlt: 'Prato em restaurante self-service com salada, vegetais, frango grelhado, arroz e feijão.',
        intro: 'Use a mesma referência de montagem do prato:',
        sections: [
            {
                title: '',
                text: '',
                items: ['50% de vegetais;', '25% de proteína;', '25% de carboidratos.'],
                closing: 'Dê preferência a saladas cruas, vegetais cozidos, carnes magras e preparações grelhadas, assadas ou cozidas.',
            },
            {
                title: '',
                text: 'Evite preparações prontas, como estrogonofe, parmegiana e farofas.',
                items: [],
                closing: '',
            },
        ],
    },
    {
        label: 'Atenção às escolhas de proteínas',
        title: 'Atenção às opções mais gordurosas do buffet!',
        description: '',
        image: '/images/alimentacao/proteinas-fora-de-casa.png',
        imageAlt: 'Bandejas de buffet com costela, linguiça, bacon, panceta, torresmo e queijo coalho.',
        intro: 'Algumas preparações podem concentrar bastante gordura. Procure não fazer delas sua escolha habitual:',
        sections: [
            {
                title: '',
                text: '',
                items: ['Cupim;', 'Costela;', 'Linguiça;', 'Bacon;', 'Panceta e torresmo;', 'Carnes com muita gordura aparente;', 'Queijo coalho;', 'Frituras em geral.'],
                closing: 'Não é necessário deixar de frequentar restaurantes ou abrir mão da vida social. A ideia é reconhecer essas escolhas e entender como elas podem influenciar sua alimentação ao longo da semana.',
            },
        ],
    },
];
</script>

<template>
    <Head title="Monte seu prato" />

    <AppLayout :breadcrumbs="[{ title: 'Alimentação', href: '#' }, { title: 'Monte seu prato', href: '/alimentacao/monte-seu-prato' }]">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-2">
                <div class="eyebrow">Alimentação</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] text-brand-gold">Monte seu prato</h1>
                <p class="text-sm text-brand-muted">Use as referências e ideias abaixo para organizar suas refeições.</p>
            </header>

            <div class="flex flex-wrap gap-3">
                <Dialog v-for="(tip, index) in tips" :key="tip.label">
                    <DialogTrigger as-child>
                        <Button
                            class="h-auto min-h-11 max-w-full whitespace-normal px-5 py-3 text-left"
                            :class="index === 0 ? 'bg-brand-gold text-brand-ink hover:bg-brand-gold/90' : 'border border-brand-line2 bg-brand-card text-brand-cream hover:bg-brand-card2'"
                        >
                            {{ tip.label }}
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="max-h-[90dvh] max-w-3xl overflow-y-auto border-brand-line2 bg-brand-card text-brand-cream">
                        <DialogHeader>
                            <DialogTitle class="pr-6 text-xl leading-snug text-brand-gold">{{ tip.title }}</DialogTitle>
                            <DialogDescription :class="tip.description ? 'text-brand-muted' : 'sr-only'">{{ tip.description || tip.imageAlt }}</DialogDescription>
                        </DialogHeader>

                        <img v-if="tip.image" :src="tip.image" :alt="tip.imageAlt" width="1536" height="1024" class="h-auto w-full rounded-xl" />

                        <div class="space-y-5 text-sm leading-relaxed">
                            <p>{{ tip.intro }}</p>
                            <section v-for="(section, sectionIndex) in tip.sections" :key="sectionIndex" class="space-y-3">
                                <h2 v-if="section.title" class="text-base font-bold">{{ section.title }}</h2>
                                <p v-if="section.text">{{ section.text }}</p>
                                <ul v-if="section.items.length" class="list-disc space-y-2 pl-5">
                                    <li v-for="item in section.items" :key="item">{{ item }}</li>
                                </ul>
                                <p v-if="section.closing">{{ section.closing }}</p>
                            </section>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>

            <section class="grid items-start gap-5 md:grid-cols-3" aria-label="Materiais para montar seu prato">
                <article v-for="material in materials" :key="material.image" class="surface-card min-w-0 space-y-3 p-3">
                    <h2 class="text-lg font-bold">{{ material.title }}</h2>
                    <a :href="material.image" target="_blank" rel="noopener noreferrer" :aria-label="'Ampliar ' + material.title">
                        <img :src="material.image" :alt="material.alt" loading="lazy" class="h-auto w-full rounded-xl" />
                    </a>
                </article>
            </section>
        </main>
    </AppLayout>
</template>
