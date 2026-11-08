<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import NutritionMaterialImage from '@/components/NutritionMaterialImage.vue';
import NumberedRecipeImage from '@/components/NumberedRecipeImage.vue';
import IllustratedRecipeCards from '@/components/IllustratedRecipeCards.vue';
import NutriRecommendations from '@/components/NutriRecommendations.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Head } from '@inertiajs/vue3';
import { BadgeCheck, ChevronDown, Heart, Info, Lightbulb, LockKeyhole, Maximize2, Salad, Search, ShoppingCart, Snowflake, Wheat, Zap } from 'lucide-vue-next';
import { ref } from 'vue';

const openMaterial = ref<string | null>(null);
defineProps<{ recipeUnlock: { requiredDays: number; completedDays: number; unlocked: boolean } }>();
const openRecipes = ref<string | null>(null);

const yogurtMeals = [
    { title: 'Overnight oats', ingredients: 'Iogurte + aveia + fruta + castanhas.', viewBox: '20 106 142 146' },
    { title: 'Creme de iogurte', ingredients: 'Iogurte batido + fruta congelada (ex.: morango).', viewBox: '169 106 140 146' },
    { title: 'Iogurte com fruta e castanhas', ingredients: 'Iogurte + fruta picada (ex.: banana) + castanhas ou chia.', viewBox: '315 106 139 146' },
    { title: 'Iogurte com leite em pó', ingredients: 'Iogurte + leite em pó + fruta (ex.: manga ou mamão).', viewBox: '459 106 136 146' },
    { title: 'Iogurte com fruta e canela', ingredients: 'Iogurte + fruta picada (ex.: maçã) + canela + castanhas.', viewBox: '602 106 139 146' },
];

const eggMeals = [
    { title: 'Patê de ovos com biscoito de arroz', ingredients: 'Ovos cozidos + creme de ricota ou iogurte + temperos + biscoito de arroz.', viewBox: '14 97 146 133' },
    { title: 'Panqueca de banana', ingredients: 'Banana + ovo + aveia + canela (prepare na frigideira antiaderente).', viewBox: '166 97 144 133' },
    { title: 'Omelete nutritivo', ingredients: 'Ovos + queijo + vegetais (ex.: espinafre, tomate, cebola) + pão integral (ou outra fonte de carboidrato).', viewBox: '316 97 142 133' },
    { title: 'Ovos cozidos com salada', ingredients: 'Ovos cozidos + mix de folhas + tomate-cereja + azeite (em pequena quantidade).', viewBox: '463 97 139 133' },
    { title: 'Pão com ovo e folhas', ingredients: 'Pão integral + ovo mexido + folhas (ex.: rúcula ou alface).', viewBox: '608 97 136 133' },
];

const wheyMeals = [
    { title: 'Vitamina proteica', ingredients: 'Whey + fruta (ex.: morango) + leite ou bebida vegetal (200 ml).', viewBox: '18 96 141 135' },
    { title: 'Shake de banana e cacau', ingredients: 'Whey + banana + cacau 100% + leite ou bebida vegetal.', viewBox: '165 96 146 135' },
    { title: 'Vitamina tropical', ingredients: 'Whey + manga (ou outra fruta) + leite ou bebida vegetal.', viewBox: '317 96 141 135' },
    { title: 'Iogurte proteico', ingredients: 'Iogurte + whey + fruta picada + castanhas.', viewBox: '464 96 141 135' },
    { title: 'Creme de aveia proteico', ingredients: 'Aveia + whey + banana + canela (prepare no fogão ou micro-ondas).', viewBox: '609 96 141 135' },
];

const cheeseGuideImage = '/images/alimentacao/quijos%20e%20substitui%C3%A7%C3%A3o%20ovos%20.jpeg';
const cheeseTypes = [
    { name: 'Cottage', text: 'Mais magro (menor teor de gordura).', viewBox: '17 574 101 104' },
    { name: 'Ricota', text: 'Mais magra (varia conforme a marca e o tipo).', viewBox: '124 574 102 104' },
    { name: 'Minas frescal', text: 'Teor de gordura moderado.', viewBox: '231 574 101 104' },
    { name: 'Muçarela (light)', text: 'Teor de gordura moderado.', viewBox: '338 574 101 104' },
    { name: 'Minas padrão', text: 'Mais gordo (usar em menor quantidade).', viewBox: '445 574 101 104' },
    { name: 'Canastra', text: 'Teor de gordura mais elevado (usar em menor quantidade).', viewBox: '552 574 101 104' },
    { name: 'Parmesão', text: 'Mais concentrado em gordura e sabor. Use em pequenas quantidades.', viewBox: '659 574 95 104' },
];
const cheeseGroups = [
    { title: 'Queijos mais magros', items: ['Menor teor de gordura e calorias.', 'Podem ser usados em maior volume, ajudando na saciedade.', 'Boas opções para o dia a dia e para quem tem mais apetite.'] },
    { title: 'Queijos mais gordos', items: ['Têm mais gordura e, por isso, mais calorias.', 'Podem ser consumidos, mas em quantidade moderada.', 'Geralmente, a recomendação é de 30 a 40 g por refeição (de acordo com a sua prescrição).'] },
];

const cheeseMeals = [
    { title: 'Pão com cottage e geleia de fruta', ingredients: 'Pão integral + cottage + geleia sem açúcar (1 colher de chá).', viewBox: '14 106 142 145', image: '' },
    { title: 'Cuscuz com queijo coalho', ingredients: 'Cuscuz de milho cozido no vapor + queijo coalho grelhado.', viewBox: '', image: '/images/alimentacao/cuscuz-queijo-coalho-v1.png' },
    { title: 'Crepioca prática', ingredients: 'Ovo + tapioca + queijo + tomate (e outros temperos).', viewBox: '313 106 139 145', image: '' },
    { title: 'Ovos mexidos com queijo', ingredients: 'Ovos + queijo (minas ou muçarela) + pão integral + fruta.', viewBox: '459 106 137 145', image: '' },
    { title: 'Pão com queijo e tomate', ingredients: 'Pão integral + queijo (minas, muçarela ou branco) + tomate + orégano.', viewBox: '604 106 141 145', image: '' },
];

const oatMeals = [
    { title: 'Mingau de aveia', text: 'Aveia + leite ou bebida vegetal + canela. Cremoso, prático e sustentador.', viewBox: '30 305 224 277' },
    { title: 'Vitamina energética', text: 'Aveia + banana + whey protein + leite ou bebida vegetal. Ótima opção no pré ou pós-treino.', viewBox: '265 305 212 277' },
    { title: 'Overnight oats', text: 'Aveia + iogurte natural (ou leite) + fruta picada. Deixe na geladeira na noite anterior.', viewBox: '489 305 212 277' },
    { title: 'Panqueca de aveia', text: 'Aveia + ovo + banana + canela. Uma opção prática e nutritiva.', viewBox: '714 305 211 277' },
    { title: 'Aveia com iogurte e castanhas', text: 'Aveia + iogurte natural + castanhas + fruta. Combinação com proteína, fibras e gordura boa.', viewBox: '936 305 208 277' },
];

const breadSteps = [
    { title: 'Leia a lista de ingredientes.', text: 'Vá além da capa, que muitas vezes é atrativa, mas não reflete a qualidade do produto.' },
    { title: 'Entenda a ordem dos ingredientes.', text: 'Eles aparecem em ordem decrescente de quantidade, ou seja, o primeiro é o que está em maior quantidade.' },
    { title: 'Prefira farinha integral.', text: 'Opte por pães em que o primeiro ingrediente seja “farinha de trigo integral”.' },
    { title: 'Fique atento à farinha branca.', text: 'Se aparecer “farinha de trigo enriquecida com ferro e ácido fólico”, trata-se de farinha branca. Quanto mais longe ela estiver no rótulo, melhor.' },
    { title: 'Observe o açúcar.', text: 'O ideal é que o açúcar (ou outros nomes, como sacarose, xarope de glicose, maltodextrina) esteja em menor quantidade, mais ao final da lista.' },
];
const breadIngredientTips = [
    'Ótimo sinal: o primeiro ingrediente é farinha integral.',
    'A farinha branca (enriquecida com ferro e ácido fólico) aparece depois.',
    'O açúcar está mais ao final, indicando menor quantidade.',
];
const breadImportant = [
    'Se o seu objetivo é emagrecimento, dê preferência a outras fontes de carboidrato mais nutritivas, como raízes (batata-doce, mandioca), cereais integrais (ex.: aveia) e frutas.',
    'O pão pode entrar na sua rotina e na sua prescrição nutricional, desde que consumido com moderação, de forma equilibrada e em combinação com outros alimentos que aumentem a saciedade.',
    'A ideia não é proibir, mas fazer escolhas mais inteligentes, observando a qualidade do produto, a composição e o contexto da sua alimentação e objetivos.',
];

