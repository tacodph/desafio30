<script setup lang="ts">
import ParticipationCta from '@/components/ParticipationCta.vue';
import { useChallenge } from '@/composables/useChallenge';

import type { GoalKey } from '@/data/goalDetails';
import { images } from '@/data/images';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDays, LockKeyhole, Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';

const { countdown, goals, fails, failTotal, failNote, failDots, incFail, decFail, milestones } = useChallenge();

const menuOpen = ref(false);

const nutriMaterials = [
    {
        title: 'Kit da vida real',
        description: 'Produtos industrializados práticos para ter à mão: iogurtes, bebidas proteicas, mix de castanhas, barrinhas e outras opções de mercado.',
        image: '/images/nutri/kit.jpg',
        alt: 'Iogurtes, castanhas e lanches práticos para a rotina.',
    },
    {
        title: 'Refeições práticas',
        description: '5 opções de café da manhã, 5 almoços, 5 jantares e 5 lanches fáceis de preparar.',
        image: '/images/nutri/refeicoes.jpg',
        alt: 'Opções simples de café da manhã, almoço e jantar.',
    },
    {
        title: 'Produtos do dia a dia',
        description: 'Aprenda a ler rótulos, comparar produtos e fazer escolhas mais conscientes no supermercado.',
        image: '/images/nutri/produtos.jpg',
        alt: 'Comparação de rótulos de produtos no supermercado.',
    },
    {
        title: 'SOS da vida real',
        description: 'SOS fim de semana, vontade de doce e como fazer escolhas em restaurantes.',
        image: '/images/nutri/sos.jpg',
        alt: 'Refeições compartilhadas em um encontro no restaurante.',
    },
    {
        title: 'Guia de hidratação e sono',
        description: 'Estratégias simples para melhorar sua hidratação e qualidade do sono.',
        image: '/images/nutri/sono.jpg',
        alt: 'Quarto tranquilo com água disponível na mesa de cabeceira.',
    },
];

const goalCardCopy: Record<GoalKey, { frequency: string; title: string; desc: string; supportTitle?: string }> = {
    alimentacao: {
        frequency: 'Diariamente',
        title: 'Alimentação e organização',
        desc: '30 dias de alimentação limpa, com planejamento e praticidade.',
    },
    treino: {
        frequency: 'Check-in diário',
        title: 'Movimento e treino',
        desc: 'Registre seu treino ou atividade física. A referência é de 45 minutos por dia, respeitando seu nível e sua rotina.',
    },
    hidratacao: {
        frequency: 'Check-in diário',
        title: 'Água e sono',
        desc: 'Cumpra sua meta individual de água e busque pelo menos 7 horas de sono por noite. Registre os dois hábitos diariamente.',
    },
    habilidade: {
        frequency: '3x / sem',
        title: 'Habilidade nova',
        supportTitle: 'Autonomia na rotina',
        desc: 'Desenvolva habilidades: planejar o cardápio, preparar marmitas, aprender receitas e organizar suas compras.',
    },
    plus: {
        frequency: '2x / sem',
        title: 'Refeição luxo',
        desc: 'Escolha previamente 2 refeições na semana para comer o que gosta, dessa forma você se mantém firme para seguir no desafio.',
    },
};

const goalHref = (key: GoalKey) => (key === 'plus' ? route('objetivo.luxo') : route('objetivos.show', key));

const navLinks = [
    { href: '#objetivos', label: 'Objetivos' },
    { href: '#regras', label: 'Regras' },
    { href: '#jornada', label: 'Jornada' },
    { href: '#mindset', label: 'Mindset' },
    { href: '#nutri', label: 'Nutri' },
    { href: '#parceiros', label: 'Parceiros' },
];
</script>

