<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CircleCheck, Maximize2 } from 'lucide-vue-next';

const materials = [
    {
        title: 'Monte seu prato',
        image: '/images/alimentacao/pratoComPercentual.png',
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

const plateGroups = [
    {
        title: '5 ideias de pratos', image: materials[1].image, width: 520, height: 1024,
        meals: [
            { title: 'Frango grelhado', sides: ['arroz e feijão', 'alface, tomate e cenoura ralada'], crop: '22 104 207 179' },
            { title: 'Peixe grelhado', sides: ['purê de batata', 'brócolis e cenoura no vapor'], crop: '22 282 207 172' },
            { title: 'Carne moída magra', sides: ['batata assada', 'abobrinha grelhada', 'alface'], crop: '22 453 207 171' },
            { title: 'Sobrecoxa assada (sem pele)', sides: ['batata-doce', 'repolho roxo', 'cenoura ralada'], crop: '22 623 207 170' },
            { title: 'Lombo suíno grelhado', sides: ['inhame cozido', 'rúcula e tomate-cereja'], crop: '22 793 207 174' },
        ],
    },
    {
        title: 'Mais 5 ideias de pratos', image: materials[2].image, width: 508, height: 1024,
        meals: [
            { title: 'Frango desfiado', sides: ['arroz integral', 'seleta de legumes (congelada) refogada'], crop: '22 104 207 181' },
            { title: 'Omelete (2 ovos)', sides: ['batata-doce', 'beterraba ralada', 'alface'], crop: '22 285 207 173' },
            { title: 'Peixe grelhado', sides: ['macarrão (integral ou tradicional)', 'couve refogada', 'tomate'], crop: '22 458 207 169' },
            { title: 'Lombo suíno', sides: ['mandioca cozida', 'vagem e cenoura no vapor'], crop: '22 627 207 167' },
            { title: 'Sobrecoxa grelhada (sem pele)', sides: ['arroz integral', 'abóbora assada', 'abobrinha grelhada'], crop: '22 794 207 176' },
        ],
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
    <Head title="Almoço - Monte seu prato" />

    <AppLayout :breadcrumbs="[{ title: 'Alimentação', href: '#' }, { title: 'Almoço - Monte seu prato', href: '/alimentacao/monte-seu-prato' }]">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-2">
                <div class="eyebrow">Alimentação</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] leading-tight text-brand-gold">Almoço - Monte seu prato</h1>
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

            <section class="space-y-6 rounded-3xl border border-yellow-400/60 bg-[#191919] p-5 text-white sm:p-8" aria-labelledby="plate-heading">
                <div class="grid items-center gap-6 lg:grid-cols-2">
                    <div class="space-y-5">
                        <div class="h-1.5 w-16 rounded-full bg-yellow-400" />
                        <h2 id="plate-heading" class="font-display text-4xl font-black uppercase leading-tight sm:text-5xl">Monte seu prato<br />Dividindo o prato</h2>
                        <p class="max-w-md text-lg leading-relaxed">Use esta referência para montar refeições equilibradas, variadas e práticas no seu dia a dia.</p>
                        <ul class="space-y-3 text-lg font-bold uppercase">
                            <li><span class="mr-2 inline-block rounded-xl bg-yellow-400 px-3 py-1 text-2xl font-black text-black">50%</span> Vegetais</li>
                            <li><span class="mr-2 inline-block rounded-xl bg-yellow-400 px-3 py-1 text-2xl font-black text-black">25%</span> Proteína magra</li>
                            <li><span class="mr-2 inline-block rounded-xl bg-yellow-400 px-3 py-1 text-2xl font-black text-black">25%</span> Carboidrato</li>
                        </ul>
                        <p class="text-base leading-relaxed text-white/85">Carboidrato: arroz e feijão, batata, batata-doce, inhame, macarrão, etc.</p>
                    </div>
                    <img :src="materials[0].image" :alt="materials[0].alt" class="mx-auto h-auto w-full max-w-md rounded-xl" />
                </div>
                <aside class="rounded-2xl border border-yellow-400 p-5 text-center">
                    <h3 class="mb-2 flex items-center justify-center gap-2 text-xl font-black uppercase text-yellow-400"><CircleCheck class="size-7 shrink-0" /> Dica da nutri</h3>
                    <p class="mx-auto max-w-2xl text-lg leading-relaxed">Escolha as combinações que você gosta das ideias abaixo e depois crie uma lista de compras baseado no que você vai usar essa semana.</p>
                </aside>
            </section>

            <section class="space-y-5 rounded-3xl border border-yellow-400/60 bg-[#191919] p-4 text-white sm:p-5" aria-labelledby="meal-ideas-heading">
                <h2 id="meal-ideas-heading" class="rounded-full bg-yellow-400 px-4 py-3 text-center font-display text-2xl font-black uppercase leading-tight text-black">10 ideias de pratos</h2>
                <div class="grid items-start gap-5 lg:grid-cols-2">
                    <ol v-for="(group, groupIndex) in plateGroups" :key="group.title" class="min-w-0 space-y-4" :start="groupIndex * 5 + 1">
                        <li v-for="(meal, mealIndex) in group.meals" :key="meal.title" class="flex flex-col items-center gap-3 rounded-3xl border border-white/10 bg-gradient-to-r from-[#131313] to-[#252525] p-3 sm:flex-row">
                            <svg :viewBox="meal.crop" :width="meal.crop.split(' ')[2]" :height="meal.crop.split(' ')[3]" class="h-auto w-48 max-w-full shrink-0 sm:w-40 xl:w-48" role="img" :aria-label="meal.title" overflow="hidden"><image :href="group.image" :width="group.width" :height="group.height" /></svg>
                            <div class="flex w-full items-start gap-3 py-2">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-yellow-400 text-2xl font-black text-black" aria-hidden="true">{{ groupIndex * 5 + mealIndex + 1 }}</span>
                                <div class="min-w-0 space-y-1 text-base leading-relaxed">
                                    <h3 class="font-bold">{{ meal.title }}</h3>
                                    <p v-for="side in meal.sides" :key="side">+ {{ side }}</p>
                                </div>
                            </div>
                        </li>
                    </ol>
                </div>
            </section>

            <div class="flex flex-wrap gap-3" aria-label="Ampliar materiais originais">
                <Dialog v-for="material in materials" :key="material.image">
                    <DialogTrigger as-child><Button variant="outline" class="h-auto gap-2 whitespace-normal border-brand-gold/60 py-3 text-brand-gold"><Maximize2 class="size-4 shrink-0" /> Ver imagem: {{ material.title }}</Button></DialogTrigger>
                    <DialogContent class="max-h-[90dvh] max-w-2xl overflow-y-auto border-brand-line2 bg-[#191919] text-white">
                        <DialogHeader><DialogTitle>{{ material.title }}</DialogTitle><DialogDescription class="sr-only">{{ material.alt }}</DialogDescription></DialogHeader>
                        <img :src="material.image" :alt="material.alt" loading="lazy" class="mx-auto h-auto w-full max-w-lg rounded-xl" />
                    </DialogContent>
                </Dialog>
            </div>
        </main>
    </AppLayout>
</template>