const goodFats = [
    { title: 'Castanhas e oleaginosas', description: 'Fontes de gorduras insaturadas, fibras e minerais (como magnésio, selênio e zinco).', usage: 'Nos lanches, sobre iogurtes, frutas ou saladas.', amount: '15 a 20 g por dia (um pequeno punhado).', note: 'Se tiver dificuldade em controlar a porção, deixe essa opção para outro momento.', viewBox: '30 204 330 184' },
    { title: 'Mix de sementes', description: 'Chia, linhaça, gergelim e semente de abóbora. Fornecem gorduras boas, fibras e antioxidantes.', usage: 'Polvilhe sobre iogurtes, frutas, cremes, saladas e preparações em geral.', amount: '1 colher de sopa ao dia (pode variar entre as opções).', note: '', viewBox: '30 405 335 172' },
    { title: 'Abacate', description: 'Rico em gorduras predominantemente insaturadas, além de fibras, vitaminas e minerais.', usage: 'Amassado no pão, em pastas, saladas, crepiocas ou combinado com frutas (ex.: abacate com banana).', amount: 'Cerca de 1/4 a 1/2 unidade por dia (de acordo com a sua rotina e objetivo).', note: '', viewBox: '30 596 340 179' },
    { title: 'Coco', description: 'Também é uma fonte de gordura, com maior proporção de gordura saturada. Pode ser incluído na dieta com moderação.', usage: 'Coco ralado ou em lascas sobre iogurtes, frutas, cremes e vitaminas.', amount: '1 colher de sopa ao dia.', note: '', viewBox: '30 791 335 156' },
    { title: 'Cacau em pó', description: 'Rico em compostos antioxidantes (como os flavonoides), que contribuem para a saúde do coração e o bem-estar.', usage: 'Em iogurtes, vitaminas, panquecas, mingaus e overnight oats.', amount: '', choice: 'Já gosta do sabor amargo? Use o cacau 100%. Se ainda não está acostumado, comece pelo cacau 70%.', note: '', viewBox: '30 963 335 177' },
];

const eggAlternatives = [
    { title: 'Frango', detail: 'Desfiado, grelhado ou em patê', viewBox: '992 164 58 53' },
    { title: 'Peixe', detail: 'Atum, sardinha ou outros', viewBox: '1104 164 62 53' },
    { title: 'Lombo suíno', detail: 'Desfiado', viewBox: '1224 164 62 53' },
    { title: 'Queijos', detail: 'Ex.: cottage', viewBox: '1333 164 65 53' },
    { title: 'Whey protein', detail: 'Suplemento', viewBox: '1442 164 52 53' },
];
const eggFreeMeals = [
    { title: 'Sanduíche de atum', ingredients: 'Atum em lata (em água) + requeijão light (ou iogurte natural) + temperos + pão integral.', viewBox: '806 351 234 157' },
    { title: 'Patê de frango', ingredients: 'Frango desfiado + requeijão light (ou cottage) + temperos (cebolinha, limão, ervas) + biscoito de arroz ou pão integral.', viewBox: '1047 351 230 157' },
    { title: 'Lombo suíno desfiado', ingredients: 'Lombo cozido e desfiado + mostarda (opcional) + tomate e folhas + pão integral.', viewBox: '1282 351 236 157' },
    { title: 'Cuscuz com frango desfiado', ingredients: 'Cuscuz de milho + frango desfiado temperado + azeite (opcional) + salada.', viewBox: '806 619 234 154' },
    { title: 'Cottage turbinado', ingredients: 'Queijo cottage + tomate-cereja + orégano + azeite (opcional) + pão integral, torrada ou biscoito de arroz.', viewBox: '1047 619 230 154' },
    { title: 'Vitamina proteica', ingredients: 'Whey protein + banana + leite ou bebida vegetal + cacau em pó (opcional).', viewBox: '1282 619 236 154' },
];

const snacks = [
    { name: 'PopCorners', description: 'Snack de pipoca', viewBox: '55 512 195 278' },
    { name: 'Batata doce em palito', description: 'Palitos de batata-doce', viewBox: '264 516 198 274' },
    { name: 'Chips de cebola e salsa', description: 'bio2 Vegan Chips', viewBox: '481 514 194 276' },
    { name: 'Veggie Chips', description: 'Fit Food · tomate e manjericão', viewBox: '688 515 197 275' },
    { name: 'Roots', description: 'Chips de raízes', viewBox: '54 937 194 271' },
    { name: 'Belive', description: 'Snack assado de cebola e salsa', viewBox: '262 940 200 268' },
    { name: 'Natural Life', description: 'Biscoitos de arroz · cebola e salsa', viewBox: '478 937 201 271' },
    { name: 'Zaytas', description: 'Lascas de batata-doce', viewBox: '696 936 194 272' },
];

type YogurtProduct = { name: string; detail: string; viewBox: string; image?: string; imageWidth?: number; imageHeight?: number; presentation?: 'pot' | 'bottle' };
const yogurtOptions: { id: string; title: string; subtitle: string; viewBox: string; height: number; products: YogurtProduct[] }[] = [
    {
        id: 'natural', title: 'Opções de iogurte natural', subtitle: 'Versões naturais / integrais', viewBox: '0 0 1199 674', height: 674,
        products: [
            { name: 'Batavo', detail: 'Iogurte natural integral · 170 g', viewBox: '65 166 210 258' },
            { name: 'Nestlé', detail: 'Natural integral · 160 g', viewBox: '297 166 200 258' },
            { name: 'Vigor Viv', detail: 'Natural · 170 g', viewBox: '506 166 204 258' },
            { name: 'Itambé Natural Milk', detail: 'Iogurte natural integral · 170 g', viewBox: '160 165 480 580', image: '/images/alimentacao/iogurte-itambe-natural-milk.png', imageWidth: 800, imageHeight: 800 },
            { name: 'Danone', detail: 'Natural integral · 170 g', viewBox: '932 166 211 258' },
            { name: 'Yorgus Grego', detail: 'Iogurte natural zero lactose', viewBox: '71 438 287 214' },
            { name: 'Yorgus Grego', detail: 'Iogurte desnatado', viewBox: '140 100 520 630', image: '/images/alimentacao/iogurte-yorgus-grego-desnatado.png', imageWidth: 800, imageHeight: 800 },
            { name: 'Leitíssimo', detail: 'Iogurte natural parcialmente desnatado · zero lactose · 500 g', viewBox: '160 80 530 650', image: '/images/alimentacao/iogurte-leitissimo-zero-lactose.png', imageWidth: 800, imageHeight: 800 },
            { name: 'Verde Campo Natural Whey', detail: 'Coco · 14 g de proteínas', viewBox: '0 110 610 506', image: '/images/alimentacao/iogurte-verde-campo-natural-whey-coco.png', imageWidth: 787, imageHeight: 616 },
        ],
    },
    {
        id: 'proteico', title: 'Opções de iogurte proteico', subtitle: 'Iogurtes com sabor (com proteína) · Praticidade no dia a dia', viewBox: '0 678 1199 586', height: 586,
        products: [
            { name: 'Vida Veg Veg Protein', detail: 'Cookies & Cream · 14 g de proteína · 250 g · 100% vegetal, feito com leite de coco', viewBox: '410 95 350 1030', image: '/images/alimentacao/vida-veg-protein-cookies-cream-14g.jpg', imageWidth: 1200, imageHeight: 1200, presentation: 'bottle' },
            { name: 'YoPRO', detail: 'Morango · 15 g de proteínas · 250 g', viewBox: '133 845 157 389' },
            { name: 'Verde Campo Natural Whey', detail: 'Morango · 14 g de proteínas · 250 g', viewBox: '360 845 153 389' },
            { name: 'Yorgus Whey', detail: 'Frutas vermelhas · 16 g de proteínas · 250 g', viewBox: '576 845 164 389' },
            { name: 'Yorgus Grego', detail: 'Morango · zero lactose · 12 g de proteínas', viewBox: '15 80 440 325', image: '/images/alimentacao/iogurte-yorgus-grego-morango-12g.png', imageWidth: 468, imageHeight: 468, presentation: 'pot' },
            { name: 'Verde Campo Natural Whey', detail: 'Morango · 21 g de proteínas · 250 g', viewBox: '430 280 310 850', image: '/images/alimentacao/iogurte-verde-campo-morango-21g.png', imageWidth: 1170, imageHeight: 1184, presentation: 'bottle' },
            { name: 'Verde Campo Natural Whey', detail: 'Cookies & cream · 30 g de proteínas · 500 g', viewBox: '220 120 470 1460', image: '/images/alimentacao/iogurte-verde-campo-cookies-cream-30g.png', imageWidth: 1016, imageHeight: 1600, presentation: 'bottle' },
            { name: 'Verde Campo Natural Whey', detail: 'Torta de limão · 21 g de proteínas · 250 g', viewBox: '55 148 350 913', image: '/images/alimentacao/iogurte-verde-campo-torta-limao-21g.png', imageWidth: 761, imageHeight: 1061, presentation: 'bottle' },
            { name: 'Verde Campo Natural Whey', detail: 'Maracujá · versão em pote', viewBox: '0 0 299 320', image: '/images/alimentacao/iogurte-verde-campo-maracuja-pote.png', imageWidth: 299, imageHeight: 320, presentation: 'pot' },
            { name: 'Yorgus Ultra', detail: 'Frutas vermelhas · 18 g de proteína · 300 g', viewBox: '10 100 210 486', image: '/images/alimentacao/iogurte-yorgus-ultra-frutas-vermelhas-18g.png', imageWidth: 312, imageHeight: 586, presentation: 'bottle' },
            { name: 'Verde Campo Natural Whey', detail: 'Sobremesa láctea cremosa · doce de leite · 12 g de proteínas · 130 g', viewBox: '145 318 735 545', image: '/images/alimentacao/sobremesa-verde-campo-doce-leite-12g.png', imageWidth: 1024, imageHeight: 1024, presentation: 'pot' },
        ],
    },
];