<template>
    <Head title="A gente cresce junto" />

    <div class="min-h-screen overflow-x-hidden bg-brand-ink text-brand-cream">
        <header class="sticky top-0 z-20 border-b border-brand-line bg-brand-ink/88 backdrop-blur-[10px]">
            <div class="mx-auto flex max-w-[1200px] items-center justify-between gap-4 px-6 py-3">
                <a href="#topo" class="flex items-center gap-2.5 text-brand-cream hover:text-brand-cream">
                    <img :src="images.logoMark" alt="Desafio 30 Dias" class="size-11 object-contain" />
                    <span class="font-display text-[22px] font-extrabold italic uppercase tracking-wide">
                        Desafio <span class="text-brand-gold">30</span> Dias
                    </span>
                </a>

                <nav class="hidden items-center justify-end gap-6 lg:flex">
                    <a
                        v-for="link in navLinks"
                        :key="link.href"
                        :href="link.href"
                        class="text-[15px] font-medium text-brand-muted-3 hover:text-brand-cream"
                    >
                        {{ link.label }}
                    </a>
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('login')"
                        class="rounded-full bg-brand-gold px-[18px] py-2.5 text-[15px] font-bold text-brand-ink hover:bg-brand-gold-hover hover:text-brand-ink"
                    >
                        Entrar
                    </Link>
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="text-[15px] font-medium text-brand-muted-3 hover:text-brand-cream"
                    >
                        Painel
                    </Link>
                </nav>

                <button class="rounded-full border border-brand-line3 p-2 text-brand-cream lg:hidden" type="button" @click="menuOpen = !menuOpen">
                    <X v-if="menuOpen" class="size-5" />
                    <Menu v-else class="size-5" />
                </button>
            </div>

            <nav v-if="menuOpen" class="flex flex-col gap-3 border-t border-brand-line px-6 py-4 lg:hidden">
                <a
                    v-for="link in navLinks"
                    :key="link.href"
                    :href="link.href"
                    class="text-[15px] font-medium text-brand-muted-3"
                    @click="menuOpen = false"
                >
                    {{ link.label }}
                </a>
                <Link
                    v-if="!$page.props.auth.user"
                    :href="route('login')"
                    class="inline-flex w-fit rounded-full bg-brand-gold px-[18px] py-2.5 text-[15px] font-bold text-brand-ink"
                >
                    Entrar
                </Link>
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-[15px] font-medium text-brand-muted-3"> Painel </Link>
            </nav>
        </header>

        <section
            id="topo"
            class="relative mx-auto max-w-[1200px] px-6 py-10 sm:py-16 lg:py-24"
        >
            <div class="relative z-10 flex flex-col gap-7">
                <div class="flex items-center gap-3 text-sm font-bold uppercase tracking-[3px] text-brand-gold">
                    <span class="h-0.5 w-8 bg-brand-gold" />
                    <span>9 nov → 8 dez · 30 dias</span>
                </div>
                <h1 class="display-title m-0 text-[clamp(56px,8.4vw,112px)] tracking-[-1px]">
                    <span class="block text-[clamp(28px,4vw,44px)] italic leading-tight tracking-normal">DESAFIO DA VIRADA</span>
                    <span class="text-brand-gold">30 dias.</span><br />5 objetivos.
                </h1>
                <p class="m-0 max-w-[520px] text-[clamp(18px,1.6vw,21px)] leading-normal text-brand-muted-3">
                    Enquanto 2027 se aproxima, a gente se prepara.<br />
                    Não é só ficar com o corpo melhor (spoiler: vai ficar).<br />
                    É sobre não esperar o momento perfeito pra começar sua transformação.
                </p>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('participar')"
                        class="inline-flex items-center gap-2.5 rounded-full bg-brand-gold px-7 py-4 text-[17px] font-bold text-brand-ink hover:bg-brand-gold-hover hover:text-brand-ink"
                    >
                        Quem topa? <span>→</span>
                    </Link>
                    <a
                        href="#regras"
                        class="rounded-full border-[1.5px] border-brand-line3 px-7 py-4 text-[17px] font-semibold text-brand-cream hover:border-brand-cream hover:text-brand-cream"
                    >
                        Ver as regras
                    </a>
                </div>

                <div class="flex flex-col gap-3 pt-2">
                    <div class="text-[13px] font-bold uppercase tracking-[2.5px] text-brand-muted-2">{{ countdown.label }}</div>
                    <div v-if="countdown.isCounting" class="flex flex-wrap gap-2.5">
                        <div
                            v-for="unit in countdown.units"
                            :key="unit.l"
                            class="flex min-w-[78px] flex-col items-center gap-0.5 rounded-[14px] border border-brand-line2 bg-brand-card2 px-3.5 py-3"
                        >
                            <span class="font-display text-[40px] font-extrabold leading-none tabular-nums text-brand-cream">{{ unit.v }}</span>
                            <span class="text-xs font-semibold uppercase tracking-widest text-brand-muted-2">{{ unit.l }}</span>
                        </div>
                    </div>
                    <div v-else-if="countdown.isRunning" class="flex max-w-[440px] flex-col gap-2.5">
                        <div class="font-display text-[44px] font-extrabold italic uppercase leading-none">
                            Dia <span class="text-brand-gold">{{ countdown.day }}</span> de 30
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-brand-line">
                            <div class="h-full rounded-full bg-brand-gold" :style="{ width: countdown.pct }" />
                        </div>
                    </div>
                    <div v-else class="font-display text-[44px] font-extrabold italic uppercase leading-none text-brand-gold">
                        30 dias firmes. Uma virada de hábitos.
                    </div>
                </div>
            </div>

        </section>

        <div class="border-y border-brand-line bg-brand-strip">
            <div
                class="font-display mx-auto flex max-w-[1200px] justify-between gap-x-6 overflow-x-auto whitespace-nowrap px-6 py-[18px] text-[clamp(12px,1.7vw,22px)] font-bold uppercase tracking-[1px] lg:tracking-[2px]"
            >
                <span v-for="goal in goals" :key="goal.key" class="shrink-0" :style="{ color: goal.color }">{{ goal.title }}</span>
            </div>
        </div>

        <section id="objetivos" class="mx-auto max-w-[1200px] px-6 py-[clamp(72px,9vw,120px)]">
            <div class="mb-10 flex flex-wrap items-end justify-between gap-6">
                <div class="flex flex-col gap-3.5">
                    <div class="eyebrow">Consistência, não perfeição</div>
                    <h2 class="display-title m-0 text-[clamp(44px,6vw,80px)]">5 objetivos</h2>
                </div>
            </div>

            <div class="mb-10 flex max-w-[760px] flex-col gap-5">
                <p class="m-0 text-[18px] leading-relaxed text-brand-muted">
                    O Desafio da Virada é um compromisso de 30 dias seguidos, de segunda, 9 de novembro, a terça, 8 de dezembro de 2026. Virada é o convite para transformar a rotina e construir novos hábitos.
                </p>
                <p class="m-0 text-[18px] leading-relaxed text-brand-muted">
                    São 5 objetivos que cuidam do movimento, da hidratação e do sono, da autonomia na rotina, da alimentação e de pequenas experiências que deixam a vida mais prazerosa.
                </p>
                <p class="m-0 text-[18px] leading-relaxed text-brand-muted">
                    Você vai treinar seu autocontrole, experimentar coisas novas e encarar desafios semanais com metas possíveis, que vão te mostrar do que você é capaz quando se compromete com suas escolhas.
                </p>
            </div>

            <div class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,200px),1fr))] gap-4">
                <Link
                    v-for="goal in goals"
                    :key="goal.key"
                    :href="goalHref(goal.key)"
                    class="flex flex-col gap-4 rounded-[22px] border-2 border-brand-line2 bg-brand-card p-6 text-brand-cream transition hover:-translate-y-0.5 hover:border-brand-line3 hover:text-brand-cream"
                >
                    <img :src="goal.icon" alt="" class="block size-[88px]" />
                    <div class="flex flex-1 flex-col gap-1.5">
                        <span class="text-xs font-bold uppercase tracking-wider text-brand-muted">Frequência</span>
                        <span class="font-display text-[34px] font-extrabold leading-none" :style="{ color: goal.color }">{{ goalCardCopy[goal.key].frequency }}</span>
                        <span class="font-display text-[22px] font-bold uppercase leading-tight tracking-wide text-brand-cream">{{ goalCardCopy[goal.key].title }}</span>
                        <span v-if="goalCardCopy[goal.key].supportTitle" class="text-[15px] font-semibold leading-snug text-brand-cream">
                            {{ goalCardCopy[goal.key].supportTitle }}
                        </span>
                        <span class="text-[15px] leading-snug text-brand-muted">{{ goalCardCopy[goal.key].desc }}</span>
                    </div>
                    <span class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider" :style="{ color: goal.color }">
                        <span>Ver regra e dicas</span><span>→</span>
                    </span>
                </Link>
            </div>
            <ParticipationCta class="mt-8" />
        </section>

        <section id="regras" class="mx-auto flex max-w-[1200px] flex-col gap-10 px-6 py-[clamp(72px,9vw,120px)]">
            <div class="flex flex-col gap-3.5">
                <div class="eyebrow">Como funciona</div>
                <h2 class="display-title m-0 text-[clamp(44px,6vw,80px)]">Regras simples.<br />Compromisso sério.</h2>
                <p class="m-0 max-w-[560px] text-[17px] leading-relaxed text-brand-muted">
                    Dá pra fazer sozinho ou com a sua turma. Quem for em grupo cria o próprio grupo — no WhatsApp e no Gymrats — e segue as regras de
                    grupo.
                </p>
            </div>

            <div class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,250px),1fr))] gap-3">
                <div class="surface-card flex flex-col gap-3 p-7">
                    <span class="font-display text-xl font-extrabold text-brand-gold">01</span>
                    <h3 class="font-display m-0 text-[28px] font-extrabold uppercase leading-none">Check-in até 23h</h3>
                    <p class="m-0 text-base leading-normal text-brand-muted">Faça seu check-in diário dos cinco objetivos: alimentação, treino, água, sono, habilidade nova e refeição luxo.</p>
                </div>
                <div class="surface-card flex flex-col gap-3 p-7">
                    <span class="font-display text-xl font-extrabold text-brand-gold">02</span>
                    <h3 class="font-display m-0 text-[28px] font-extrabold uppercase leading-none">Conquistas do dia</h3>
                    <p class="m-0 text-base leading-normal text-brand-muted">Registre suas conquistas, metas batidas e situações que você superou. Reconheça sua evolução ao longo dos 30 dias.</p>
                </div>
                <div class="surface-card flex flex-col gap-3 p-7">
                    <span class="font-display text-xl font-extrabold text-brand-gold">03</span>
                    <h3 class="font-display m-0 text-[28px] font-extrabold uppercase leading-none">Superação e aprendizado</h3>
                    <p class="m-0 text-base leading-normal text-brand-muted">Teve um dia difícil? Identifique o que pode melhorar, aprenda com a experiência e siga em frente no dia seguinte.</p>
                </div>
                <div class="surface-card flex flex-col gap-3 p-7">
                    <span class="font-display text-xl font-extrabold text-brand-gold">04</span>
                    <h3 class="font-display m-0 text-[28px] font-extrabold uppercase leading-none">Evolução na prática</h3>
                    <p class="m-0 text-base leading-normal text-brand-muted">Bateu uma meta ou foi além do planejado? Registre suas vitórias e valorize cada avanço. Toda conquista conta!</p>
                </div>
            </div>

            <div
                id="penalidade"
                class="grid items-center gap-7 rounded-[26px] bg-brand-gold p-7 text-brand-ink lg:grid-cols-2 lg:gap-14 lg:p-12"
            >
                <div class="flex flex-col gap-[22px]">
                    <div class="flex flex-col gap-2.5">
                        <span class="text-sm font-bold uppercase tracking-[3px]">Opcional · no dia 1</span>
                        <h3 class="display-title m-0 text-[clamp(48px,6vw,84px)] leading-[0.86]">Penalidade<br />solidária</h3>
                        <span class="font-script text-[clamp(24px,2.6vw,32px)] leading-tight">Um compromisso extra com você mesmo.</span>
                    </div>
                    <p class="m-0 max-w-[520px] text-[19px] font-semibold leading-snug">
                        Quem topar, guarda <strong class="font-extrabold">R$ 10,00</strong> por dia sem check-in. No fim, vira doação ou comemoração.
                    </p>
                </div>

                <div class="flex flex-col gap-[22px] rounded-[22px] bg-brand-ink p-6 text-brand-cream lg:p-9">
                    <span class="text-[13px] font-bold uppercase tracking-[2px] text-brand-muted-2">Calcule sua doação</span>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-[17px] font-semibold text-brand-muted-3">Dias não cumpridos</span>
                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                aria-label="Menos um dia"
                                class="flex size-11 items-center justify-center rounded-full border-[1.5px] border-brand-line3 text-[22px] font-bold text-brand-cream hover:border-brand-cream"
                                @click="decFail"
                            >
                                −
                            </button>
                            <span class="font-display min-w-14 text-center text-[40px] font-extrabold leading-none tabular-nums">{{ fails }}</span>
                            <button
                                type="button"
                                aria-label="Mais um dia"
                                class="flex size-11 items-center justify-center rounded-full border-[1.5px] border-brand-line3 text-[22px] font-bold text-brand-cream hover:border-brand-cream"
                                @click="incFail"
                            >
                                +
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1">
                        <span v-for="(dot, index) in failDots" :key="index" class="size-2.5 rounded-sm" :style="{ background: dot.c }" />
                    </div>
                    <div class="flex flex-col gap-1.5 border-t border-brand-line2 pt-[18px]">
                        <span class="text-[13px] font-bold uppercase tracking-[2px] text-brand-muted-2">Valor acumulado para doar</span>
                        <span class="font-display text-[clamp(56px,6vw,76px)] font-black leading-[0.9] tabular-nums text-brand-gold">{{
                            failTotal
                        }}</span>
                        <span class="text-[15px] leading-normal text-brand-muted">{{ failNote }}</span>
                    </div>
                </div>
            </div>
            <ParticipationCta />
        </section>

        <section id="jornada" class="border-y border-brand-line bg-brand-strip">
            <div class="mx-auto flex max-w-[1200px] flex-col gap-12 px-6 py-[clamp(72px,9vw,120px)]">
                <div class="flex flex-col gap-3.5">
                    <div class="eyebrow">A jornada</div>
                    <h2 class="display-title m-0 text-[clamp(44px,6vw,80px)]">Do dia 1<br />ao dia 30</h2>
                </div>
                <div class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,160px),1fr))] gap-3">
                    <div
                        v-for="mile in milestones"
                        :key="mile.day"
                        class="flex flex-col gap-3.5 rounded-[20px] px-[18px] py-[22px]"
                        :style="{ background: mile.bg, border: `1.5px solid ${mile.border}` }"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[13px] font-bold uppercase tracking-widest text-brand-muted-2">{{ mile.date }}</span>
                            <span class="text-[11px] font-bold uppercase tracking-widest" :style="{ color: mile.tagColor }">{{ mile.tag }}</span>
                        </div>
                        <span class="font-display text-[48px] font-black italic leading-[0.85]" :style="{ color: mile.numColor }">Dia {{ mile.day }}</span>
                        <span class="font-display text-[22px] font-extrabold uppercase leading-tight">{{ mile.title }}</span>
                        <span class="text-[15px] leading-normal text-brand-muted">{{ mile.line }}</span>
                    </div>
                </div>
                <ParticipationCta />
            </div>
        </section>

        <section id="mindset" class="mx-auto flex max-w-[1200px] flex-col gap-10 px-6 py-[clamp(72px,9vw,120px)]">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div class="flex flex-col gap-3.5">
                    <div class="eyebrow">O mindset</div>
                    <h2 class="display-title m-0 text-[clamp(44px,6vw,80px)]">Sozinho ou<br />em grupo</h2>
                </div>
                <p class="m-0 max-w-[400px] text-[17px] leading-relaxed text-brand-muted">
                    O jeito de fazer muda. O espírito, não. Quatro princípios que valem pra todo mundo.
                </p>
            </div>

            <div class="flex flex-col border-t border-brand-line2">
                <div
                    v-for="item in [
                        {
                            n: '01',
                            title: 'Comunidade,<br>não competição',
                            solo: 'Sua única comparação é com quem você era ontem.',
                            group: 'O ambiente muda tudo! Motivação em grupo para todo mundo cruzar a linha de chegada melhor do que começou.',
                        },
                        {
                            n: '02',
                            title: 'Acolher,<br>não julgar',
                            solo: 'Falhou um dia? Sem culpa. Recomeça amanhã com mais vontade.',
                            group: 'Quem tropeçar recebe mão estendida, não dedo apontado.',
                        },
                        {
                            n: '03',
                            title: 'Celebrar<br>tudo',
                            solo: 'Primeira marmita, primeiro treino, primeira habilidade — anota e comemora.',
                            group: 'A vitória de um é a de todos.',
                        },
                        {
                            n: '04',
                            title: 'Lembrar<br>todo dia',
                            solo: 'Você não está só: tem gente fazendo o mesmo desafio, no seu ritmo.',
                            group: 'Amigo que melhora junto, emagrece junto, fica mais saudável junto e faz refeição luxo junto! 😂',
                        },
                    ]"
                    :key="item.n"
                    class="flex flex-wrap items-start gap-5 border-b border-brand-line2 py-8 lg:gap-10"
                >
                    <div class="flex min-w-[280px] flex-1 items-baseline gap-[18px]">
                        <span class="font-display text-xl font-extrabold text-brand-gold">{{ item.n }}</span>
                        <h3 class="display-title m-0 text-[clamp(30px,3.4vw,44px)] leading-[0.95]" v-html="item.title" />
                    </div>
                    <div class="grid min-w-[280px] flex-[2] grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <span class="text-xs font-bold uppercase tracking-[2px] text-brand-conhecimento">Sozinho</span>
                            <p class="m-0 text-base leading-relaxed text-brand-muted-3">{{ item.solo }}</p>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <span class="text-xs font-bold uppercase tracking-[2px] text-brand-gold">Em grupo</span>
                            <p class="m-0 text-base leading-relaxed text-brand-muted-3">{{ item.group }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center gap-5 pt-2">
                <span class="font-script text-center text-[clamp(34px,5vw,60px)] leading-tight text-brand-gold">“A gente cresce junto”</span>
                <ParticipationCta class="justify-center" />
            </div>
        </section>

        <section id="nutri" aria-labelledby="nutri-title" class="border-t border-brand-line bg-brand-strip">
            <div class="mx-auto flex max-w-[1200px] flex-col gap-9 px-6 py-[clamp(72px,9vw,120px)]">
                <div class="flex max-w-[760px] flex-col gap-3.5">
                    <div class="eyebrow">Conteúdo exclusivo para participantes</div>
                    <h2 id="nutri-title" class="display-title m-0 text-[clamp(44px,6vw,80px)]">Cantinho da nutri</h2>
                    <p class="m-0 text-[18px] leading-relaxed text-brand-muted-3">
                        Materiais exclusivos para facilitar sua rotina durante o desafio.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-x-6 gap-y-9 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="item in nutriMaterials" :key="item.title" class="flex min-w-0 flex-col gap-4">
                        <img
                            :src="item.image"
                            :alt="item.alt"
                            width="1536"
                            height="1024"
                            loading="lazy"
                            decoding="async"
                            class="aspect-[16/10] w-full rounded-[22px] border border-brand-line2 object-cover"
                        />
                        <div class="flex flex-col gap-2.5">
                            <h3 class="font-display m-0 text-[26px] font-extrabold uppercase leading-tight">{{ item.title }}</h3>
                            <p class="m-0 text-base leading-relaxed text-brand-muted-3">{{ item.description }}</p>
                        </div>
                    </article>
                </div>

                <article class="flex flex-col gap-4 rounded-[22px] border border-brand-gold/30 bg-brand-gold/5 p-5 sm:flex-row sm:items-start sm:gap-5 sm:p-6">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand-gold/10 text-brand-gold">
                        <CalendarDays class="size-5" aria-hidden="true" />
                    </div>
                    <div class="flex min-w-0 flex-1 flex-col gap-4">
                        <h3 class="m-0 text-lg font-bold text-brand-cream">Bônus exclusivo</h3>
                        <div class="flex flex-col gap-4 border-t border-brand-gold/20 pt-5">
                            <p class="m-0 max-w-[780px] text-[18px] leading-relaxed text-brand-cream">
                                Material: <strong>GUIA DE CONTINUIDADE — Como não perder tudo o que você construiu nesses 30 dias.</strong>
                            </p>
                            <div class="flex flex-col gap-3">
                                <p class="m-0 text-base font-semibold text-brand-muted-3">O bônus</p>
                                <ul class="m-0 flex list-disc flex-col gap-2 pl-5 text-base leading-relaxed text-brand-muted-3">
                                    <li>Como manter os hábitos depois do desafio.</li>
                                    <li>Como organizar a próxima semana.</li>
                                    <li>Como lidar com dias ruins sem abandonar tudo.</li>
                                    <li>Como estabelecer novas metas.</li>
                                </ul>
                            </div>
                        </div>
                        <span class="mt-1 inline-flex items-center gap-2 text-sm font-semibold text-brand-gold">
                            <LockKeyhole class="size-4 shrink-0" aria-hidden="true" />
                            Exclusivo a partir da última semana do Desafio da Virada
                        </span>
                    </div>
                </article>
                <ParticipationCta />
            </div>
        </section>

        <section id="parceiros" class="mx-auto flex max-w-[1200px] flex-col gap-10 px-6 py-[clamp(72px,9vw,120px)]">
            <div class="flex flex-col gap-3.5">
                <div class="eyebrow">Siga a nutri nas redes sociais</div>
                <h2 class="display-title m-0 text-[clamp(44px,6vw,80px)]">Siga a nutri nas redes sociais</h2>
            </div>

            <article class="grid items-center gap-8 overflow-hidden rounded-[22px] border border-brand-line2 bg-brand-card2 p-5 sm:p-8 lg:grid-cols-[320px_1fr]">
                <img :src="images.helen" alt="Helen Freitas" class="aspect-[3/4] w-full rounded-[18px] object-cover object-top" />
                <div class="flex flex-col gap-5">
                    <span class="text-xs font-bold uppercase tracking-[2px] text-brand-gold">Nutricionista</span>
                    <h3 class="display-title m-0 text-[clamp(40px,5vw,64px)] leading-none">Helen Freitas</h3>
                    <div class="flex flex-col gap-3 text-[17px]">
                        <a
                            href="https://www.instagram.com/helenfreitasnutri/"
                            target="_blank"
                            rel="noopener"
                            class="font-semibold text-brand-cream hover:text-brand-gold"
                        >
                            Instagram · @helenfreitasnutri
                        </a>
                        <a href="https://helenfreitasnutri.com.br/" target="_blank" rel="noopener" class="font-semibold text-brand-cream hover:text-brand-gold">
                            Site · helenfreitasnutri.com.br
                        </a>
                        <a href="tel:+5561992098384" class="font-semibold text-brand-cream hover:text-brand-gold">Telefone · (61) 99209-8384</a>
                    </div>
                    <ParticipationCta />
                </div>
            </article>
        </section>

        <section id="entrar" class="bg-brand-gold text-brand-ink">
            <div class="mx-auto flex max-w-[1200px] flex-col items-center gap-6 px-6 py-[clamp(72px,10vw,128px)] text-center">
                <span class="font-script text-[clamp(28px,3.4vw,40px)] leading-none">A gente cresce junto</span>
                <h2 class="display-title m-0 text-[clamp(72px,13vw,180px)] tracking-[-2px] leading-[0.82]">Quem topa?</h2>
                <p class="m-0 max-w-[560px] text-[clamp(18px,1.8vw,22px)] font-semibold leading-snug">
                    Bora ficar 30 dias firme. 9 de novembro a 8 de dezembro — sozinho ou com seus amigos.
                </p>
                <div class="flex flex-wrap justify-center gap-3 pt-2">
                    <Link
                        :href="route('participar')"
                        class="rounded-full bg-brand-ink px-[30px] py-[18px] text-[17px] font-bold text-brand-cream hover:bg-brand-line hover:text-brand-cream"
                    >
                        Participar
                    </Link>
                </div>
            </div>
        </section>

        <section class="mx-auto grid max-w-[1200px] items-start gap-12 px-6 py-[clamp(72px,9vw,120px)] lg:grid-cols-2">
            <div class="flex flex-col gap-6">
                <div class="eyebrow">Mais do que um desafio</div>
                <h2 class="display-title m-0 text-[clamp(44px,6vw,80px)]">Uma regra de vida.</h2>
                <p class="m-0 max-w-[480px] text-[19px] leading-relaxed text-brand-muted-3">
                    A gente quer viver uma virada de hábitos. Cada um no seu ritmo, respeitando o próprio nível e construindo hábitos que fazem diferença de
                    verdade.
                </p>
                <p class="m-0 max-w-[480px] text-[19px] leading-relaxed text-brand-muted-3">
                    São 30 dias para sair da intenção, colocar os planos em prática e descobrir do que a gente é capaz.
                </p>
                <p class="m-0 max-w-[480px] text-[19px] leading-relaxed text-brand-muted-3">
                    Porque o objetivo vai muito além do desafio: é levar essa evolução para a vida.
                </p>
            </div>
            <div class="relative flex items-center justify-center">
                <div
                    class="absolute aspect-square w-[78%] rounded-full bg-[radial-gradient(circle,rgba(246,168,33,0.22)_0%,rgba(246,168,33,0)_68%)]"
                    aria-hidden="true"
                />
                <img
                    :src="images.logo"
                    alt="Emblema do Desafio 30 dias · Virada, com os cinco ícones dos objetivos"
                    class="relative block h-auto w-full max-w-[580px]"
                />
            </div>
        </section>

        <footer class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-5 px-6 py-10">
            <div class="flex items-center gap-3">
                <img :src="images.logo" alt="" class="size-[52px] object-contain" />
                <div class="flex flex-col gap-0.5">
                    <span class="font-display text-xl font-extrabold italic leading-tight">DESAFIO DA VIRADA</span>
                    <span class="font-display text-xl font-extrabold italic leading-tight text-brand-gold">30 dias.</span>
                    <span class="mt-1 text-sm font-bold text-brand-muted-2">9 nov → 8 dez</span>
                </div>
            </div>
            <span class="text-sm font-semibold text-brand-muted-2">#DesafiodaVirada #30Dias #5Objetivos #AGenteCresceJunto</span>
        </footer>
    </div>
</template>
