<script setup lang="ts">
import GoalContent from '@/components/GoalContent.vue';
import { GOAL_DETAILS, type GoalKey } from '@/data/goalDetails';
import { images } from '@/data/images';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, Menu, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    goal: GoalKey;
    skillCatalog?: { title: string; items: string[] }[];
}>();

const menuOpen = ref(false);

const index = computed(() => Math.max(0, GOAL_DETAILS.findIndex((item) => item.key === props.goal)));
const item = computed(() => GOAL_DETAILS[index.value]);
const previous = computed(() => GOAL_DETAILS[(index.value + GOAL_DETAILS.length - 1) % GOAL_DETAILS.length]);
const next = computed(() => GOAL_DETAILS[(index.value + 1) % GOAL_DETAILS.length]);
const goalHref = (key: GoalKey) => (key === 'plus' ? route('objetivo.luxo') : route('objetivos.show', key));
</script>

<template>
    <Head :title="`Objetivos — ${GOAL_DETAILS.find((item) => item.key === goal)?.title ?? 'Regras'}`" />

    <div class="min-h-screen overflow-x-hidden bg-brand-ink text-brand-cream">
        <header class="sticky top-0 z-20 border-b border-brand-line bg-brand-ink/88 backdrop-blur-[10px]">
            <div class="mx-auto flex max-w-[1200px] items-center justify-between gap-4 px-6 py-3">
                <Link :href="route('home')" class="flex items-center gap-2.5 text-brand-cream hover:text-brand-cream">
                    <img :src="images.logoMark" alt="Desafio 30 Dias" class="size-11 object-contain" />
                    <span class="font-display text-[22px] font-extrabold italic uppercase tracking-wide">
                        Desafio <span class="text-brand-gold">30</span> Dias
                    </span>
                </Link>

                <nav class="hidden items-center justify-end gap-5 lg:flex">
                    <Link
                        v-for="item in GOAL_DETAILS"
                        :key="item.key"
                        :href="goalHref(item.key)"
                        class="text-[15px] font-medium hover:text-brand-cream"
                        :class="item.key === goal ? 'text-brand-cream' : 'text-brand-muted-3'"
                    >
                        {{ item.nav }}
                    </Link>
                    <Link
                        :href="route('dashboard')"
                        class="rounded-full bg-brand-gold px-[18px] py-2.5 text-[15px] font-bold text-brand-ink hover:bg-brand-gold-hover hover:text-brand-ink"
                    >
                        Voltar ao início
                    </Link>
                </nav>

                <button class="rounded-full border border-brand-line3 p-2 text-brand-cream lg:hidden" type="button" @click="menuOpen = !menuOpen">
                    <X v-if="menuOpen" class="size-5" />
                    <Menu v-else class="size-5" />
                </button>
            </div>

            <nav v-if="menuOpen" class="flex flex-col gap-3 border-t border-brand-line px-6 py-4 lg:hidden">
                <Link
                    v-for="item in GOAL_DETAILS"
                    :key="item.key"
                    :href="goalHref(item.key)"
                    class="text-[15px] font-medium text-brand-muted-3"
                    @click="menuOpen = false"
                >
                    {{ item.title }}
                </Link>
                <Link :href="route('dashboard')" class="text-[15px] font-medium text-brand-gold" @click="menuOpen = false"> Voltar ao início </Link>
            </nav>
        </header>

        <section class="mx-auto max-w-[1200px] px-6 pb-8 pt-[clamp(40px,6vw,72px)]">
            <Link :href="`${route('home')}#objetivos`" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-muted-3 hover:text-brand-cream">
                <ArrowLeft class="size-4" />
                5 objetivos
            </Link>
            <div class="mt-6 flex flex-col gap-3.5">
                <div class="eyebrow">Cada um no seu ritmo</div>
                <h1 class="display-title m-0 text-[clamp(44px,6vw,80px)]">A regra do objetivo</h1>
                <p class="m-0 max-w-[560px] text-[17px] leading-relaxed text-brand-muted">
                    Aqui está a regra, por que funciona e dicas práticas. Escolha outro objetivo abaixo.
                </p>
            </div>
            <div class="-mx-6 mt-6 flex gap-2 overflow-x-auto px-6 pb-1">
                <Link
                    v-for="goalItem in GOAL_DETAILS"
                    :key="goalItem.key"
                    :href="goalHref(goalItem.key)"
                    class="shrink-0 whitespace-nowrap rounded-full border px-4 py-2 text-sm font-bold"
                    :class="goalItem.key === goal ? 'border-transparent text-brand-ink' : 'border-brand-line3 text-brand-cream hover:border-brand-cream'"
                    :style="goalItem.key === goal ? { background: goalItem.color } : {}"
                >
                    {{ goalItem.nav }}
                </Link>
            </div>
        </section>

        <section :key="item.key" class="border-t border-brand-line bg-brand-ink">
            <div v-if="item.key === 'alimentacao'" class="mx-auto flex max-w-[1200px] flex-wrap gap-2 px-6 pt-8">
                <span class="rounded-full bg-[#43A047] px-4 py-2 text-sm font-bold text-white">A regra</span>
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('alimentacao.compras')"
                    class="rounded-full border border-brand-line3 px-4 py-2 text-sm font-bold text-brand-cream hover:border-brand-cream"
                >
                    Lista de compras
                </Link>
            </div>
            <div class="mx-auto max-w-[1200px] px-6 py-[clamp(56px,8vw,96px)]">
                <GoalContent :item="item" :skill-catalog="skillCatalog" />
            </div>

            <div class="mx-auto flex max-w-[1200px] flex-wrap items-center justify-between gap-3 px-6 pb-[clamp(40px,6vw,72px)]">
                <Link
                    :href="goalHref(previous.key)"
                    class="inline-flex items-center gap-2 rounded-full border border-brand-line3 px-5 py-3 text-[15px] font-bold text-brand-cream hover:border-brand-cream"
                >
                    <ArrowLeft class="size-4" />
                    {{ previous.nav }}
                </Link>
                <Link
                    :href="goalHref(next.key)"
                    class="inline-flex items-center gap-2 rounded-full border border-brand-line3 px-5 py-3 text-[15px] font-bold text-brand-cream hover:border-brand-cream"
                >
                    {{ next.nav }}
                    <ArrowRight class="size-4" />
                </Link>
            </div>
        </section>

        <section class="bg-brand-gold text-brand-ink">
            <div class="mx-auto flex max-w-[1200px] flex-col items-start gap-5 px-6 py-16 lg:flex-row lg:items-center lg:justify-between">
                <h2 class="display-title m-0 text-[clamp(36px,5vw,56px)]">Cinco objetivos.<br />Trinta dias.</h2>
                <Link
                    :href="`${route('home')}#entrar`"
                    class="rounded-full bg-brand-ink px-8 py-[18px] text-[17px] font-bold text-brand-cream hover:bg-brand-line hover:text-brand-cream"
                >
                    Quem topa?
                </Link>
            </div>
        </section>
    </div>
</template>