type Material = { id: string; title: string; image: string; cover: string; panel?: 'left' | 'right'; guidance?: string[]; tip: string; combinations: { title: string; ingredients: string }[] };

const materials: Material[] = [
    {
        id: 'iogurte',
        cover: '/images/alimentacao/capa-iogurte-v1.png',
        title: 'Comprei iogurte. Como usar?',
        image: '/images/alimentacao/COMBINANDOaLIMENTOS%20(2).jpeg',
        tip: 'O iogurte é uma ótima fonte de proteína, que ajuda na saciedade. Combine com frutas (carboidrato) e castanhas, chia ou aveia (gordura boa e fibras).',
        combinations: yogurtMeals.map(({ title, ingredients }) => ({ title, ingredients })),
    },
    {
        id: 'queijos',
        cover: '/images/alimentacao/capa-queijos-v1.png',
        title: 'Comprei queijos. Como usar?',
        image: '/images/alimentacao/COMBINANDOaLIMENTOS%20(3).jpeg',
        tip: 'Os queijos adicionam proteína e sabor às refeições. Prefira versões mais magras, como minas, cottage, ricota ou muçarela light.',
        combinations: [
            { title: 'Pão com cottage e geleia de fruta', ingredients: 'Pão integral + cottage + geleia sem açúcar (1 colher de chá).' },
            { title: 'Cuscuz com queijo coalho', ingredients: 'Cuscuz de milho cozido no vapor + queijo coalho grelhado.' },
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
        combinations: eggMeals.map(({ title, ingredients }) => ({ title, ingredients })),
    },
    {
        id: 'whey',
        cover: '/images/alimentacao/capa-whey-v1.png',
        title: 'Comprei whey protein. Como usar?',
        image: '/images/alimentacao/COMBINANDOaLIMENTOS%20(1).jpeg',
        tip: 'O whey é uma forma prática de aumentar a ingestão de proteína e pode ser usado em vitaminas, cremes e preparações do dia a dia.',
        combinations: wheyMeals.map(({ title, ingredients }) => ({ title, ingredients })),
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
    {
        id: 'nutri-indica',
        cover: '/images/alimentacao/nutri_indica/graoBico.jpeg',
        title: 'A nutri indica!',
        image: '',
        tip: '',
        combinations: [],
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

                        <div v-if="material.id === 'iogurte'" class="grid gap-3 sm:grid-cols-2">
                            <Dialog v-for="option in yogurtOptions" :key="option.id">
                                <DialogTrigger as-child>
                                    <button type="button" class="flex w-full items-center justify-center gap-3 rounded-xl border border-brand-gold/40 bg-brand-gold/10 px-4 py-3 text-sm font-bold text-brand-gold hover:bg-brand-gold/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold focus-visible:ring-offset-2">
                                        {{ option.title }}
                                    </button>
                                </DialogTrigger>
                                <DialogContent class="max-h-[95dvh] w-[calc(100vw_-_1.5rem)] max-w-6xl overflow-y-auto rounded-2xl border-brand-line2 bg-[#151515] p-4 text-white sm:p-6">
                                    <DialogHeader>
                                        <DialogTitle class="pr-6 font-display text-3xl font-black uppercase leading-tight text-yellow-400">{{ option.title }}</DialogTitle>
                                        <DialogDescription class="text-base font-semibold text-white/85">{{ option.subtitle }}</DialogDescription>
                                    </DialogHeader>
                                    <ul class="grid gap-4 min-[480px]:grid-cols-2 lg:grid-cols-4">
                                        <li v-for="product in option.products" :key="product.name + product.detail" class="flex min-w-0 flex-col rounded-2xl border border-yellow-400/50 bg-black/20 p-4">
                                            <div class="flex min-h-60 flex-1 items-center justify-center">
                                                <svg :viewBox="product.viewBox" :width="product.viewBox.split(' ')[2]" :height="product.viewBox.split(' ')[3]" class="block h-auto w-full rounded-xl" :class="[option.id === 'proteico' && product.presentation !== 'pot' && !product.name.includes('Grego') ? 'max-w-28' : 'max-w-52', { 'bg-white': product.image }]" role="img" :aria-label="product.name + ' · ' + product.detail" overflow="hidden">
                                                    <image :href="product.image ?? '/images/alimentacao/opcoes-iogurtes.png'" :width="product.imageWidth ?? 1199" :height="product.imageHeight ?? 1312" />
                                                </svg>
                                            </div>
                                            <h2 class="mt-4 text-center font-display text-xl font-black uppercase leading-tight text-yellow-400">{{ product.name }}</h2>
                                            <p class="mt-2 text-center text-sm leading-relaxed text-white/85">{{ product.detail }}</p>
                                        </li>
                                    </ul>
                                    <aside v-if="option.id === 'natural'" class="flex flex-col gap-3 rounded-3xl border-2 border-yellow-400 p-4 sm:flex-row sm:items-center">
                                        <h2 class="flex shrink-0 items-center gap-2 font-display text-2xl font-black uppercase text-yellow-400"><BadgeCheck class="size-9 shrink-0" aria-hidden="true" />Boas opções:</h2>
                                        <p class="text-base leading-relaxed">Iogurtes naturais sem adição de açúcar, com versões integrais, desnatadas e zero lactose.</p>
                                    </aside>
                                    <details class="rounded-xl border border-white/20 p-3">
                                        <summary class="cursor-pointer text-sm font-bold text-yellow-400">Ver material original</summary>
                                        <svg :viewBox="option.viewBox" width="1199" :height="option.height" role="img" :aria-label="option.title" overflow="hidden" class="mt-4 block h-auto w-full rounded-xl"><image href="/images/alimentacao/opcoes-iogurtes.png" width="1199" height="1312" /></svg>
                                    </details>
                                </DialogContent>
                            </Dialog>
                        </div>

                        <Dialog v-if="material.id === 'queijos'">
                            <DialogTrigger as-child>
                                <button type="button" class="flex w-full items-center justify-center gap-3 rounded-xl border border-brand-gold/40 bg-brand-gold/10 px-4 py-3 text-sm font-bold text-brand-gold hover:bg-brand-gold/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold focus-visible:ring-offset-2">
                                    Como escolher o queijo?
                                </button>
                            </DialogTrigger>
                            <DialogContent class="max-h-[95dvh] w-[calc(100vw_-_1.5rem)] max-w-6xl overflow-y-auto rounded-2xl border-brand-line2 bg-[#151515] p-4 text-white sm:p-6">
                                <DialogHeader>
                                    <DialogTitle class="pr-6 font-display text-3xl font-black uppercase leading-tight">Queijos na rotina:<br /><span class="text-yellow-400">Como escolher e usar?</span></DialogTitle>
                                    <DialogDescription class="text-base leading-relaxed text-white/85">Os queijos são boas fontes de proteína, mas também de gordura. A escolha do tipo e da quantidade faz diferença para o seu objetivo.</DialogDescription>
                                </DialogHeader>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <section v-for="group in cheeseGroups" :key="group.title" class="rounded-3xl border border-[#d7bf8c] p-4">
                                        <h2 class="mb-4 rounded-full bg-yellow-400 px-4 py-2 text-center font-display text-xl font-black uppercase text-black">{{ group.title }}</h2>
                                        <ul class="space-y-4"><li v-for="item in group.items" :key="item" class="flex items-start gap-3 text-sm leading-relaxed"><BadgeCheck class="size-6 shrink-0 text-yellow-400" aria-hidden="true" />{{ item }}</li></ul>
                                    </section>
                                </div>
                                <aside class="flex flex-col gap-3 rounded-3xl bg-yellow-400 p-4 text-black sm:flex-row sm:items-center">
                                    <h2 class="flex shrink-0 items-center gap-2 font-display text-2xl font-black uppercase"><Info class="size-9 shrink-0" aria-hidden="true" />Importante:</h2>
                                    <p class="text-sm font-semibold leading-relaxed">A proteína do queijo, na maioria das vezes, vem acompanhada de gordura, o que aumenta o valor calórico da refeição. Exceto o cottage e alguns tipos de ricota, que são mais magros.</p>
                                </aside>
                                <section class="space-y-4 rounded-3xl border border-[#d7bf8c] p-4">
                                    <h2 class="rounded-2xl bg-yellow-400 px-4 py-3 text-center font-display text-xl font-black uppercase leading-tight text-black">Exemplos de queijos e teor de gordura <span class="block text-sm">(da menor para a maior)</span></h2>
                                    <ol class="grid gap-4 min-[480px]:grid-cols-2 sm:grid-cols-3 lg:grid-cols-7">
                                        <li v-for="cheese in cheeseTypes" :key="cheese.name" class="min-w-0 space-y-3 text-center">
                                            <svg :viewBox="cheese.viewBox" :width="cheese.viewBox.split(' ')[2]" :height="cheese.viewBox.split(' ')[3]" class="mx-auto block h-auto w-full max-w-48 rounded-xl" role="img" :aria-label="cheese.name" overflow="hidden"><image :href="cheeseGuideImage" width="1536" height="1024" /></svg>
                                            <h3 class="font-display text-lg font-black uppercase leading-tight">{{ cheese.name }}</h3>
                                            <p class="text-sm leading-relaxed text-white/85">{{ cheese.text }}</p>
                                        </li>
                                    </ol>
                                    <aside class="rounded-2xl border-2 border-yellow-400 p-3 text-sm leading-relaxed"><strong class="font-black uppercase text-yellow-400">Observação:</strong> O teor de gordura dos queijos pode variar de acordo com a forma de preparo, o tempo de maturação e o tipo de leite utilizado.</aside>
                                </section>
                                <aside class="flex flex-col gap-3 rounded-3xl border-2 border-yellow-400 p-4 sm:flex-row sm:items-center">
                                    <h2 class="shrink-0 font-display text-2xl font-black uppercase text-yellow-400">Dica final:</h2>
                                    <p class="text-sm leading-relaxed">Escolha o queijo de acordo com o seu objetivo, sua rotina e a composição da refeição, priorizando o equilíbrio entre proteína, gordura e calorias.</p>
                                </aside>
                                <details class="rounded-xl border border-white/20 p-3">
                                    <summary class="cursor-pointer text-sm font-bold text-yellow-400">Ver material original</summary>
                                    <NutritionMaterialImage :src="cheeseGuideImage" panel="left" alt="Queijos na rotina: como escolher e usar?" class="mx-auto mt-4 h-auto w-full max-w-2xl rounded-xl" />
                                </details>
                            </DialogContent>
                        </Dialog>

                        <section v-if="material.id === 'snacks'" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Opções de snacks saudáveis">
                            <article v-for="snack in snacks" :key="snack.name" class="surface-card min-w-0 space-y-3 p-4">
                                <Dialog>
                                    <DialogTrigger as-child>
                                        <button type="button" class="block w-full rounded-xl bg-black focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold" :aria-label="'Ampliar imagem de ' + snack.name">
                                            <svg :viewBox="snack.viewBox" :width="snack.viewBox.split(' ')[2]" :height="snack.viewBox.split(' ')[3]" class="mx-auto block h-auto w-full max-w-[200px] rounded-xl" role="img" :aria-label="snack.name" overflow="hidden">
                                                <image href="/images/alimentacao/snacks-saudaveis-v2.png" width="941" height="1672" />
                                            </svg>
                                        </button>
                                    </DialogTrigger>
                                    <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-md overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                        <DialogHeader>
                                            <DialogTitle class="pr-6 text-brand-gold">{{ snack.name }}</DialogTitle>
                                            <DialogDescription class="text-brand-muted">{{ snack.description }}</DialogDescription>
                                        </DialogHeader>
                                        <svg :viewBox="snack.viewBox" :width="snack.viewBox.split(' ')[2]" :height="snack.viewBox.split(' ')[3]" class="mx-auto block h-auto w-full max-w-[280px] rounded-xl" role="img" :aria-label="'Embalagem de ' + snack.name" overflow="hidden">
                                            <image href="/images/alimentacao/snacks-saudaveis-v2.png" width="941" height="1672" />
                                        </svg>
                                    </DialogContent>
                                </Dialog>
                                <h3 class="text-lg font-bold">{{ snack.name }}</h3>
                                <p class="text-sm text-brand-muted">{{ snack.description }}</p>
                                <p class="flex items-center gap-2 text-xs font-semibold text-brand-gold"><Maximize2 class="size-3.5" aria-hidden="true" />Toque na imagem para ampliar</p>
                            </article>
                        </section>

                        <section v-else-if="material.id === 'substituir-ovos'" class="space-y-6 rounded-2xl bg-[#101010] p-4 text-white sm:p-6" aria-label="Alternativas e combinações sem ovos">
                            <header class="space-y-3">
                                <h3 class="text-2xl font-black uppercase leading-tight sm:text-3xl">Não gosto de ovos.<br /><span class="text-yellow-400">O que posso colocar no lugar?</span></h3>
                                <p class="text-base leading-relaxed text-white/80">Sem problemas! Existem outras fontes de proteína que podem substituir os ovos nas suas refeições, com praticidade, sabor e boa saciedade.</p>
                            </header>

                            <section class="rounded-2xl border border-yellow-400 p-4">
                                <h4 class="mb-4 inline-block rounded-xl bg-yellow-400 px-4 py-2 font-black uppercase text-black">Boas alternativas de proteína:</h4>
                                <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                                    <li v-for="protein in eggAlternatives" :key="protein.title" class="flex flex-col items-center gap-2 text-center">
                                        <svg :viewBox="protein.viewBox" width="65" height="53" class="h-12 w-16" aria-hidden="true" overflow="hidden" preserveAspectRatio="xMidYMid slice">
                                            <image :href="material.image" width="1536" height="1024" />
                                        </svg>
                                        <span class="font-bold">{{ protein.title }}</span>
                                        <span class="text-sm text-white/75">{{ protein.detail }}</span>
                                    </li>
                                </ul>
                            </section>

                            <section aria-labelledby="egg-free-meals">
                                <h4 id="egg-free-meals" class="mb-4 rounded-xl bg-yellow-400 px-4 py-3 text-center text-lg font-black uppercase text-black">6 combinações práticas e saborosas (sem ovos)</h4>
                                <ol class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                    <li v-for="meal in eggFreeMeals" :key="meal.title" class="overflow-hidden rounded-2xl border border-yellow-400/30">
                                        <svg :viewBox="meal.viewBox" :width="meal.viewBox.split(' ')[2]" :height="meal.viewBox.split(' ')[3]" class="block h-auto w-full" role="img" :aria-label="meal.title" overflow="hidden">
                                            <image :href="material.image" width="1536" height="1024" />
                                        </svg>
                                        <div class="space-y-3 p-4">
                                            <h5 class="text-base font-black uppercase leading-snug">{{ meal.title }}</h5>
                                            <p class="text-sm leading-relaxed text-white/85">{{ meal.ingredients }}</p>
                                        </div>
                                    </li>
                                </ol>
                            </section>
                            <aside class="flex flex-col gap-3 rounded-3xl bg-yellow-400 px-5 py-4 text-black sm:flex-row sm:items-center sm:gap-4" aria-label="Lembre-se">
                                <div class="flex shrink-0 items-center gap-3">
                                    <Info class="size-11 shrink-0 stroke-[2.5]" aria-hidden="true" />
                                    <strong class="font-display text-3xl font-black uppercase leading-none">Lembre-se:</strong>
                                </div>
                                <p class="border-t border-black/60 pt-3 text-sm font-semibold leading-relaxed sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0">As combinações são sugestões de preparações práticas e podem variar de acordo com a sua prescrição nutricional, seus objetivos e rotina.</p>
                            </aside>
                            <Dialog>
                                <DialogTrigger as-child><button type="button" class="flex items-center gap-2 text-sm font-bold text-yellow-400 underline"><Maximize2 class="size-4" aria-hidden="true" />Ver material original</button></DialogTrigger>
                                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-3xl overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                    <DialogHeader><DialogTitle>Não gosto de ovos. O que posso colocar no lugar?</DialogTitle><DialogDescription>Material original com alternativas de proteína e seis combinações sem ovos.</DialogDescription></DialogHeader>
                                    <NutritionMaterialImage :src="material.image" panel="right" alt="Alternativas de proteína e seis combinações práticas sem ovos" class="h-auto w-full rounded-xl" />
                                </DialogContent>
                            </Dialog>
                        </section>

                        <section v-else-if="material.id === 'gorduras'" class="space-y-5 rounded-2xl bg-[#151515] p-4 text-white sm:p-6" aria-label="Gorduras boas: por que incluir no seu dia a dia?">
                            <header class="grid gap-5 lg:grid-cols-2">
                                <h3 class="font-display text-3xl font-black uppercase leading-tight sm:text-4xl">Gorduras boas:<br /><span class="text-yellow-400">Por que incluir<br />no seu dia a dia?</span></h3>
                                <div class="flex items-center gap-4 border-t border-white/40 pt-4 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                                    <p class="text-base leading-relaxed">As gorduras são importantes para a saúde, ajudam na saciedade, no equilíbrio hormonal e na absorção de vitaminas, como A, D, E e K.</p>
                                    <Heart class="hidden size-16 shrink-0 text-yellow-400 sm:block" aria-hidden="true" />
                                </div>
                            </header>
                            <div class="grid gap-3">
                                <article v-for="fat in goodFats" :key="fat.title" class="grid min-w-0 gap-4 overflow-hidden rounded-3xl border border-[#d7bf8c] p-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.35fr]">
                                    <svg :viewBox="fat.viewBox" :width="fat.viewBox.split(' ')[2]" :height="fat.viewBox.split(' ')[3]" class="my-auto block h-auto w-full rounded-xl" role="img" :aria-label="fat.title" overflow="hidden">
                                        <image :href="material.image" width="1199" height="1312" />
                                    </svg>
                                    <div class="space-y-3">
                                        <h4 class="inline-block rounded-2xl bg-yellow-400 px-4 py-2 font-display text-xl font-black uppercase leading-tight text-black">{{ fat.title }}</h4>
                                        <p class="text-sm leading-relaxed text-white/90">{{ fat.description }}</p>
                                    </div>
                                    <div class="space-y-4 border-t border-white/50 pt-4 sm:col-span-2 lg:col-span-1 lg:border-l lg:border-t-0 lg:pl-4 lg:pt-0">
                                        <div class="flex items-start gap-3">
                                            <Salad class="size-7 shrink-0 text-yellow-400" aria-hidden="true" />
                                            <p class="text-sm leading-relaxed"><strong class="font-black uppercase text-yellow-400">Como usar:</strong> {{ fat.usage }}</p>
                                        </div>
                                        <div class="flex items-start gap-3">
                                            <BadgeCheck class="size-7 shrink-0 text-yellow-400" aria-hidden="true" />
                                            <div class="space-y-1 text-sm leading-relaxed">
                                                <p v-if="fat.amount"><strong class="font-black uppercase text-yellow-400">Quantidade:</strong> {{ fat.amount }}</p>
                                                <p v-else><strong class="font-black uppercase text-yellow-400">Qual escolher:</strong> {{ fat.choice }}</p>
                                                <p v-if="fat.note" class="italic text-white/80">{{ fat.note }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </div>
                            <aside class="flex flex-col gap-3 rounded-3xl bg-yellow-400 px-5 py-4 text-black sm:flex-row sm:items-center sm:gap-4" aria-label="Dica importante">
                                <div class="flex shrink-0 items-center gap-3"><Info class="size-11 shrink-0 stroke-[2.5]" aria-hidden="true" /><strong class="font-display text-2xl font-black uppercase leading-tight">Dica<br />importante:</strong></div>
                                <p class="border-t border-black/60 pt-3 text-sm font-semibold leading-relaxed sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0">Para aproveitar os benefícios dessas gorduras, reduza o excesso de óleo no preparo das refeições (use apenas o mínimo necessário) e dê preferência a essas fontes de melhor qualidade ao longo do dia.</p>
                            </aside>
                            <Dialog>
                                <DialogTrigger as-child><button type="button" class="flex items-center gap-2 text-sm font-bold text-yellow-400 underline"><Maximize2 class="size-4" aria-hidden="true" />Ver material original</button></DialogTrigger>
                                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-4xl overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                    <DialogHeader><DialogTitle>Gorduras boas: por que incluir no seu dia a dia?</DialogTitle><DialogDescription>Material original com as cinco fontes de gordura, formas de uso e quantidades.</DialogDescription></DialogHeader>
                                    <img :src="material.image" alt="Gorduras boas: castanhas e oleaginosas, mix de sementes, abacate, coco e cacau em pó." class="h-auto w-full rounded-xl" />
                                </DialogContent>
                            </Dialog>
                        </section>

                        <section v-else-if="material.id === 'pao'" class="space-y-5 rounded-2xl bg-[#1b1915] p-4 text-white sm:p-6" aria-label="Como escolher o melhor pão integral">
                            <header class="grid items-center gap-4 lg:grid-cols-2">
                                <div class="space-y-4">
                                    <span class="inline-block rounded-full bg-yellow-400 px-4 py-2 text-sm font-black uppercase text-black">Material extra</span>
                                    <h3 class="font-display text-3xl font-black uppercase leading-tight sm:text-4xl">Como escolher<br /><span class="text-yellow-400">o melhor pão?</span></h3>
                                    <p class="border-l-2 border-yellow-400 pl-3 text-base leading-relaxed">O pão pode, sim, fazer parte da sua alimentação, mas a escolha do tipo, da composição e da quantidade faz diferença – especialmente se o seu objetivo for emagrecimento.</p>
                                </div>
                                <svg viewBox="610 0 589 300" width="589" height="300" class="block h-auto w-full rounded-2xl" role="img" aria-label="Pão integral com grãos e fatias" overflow="hidden"><image :href="material.image" width="1199" height="1312" /></svg>
                            </header>
                            <div class="grid items-start gap-4 lg:grid-cols-[1fr_2fr]">
                                <section class="overflow-hidden rounded-3xl border border-[#d7bf8c] p-4" aria-labelledby="bread-label-heading">
                                    <h4 id="bread-label-heading" class="mb-4 flex items-center gap-2 rounded-2xl bg-yellow-400 p-3 font-display text-xl font-black uppercase leading-tight text-black"><Search class="size-8 shrink-0" aria-hidden="true" />O que observar na embalagem?</h4>
                                    <ol class="divide-y divide-[#d7bf8c]/60">
                                        <li v-for="(step, stepIndex) in breadSteps" :key="step.title" class="flex gap-3 py-4 first:pt-0 last:pb-0">
                                            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-yellow-400 text-2xl font-black text-black">{{ stepIndex + 1 }}</span>
                                            <div class="space-y-2"><h5 class="font-display text-lg font-black uppercase leading-tight">{{ step.title }}</h5><p class="text-sm leading-relaxed text-white/85">{{ step.text }}</p></div>
                                        </li>
                                    </ol>
                                </section>
                                <div class="grid min-w-0 gap-4">
                                    <section class="space-y-4 rounded-3xl border border-[#d7bf8c] p-4">
                                        <h4 class="inline-block rounded-full bg-yellow-400 px-4 py-2 font-display text-xl font-black uppercase text-black">Exemplo de lista de ingredientes</h4>
                                        <div class="grid items-center gap-4 sm:grid-cols-[1.25fr_1fr]">
                                            <div class="min-w-0 space-y-3">
                                                <svg viewBox="390 346 445 198" width="445" height="198" class="block h-auto w-full rounded-xl" role="img" aria-label="Exemplo de rótulo com farinha de trigo integral como primeiro ingrediente" overflow="hidden"><image :href="material.image" width="1199" height="1312" /></svg>
                                                <details class="text-sm"><summary class="cursor-pointer font-bold text-yellow-400">Ler ingredientes em texto</summary><p class="mt-2 leading-relaxed">Ingredientes: farinha de trigo integral, água, farinha de trigo enriquecida com ferro e ácido fólico, sementes (linhaça, chia e gergelim), açúcar, óleo vegetal, fermento biológico, sal.</p></details>
                                            </div>
                                            <ul class="space-y-4"><li v-for="tip in breadIngredientTips" :key="tip" class="flex gap-2 text-sm leading-relaxed"><BadgeCheck class="size-6 shrink-0 text-yellow-400" aria-hidden="true" />{{ tip }}</li></ul>
                                        </div>
                                    </section>
                                    <section class="grid items-center gap-4 rounded-3xl border border-[#d7bf8c] p-4 sm:grid-cols-[1fr_2fr]">
                                        <svg viewBox="375 572 190 196" width="190" height="196" class="block h-auto w-full rounded-xl" role="img" aria-label="Embalagem de pão com indicação de 60% integral" overflow="hidden"><image :href="material.image" width="1199" height="1312" /></svg>
                                        <div class="space-y-3">
                                            <h4 class="inline-block rounded-full bg-yellow-400 px-4 py-2 font-display text-xl font-black uppercase leading-tight text-black">Fique de olho no % integral</h4>
                                            <p class="text-sm leading-relaxed">Muitos pães informam na embalagem a porcentagem de farinha integral utilizada.</p>
                                            <div class="flex flex-wrap gap-2"><span v-for="percentage in [50, 60, 70]" :key="percentage" class="rounded-2xl border-2 border-yellow-400 px-3 py-2 text-center"><strong class="block text-xl">{{ percentage }}%</strong><span class="text-xs">Integral</span></span></div>
                                            <p class="text-sm leading-relaxed">Dê preferência aos que têm maior porcentagem de farinha integral (60% ou 70%, por exemplo).</p>
                                        </div>
                                    </section>
                                    <section class="grid items-center gap-4 rounded-3xl border border-[#d7bf8c] p-4 sm:grid-cols-[1fr_2fr]">
                                        <svg viewBox="375 800 235 190" width="235" height="190" class="block h-auto w-full rounded-xl" role="img" aria-label="Cesta com pães franceses integrais" overflow="hidden"><image :href="material.image" width="1199" height="1312" /></svg>
                                        <div class="space-y-3">
                                            <h4 class="inline-block rounded-full bg-yellow-400 px-4 py-2 font-display text-xl font-black uppercase leading-tight text-black">Pão francês integral congelado</h4>
                                            <p class="text-sm leading-relaxed">É uma boa opção prática para o dia a dia, com maior teor de fibras do que o pão francês tradicional. Mantenha porções equilibradas e, de preferência, combinado com uma fonte de proteína (ex.: ovos, queijo, cottage, frango desfiado).</p>
                                            <span class="inline-flex items-center gap-2 rounded-lg border border-yellow-400 px-3 py-2 text-sm font-bold uppercase"><Snowflake class="size-5" aria-hidden="true" />Congelado</span>
                                            <ul class="space-y-2 text-sm"><li v-for="benefit in ['Prático', 'Mais fibras', 'Boa alternativa ao pão branco']" :key="benefit" class="flex items-center gap-2"><BadgeCheck class="size-5 text-yellow-400" aria-hidden="true" />{{ benefit }}</li></ul>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            <aside class="overflow-hidden rounded-3xl border border-[#d7bf8c] lg:flex" aria-label="Importante saber">
                                <h4 class="flex shrink-0 items-center gap-3 bg-yellow-400 p-5 font-display text-2xl font-black uppercase leading-tight text-black"><Info class="size-12 shrink-0" aria-hidden="true" />Importante<br />saber</h4>
                                <div class="grid gap-4 p-5 md:grid-cols-3"><p v-for="paragraph in breadImportant" :key="paragraph" class="border-t border-yellow-400/50 pt-3 text-sm leading-relaxed first:border-0 first:pt-0 md:border-l md:border-t-0 md:pl-4 md:pt-0">{{ paragraph }}</p></div>
                            </aside>
                            <Dialog>
                                <DialogTrigger as-child><button type="button" class="flex items-center gap-2 text-sm font-bold text-yellow-400 underline"><Maximize2 class="size-4" aria-hidden="true" />Ver material original</button></DialogTrigger>
                                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-4xl overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                    <DialogHeader><DialogTitle>Como escolher o melhor pão?</DialogTitle><DialogDescription>Material original com orientações para ler os rótulos e escolher o pão integral.</DialogDescription></DialogHeader>
                                    <img :src="material.image" alt="Como escolher o melhor pão: cinco orientações, exemplo de ingredientes e opções integrais." class="h-auto w-full rounded-xl" />
                                </DialogContent>
                            </Dialog>
                        </section>

                        <section v-else-if="material.id === 'aveia'" class="space-y-5 rounded-2xl bg-[#1b1915] p-4 text-white sm:p-6" aria-label="Comprei aveia. Como usar?">
                            <header class="grid items-center gap-5 lg:grid-cols-[1.3fr_1fr]">
                                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                                    <svg viewBox="10 15 310 223" width="310" height="223" class="h-auto w-36 shrink-0 rounded-xl" role="img" aria-label="Tigela de aveia em flocos" overflow="hidden"><image :href="material.image" width="1536" height="1024" /></svg>
                                    <h3 class="border-l-4 border-yellow-400 pl-4 font-display text-3xl font-black uppercase leading-tight sm:text-4xl">Comprei aveia.<br /><span class="text-yellow-400">Como usar?</span></h3>
                                </div>
                                <div class="flex items-center gap-4 border-t border-white/50 pt-4 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
                                    <p class="text-base leading-relaxed">A aveia é uma fonte de carboidrato de alta qualidade, rica em fibras, que fornece energia, contribui para a saciedade e auxilia na saúde intestinal.</p>
                                    <Wheat class="hidden size-14 shrink-0 text-yellow-400 sm:block" aria-hidden="true" />
                                </div>
                            </header>
                            <section class="space-y-4 rounded-3xl border border-[#d7bf8c] p-4" aria-labelledby="oat-meals-heading">
                                <h4 id="oat-meals-heading" class="rounded-2xl bg-yellow-400 px-4 py-3 text-center font-display text-xl font-black uppercase leading-tight text-black">5 formas práticas de incluir a aveia na rotina</h4>
                                <ol class="grid items-start gap-5 sm:grid-cols-2 xl:grid-cols-5">
                                    <li v-for="meal in oatMeals" :key="meal.title" class="min-w-0 space-y-3">
                                        <svg :viewBox="meal.viewBox" :width="meal.viewBox.split(' ')[2]" :height="meal.viewBox.split(' ')[3]" class="block h-auto w-full rounded-2xl" role="img" :aria-label="meal.title" overflow="hidden"><image :href="material.image" width="1536" height="1024" /></svg>
                                        <h5 class="text-center font-display text-lg font-black uppercase leading-tight">{{ meal.title }}</h5>
                                        <p class="text-center text-sm leading-relaxed text-white/90">{{ meal.text }}</p>
                                    </li>
                                </ol>
                            </section>
                            <div class="grid gap-4 lg:grid-cols-3">
                                <aside class="rounded-3xl border border-[#d7bf8c] p-4">
                                    <h4 class="mb-3 flex items-center gap-3 font-display text-xl font-black uppercase text-yellow-400"><Zap class="size-8 shrink-0" aria-hidden="true" />Quando usar?</h4>
                                    <p class="text-sm leading-relaxed">A aveia é uma ótima escolha, especialmente em refeições próximas à atividade física, por fornecer energia de forma gradual.</p>
                                </aside>
                                <aside class="rounded-3xl border border-[#d7bf8c] p-4">
                                    <h4 class="mb-3 flex items-center gap-3 font-display text-xl font-black uppercase text-yellow-400"><Wheat class="size-8 shrink-0" aria-hidden="true" />E as fibras?</h4>
                                    <p class="text-sm leading-relaxed">A aveia é rica em fibras (beta-glucana), que ajudam na saciedade, no controle do colesterol e na saúde intestinal.</p>
                                </aside>
                                <aside class="rounded-3xl border border-[#d7bf8c] p-4">
                                    <h4 class="mb-3 rounded-2xl bg-yellow-400 px-3 py-2 font-display text-xl font-black uppercase leading-tight text-black">Psyllium: um aliado a mais</h4>
                                    <p class="text-sm leading-relaxed">Fonte de fibras solúveis, que ajuda no funcionamento do intestino e aumenta a saciedade.</p>
                                    <p class="mt-3 text-sm leading-relaxed"><strong class="font-black uppercase text-yellow-400">Como usar:</strong> adicione 1 colher de sopa (cerca de 5 g) em vitaminas, iogurtes ou preparações como o mingau.</p>
                                </aside>
                            </div>
                            <div class="grid gap-4 lg:grid-cols-2">
                                <aside class="flex flex-col gap-3 rounded-3xl border border-[#d7bf8c] p-5 sm:flex-row sm:items-center">
                                    <h4 class="flex shrink-0 items-center gap-3 font-display text-2xl font-black uppercase leading-tight text-yellow-400"><ShoppingCart class="size-10 shrink-0" aria-hidden="true" /><span>Dica<br />de consumo</span></h4>
                                    <p class="border-t border-[#d7bf8c] pt-3 text-sm leading-relaxed sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0">A aveia pode ser incluída no café da manhã, lanche, pré ou pós-treino, de acordo com a sua rotina, objetivos e prescrição nutricional.</p>
                                </aside>
                                <aside class="flex flex-col gap-3 rounded-3xl bg-yellow-400 p-5 text-black sm:flex-row sm:items-center">
                                    <h4 class="flex shrink-0 items-center gap-3 font-display text-2xl font-black uppercase"><Info class="size-10 shrink-0 stroke-[2.5]" aria-hidden="true" />Lembre-se</h4>
                                    <p class="border-t border-black/60 pt-3 text-sm font-semibold leading-relaxed sm:border-l sm:border-t-0 sm:pl-4 sm:pt-0">As porções e combinações podem variar de acordo com a sua prescrição individual e seus objetivos.</p>
                                </aside>
                            </div>
                            <Dialog>
                                <DialogTrigger as-child><button type="button" class="flex items-center gap-2 text-sm font-bold text-yellow-400 underline"><Maximize2 class="size-4" aria-hidden="true" />Ver material original</button></DialogTrigger>
                                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-6xl overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                    <DialogHeader><DialogTitle>Comprei aveia. Como usar?</DialogTitle><DialogDescription>Material original com cinco combinações, orientações sobre fibras e dicas de consumo.</DialogDescription></DialogHeader>
                                    <img :src="material.image" alt="Comprei aveia: cinco formas práticas de incluir na rotina, fibras, psyllium e dicas de consumo." class="h-auto w-full rounded-xl" />
                                </DialogContent>
                            </Dialog>
                        </section>

                        <section v-else-if="material.id === 'queijos'" class="space-y-5 rounded-2xl bg-[#1b1915] p-4 text-white sm:p-6" aria-label="Comprei queijos. Como usar?">
                            <header class="flex flex-wrap items-center justify-between gap-5">
                                <div class="flex items-center gap-4">
                                    <svg viewBox="43 12 111 80" width="111" height="80" class="hidden h-auto w-24 shrink-0 sm:block" aria-hidden="true" overflow="hidden"><image :href="material.image" width="757" height="461" /></svg>
                                    <h3 class="font-display text-3xl font-black uppercase leading-tight sm:text-4xl">Comprei queijos.<br /><span class="text-yellow-400">Como usar?</span></h3>
                                </div>
                                <p class="border-l-2 border-yellow-400 pl-4 font-display text-lg font-bold uppercase"><strong class="block text-yellow-400">5 combinações</strong>práticas e saborosas</p>
                            </header>
                            <ol class="grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-5">
                                <li v-for="meal in cheeseMeals" :key="meal.title" class="min-w-0 overflow-hidden rounded-2xl border border-[#d7bf8c]/60">
                                    <Dialog>
                                        <DialogTrigger as-child>
                                            <button type="button" class="block w-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-yellow-400" :aria-label="'Ampliar imagem de ' + meal.title">
                                                <NumberedRecipeImage v-if="meal.image" :src="meal.image" :alt="meal.title" :number="2" class="block h-auto w-full" />
                                                <svg v-else :viewBox="meal.viewBox" :width="meal.viewBox.split(' ')[2]" :height="meal.viewBox.split(' ')[3]" class="block h-auto w-full" role="img" :aria-label="meal.title" overflow="hidden"><image :href="material.image" width="757" height="461" /></svg>
                                            </button>
                                        </DialogTrigger>
                                        <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-lg overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                            <DialogHeader><DialogTitle>{{ meal.title }}</DialogTitle><DialogDescription>{{ meal.ingredients }}</DialogDescription></DialogHeader>
                                            <NumberedRecipeImage v-if="meal.image" :src="meal.image" :alt="meal.title" :number="2" class="block h-auto w-full rounded-xl" />
                                            <svg v-else :viewBox="meal.viewBox" :width="meal.viewBox.split(' ')[2]" :height="meal.viewBox.split(' ')[3]" class="block h-auto w-full rounded-xl" role="img" :aria-label="meal.title" overflow="hidden"><image :href="material.image" width="757" height="461" /></svg>
                                        </DialogContent>
                                    </Dialog>
                                    <div class="space-y-3 p-4 text-center">
                                        <h4 class="font-display text-lg font-black uppercase leading-tight">{{ meal.title }}</h4>
                                        <p class="text-sm leading-relaxed text-white/90">{{ meal.ingredients }}</p>
                                    </div>
                                </li>
                            </ol>
                            <aside class="flex flex-col gap-3 rounded-3xl border-2 border-yellow-400 p-4 sm:flex-row sm:items-center">
                                <h4 class="flex shrink-0 items-center gap-3 font-display text-2xl font-black uppercase text-yellow-400"><Lightbulb class="size-10 shrink-0" aria-hidden="true" />Dica:</h4>
                                <p class="text-sm leading-relaxed">Os queijos adicionam proteína e sabor às refeições. Prefira versões mais magras, como minas, cottage, ricota ou muçarela light.</p>
                            </aside>
                        </section>

                        <section v-else-if="material.id === 'whey'" class="space-y-5 rounded-2xl bg-[#1b1915] p-4 text-white sm:p-6" aria-label="Comprei whey protein. Como usar?">
                            <header class="flex flex-wrap items-center justify-between gap-5">
                                <div class="flex items-center gap-4">
                                    <svg viewBox="45 2 85 91" width="85" height="91" class="hidden h-auto w-20 shrink-0 sm:block" aria-hidden="true" overflow="hidden"><image :href="material.image" width="757" height="443" /></svg>
                                    <h3 class="font-display text-3xl font-black uppercase leading-tight sm:text-4xl">Comprei whey protein.<br /><span class="text-yellow-400">Como usar?</span></h3>
                                </div>
                                <p class="border-l-2 border-yellow-400 pl-4 font-display text-lg font-bold uppercase"><strong class="block text-yellow-400">5 combinações</strong>práticas e saborosas</p>
                            </header>
                            <ol class="grid items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-5">
                                <li v-for="meal in wheyMeals" :key="meal.title" class="min-w-0 overflow-hidden rounded-2xl border border-[#d7bf8c]/60">
                                    <Dialog>
                                        <DialogTrigger as-child>
                                            <button type="button" class="block w-full focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-yellow-400" :aria-label="'Ampliar imagem de ' + meal.title">
                                                <svg :viewBox="meal.viewBox" :width="meal.viewBox.split(' ')[2]" :height="meal.viewBox.split(' ')[3]" class="block h-auto w-full" role="img" :aria-label="meal.title" overflow="hidden"><image :href="material.image" width="757" height="443" /></svg>
                                            </button>
                                        </DialogTrigger>
                                        <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-lg overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                            <DialogHeader><DialogTitle>{{ meal.title }}</DialogTitle><DialogDescription>{{ meal.ingredients }}</DialogDescription></DialogHeader>
                                            <svg :viewBox="meal.viewBox" :width="meal.viewBox.split(' ')[2]" :height="meal.viewBox.split(' ')[3]" class="block h-auto w-full rounded-xl" role="img" :aria-label="meal.title" overflow="hidden"><image :href="material.image" width="757" height="443" /></svg>
                                        </DialogContent>
                                    </Dialog>
                                    <div class="space-y-3 p-4 text-center">
                                        <h4 class="font-display text-lg font-black uppercase leading-tight">{{ meal.title }}</h4>
                                        <p class="text-sm leading-relaxed text-white/90">{{ meal.ingredients }}</p>
                                    </div>
                                </li>
                            </ol>
                            <aside class="flex flex-col gap-3 rounded-3xl border-2 border-yellow-400 p-4 sm:flex-row sm:items-center">
                                <h4 class="flex shrink-0 items-center gap-3 font-display text-2xl font-black uppercase text-yellow-400"><Lightbulb class="size-10 shrink-0" aria-hidden="true" />Dica:</h4>
                                <p class="text-sm leading-relaxed">O whey é uma forma prática de aumentar a ingestão de proteína e pode ser usado em vitaminas, cremes e preparações do dia a dia.</p>
                            </aside>
                            <Dialog>
                                <DialogTrigger as-child><button type="button" class="flex items-center gap-2 text-sm font-bold text-yellow-400 underline"><Maximize2 class="size-4" aria-hidden="true" />Ver material original</button></DialogTrigger>
                                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-5xl overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                    <DialogHeader><DialogTitle>Comprei whey protein. Como usar?</DialogTitle><DialogDescription>Cinco combinações práticas e saborosas com whey protein.</DialogDescription></DialogHeader>
                                    <img :src="material.image" alt="Material original com cinco combinações de whey protein e dica de uso." class="h-auto w-full rounded-xl" />
                                </DialogContent>
                            </Dialog>
                        </section>

                        <section v-else-if="material.id === 'ovos'" class="space-y-5 rounded-2xl bg-[#1b1915] p-4 text-white sm:p-6" aria-label="Como variar os ovos?">
                            <header class="flex flex-wrap items-center justify-between gap-5">
                                <div class="flex items-center gap-4">
                                    <svg viewBox="29 6 123 87" width="123" height="87" class="hidden h-auto w-24 shrink-0 sm:block" aria-hidden="true" overflow="hidden"><image :href="material.image" width="756" height="443" /></svg>
                                    <h3 class="font-display text-3xl font-black uppercase leading-tight sm:text-4xl">Comprei ovos.<br /><span class="text-yellow-400">Como usar?</span></h3>
                                </div>
                                <p class="border-l-2 border-yellow-400 pl-4 font-display text-lg font-bold uppercase"><strong class="block text-yellow-400">5 combinações</strong>práticas e saborosas</p>
                            </header>
                            <IllustratedRecipeCards :image="material.image" :width="756" :height="443" :meals="eggMeals" />
                            <aside class="flex flex-col gap-3 rounded-3xl border-2 border-yellow-400 p-4 sm:flex-row sm:items-center">
                                <h4 class="flex shrink-0 items-center gap-3 font-display text-2xl font-black uppercase text-yellow-400"><Lightbulb class="size-10 shrink-0" aria-hidden="true" />Dica:</h4>
                                <p class="text-sm leading-relaxed">O ovo é uma proteína de alto valor biológico, versátil e prática. Combine com fontes de carboidrato (pão, aveia, tapioca) e vegetais para refeições mais completas.</p>
                            </aside>
                            <Dialog>
                                <DialogTrigger as-child><button type="button" class="flex items-center gap-2 text-sm font-bold text-yellow-400 underline"><Maximize2 class="size-4" aria-hidden="true" />Ver material original</button></DialogTrigger>
                                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-5xl overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                    <DialogHeader><DialogTitle>Como variar os ovos?</DialogTitle><DialogDescription>Cinco combinações práticas e saborosas com ovos.</DialogDescription></DialogHeader>
                                    <img :src="material.image" alt="Material original com cinco combinações com ovos e dica de uso." class="h-auto w-full rounded-xl" />
                                </DialogContent>
                            </Dialog>
                        </section>

                        <section v-else-if="material.id === 'iogurte'" class="space-y-5 rounded-2xl bg-[#1b1915] p-4 text-white sm:p-6" aria-label="Comprei iogurte. Como usar?">
                            <header class="flex flex-wrap items-center justify-between gap-5">
                                <div class="flex items-center gap-4">
                                    <svg viewBox="44 9 132 93" width="132" height="93" class="hidden h-auto w-24 shrink-0 sm:block" aria-hidden="true" overflow="hidden"><image :href="material.image" width="756" height="461" /></svg>
                                    <h3 class="font-display text-3xl font-black uppercase leading-tight sm:text-4xl">Comprei iogurte.<br /><span class="text-yellow-400">Como usar?</span></h3>
                                </div>
                                <p class="border-l-2 border-yellow-400 pl-4 font-display text-lg font-bold uppercase"><strong class="block text-yellow-400">5 combinações</strong>práticas e saborosas</p>
                            </header>
                            <IllustratedRecipeCards :image="material.image" :width="756" :height="461" :meals="yogurtMeals" />
                            <aside class="flex flex-col gap-3 rounded-3xl border-2 border-yellow-400 p-4 sm:flex-row sm:items-center">
                                <h4 class="flex shrink-0 items-center gap-3 font-display text-2xl font-black uppercase text-yellow-400"><Lightbulb class="size-10 shrink-0" aria-hidden="true" />Dica:</h4>
                                <p class="text-sm leading-relaxed">{{ material.tip }}</p>
                            </aside>
                            <Dialog>
                                <DialogTrigger as-child><button type="button" class="flex items-center gap-2 text-sm font-bold text-yellow-400 underline"><Maximize2 class="size-4" aria-hidden="true" />Ver material original</button></DialogTrigger>
                                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-5xl overflow-y-auto rounded-xl border-brand-line2 bg-brand-card text-brand-cream">
                                    <DialogHeader><DialogTitle>Comprei iogurte. Como usar?</DialogTitle><DialogDescription>Cinco combinações práticas e saborosas com iogurte.</DialogDescription></DialogHeader>
                                    <img :src="material.image" alt="Material original com cinco combinações com iogurte e dica de consumo." class="h-auto w-full rounded-xl" />
                                </DialogContent>
                            </Dialog>
                        </section>

                        <NutriRecommendations v-else-if="material.id === 'nutri-indica'" />

                        <Dialog v-else>
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

                        <section v-if="material.combinations.length || material.id === 'pao'" class="surface-card p-4">
                            <button type="button" :disabled="!recipeUnlock.unlocked" :aria-expanded="recipeUnlock.unlocked ? openRecipes === material.id : undefined" :aria-controls="recipeUnlock.unlocked ? 'recipes-' + material.id : undefined" class="flex w-full items-center justify-center gap-3 rounded-xl border border-brand-gold/40 bg-brand-gold/10 px-4 py-3 text-sm font-bold text-brand-gold disabled:cursor-not-allowed" @click="openRecipes = openRecipes === material.id ? null : material.id">
                                <LockKeyhole v-if="!recipeUnlock.unlocked" class="size-5 shrink-0" aria-hidden="true" />
                                <span>{{ material.id === 'pao' ? 'Sugestão de marcas' : material.id === 'whey' ? 'Como escolher o Whey e sugestão de marcas' : recipeUnlock.unlocked ? (openRecipes === material.id ? 'Fechar receitas desbloqueadas' : 'Ver receitas desbloqueadas') : `Receitas serão desbloqueadas após ${recipeUnlock.requiredDays} dias com os 5 objetivos cumpridos` }}</span>
                            </button>
                            <p v-if="!recipeUnlock.unlocked" class="mt-3 text-sm text-brand-muted">Seu progresso: {{ recipeUnlock.completedDays }} / {{ recipeUnlock.requiredDays }} dias completos. Os dias não precisam ser consecutivos.</p>
                            <div v-if="recipeUnlock.unlocked && openRecipes === material.id" :id="'recipes-' + material.id">
                            <p v-if="material.id === 'pao'" class="mt-4 text-sm text-brand-muted">As sugestões de marcas serão disponibilizadas em breve.</p>
                            <ol v-else class="mt-4 grid gap-3 sm:grid-cols-2">
                                <li v-for="(combination, combinationIndex) in material.combinations" :key="combination.title" class="flex items-start gap-3 rounded-lg border border-brand-line2 p-3">
                                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-brand-gold text-sm font-bold text-brand-ink">{{ combinationIndex + 1 }}</span>
                                    <div class="min-w-0 space-y-2">
                                        <h3 class="font-bold">{{ combination.title }}</h3>
                                        <p class="text-sm leading-relaxed text-brand-muted">{{ combination.ingredients }}</p>
                                    </div>
                                </li>
                            </ol>
                            <p v-if="material.tip" class="mt-4 text-sm leading-relaxed text-brand-muted"><strong class="text-brand-gold">Dica da Nutri</strong> · {{ material.tip }}</p>
                            </div>
                        </section>
                    </div>
                </section>
            </div>

        </main>
    </AppLayout>
</template>
