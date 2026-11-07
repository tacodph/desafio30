<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { shoppingList } from '@/data/shoppingList';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    weeks: { week: number; period: string }[];
    lists: { week: number; period: string; items: string[]; extras: string[] }[];
    canSave: boolean;
    adminPreview?: boolean;
    catalog: { title: string; groups: { title: string; items: string[]; notes: string[] }[] }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Lista de compras inteligente', href: '/objetivos/alimentacao/compras' },
];

const loggedIn = computed(() => Boolean(usePage<SharedData>().props.auth.user));

const sections = computed(() => (props.catalog.length ? props.catalog : shoppingList.sections.filter((section) => /^\d/.test(section.title))));
const showTips = ref(false);
const showAthleteControls = computed(() => props.canSave || props.adminPreview);
const canCreate = computed(() => props.lists.length < props.weeks.length);

const summaries = computed(() =>
    props.lists.map((list) => {
        const counts = sections.value
            .map((section) => {
                const names = new Set(section.groups.flatMap((group) => group.items));

                return { title: section.title.replace(/^\d+\.\s*/, ''), count: list.items.filter((item) => names.has(item)).length };
            })
            .filter((row) => row.count > 0);
        const known = counts.reduce((sum, row) => sum + row.count, 0);

        if (list.items.length > known) {
            counts.push({ title: 'Outros', count: list.items.length - known });
        }

        return { ...list, total: list.items.length + list.extras.length, counts, preview: [...list.items, ...list.extras].slice(0, 6) };
    }),
);
</script>

<template>
    <Head title="Lista de compras inteligente" />

    <component :is="loggedIn ? AppLayout : 'div'" v-bind="loggedIn ? { breadcrumbs } : { class: 'min-h-screen bg-brand-ink text-brand-cream' }">
        <header v-if="!loggedIn" class="sticky top-0 z-20 border-b border-brand-line bg-brand-ink/88 backdrop-blur-[10px]">
            <div class="mx-auto flex max-w-[900px] flex-wrap items-center gap-2 px-6 py-4">
                <Link :href="route('objetivos.show', 'alimentacao')" class="rounded-full border border-brand-line3 px-4 py-2 text-sm font-bold text-brand-cream hover:border-brand-cream">
                    A regra
                </Link>
                <span class="rounded-full bg-[#43A047] px-4 py-2 text-sm font-bold text-white">Lista de compras</span>
            </div>
        </header>

        <main class="mx-auto flex max-w-[900px] flex-col gap-8 px-6 py-10">
            <p v-if="adminPreview" role="status" class="rounded-xl border border-brand-gold/40 p-4 text-sm text-brand-gold">Visualização administrativa, somente para consulta. O calendário e os bloqueios dos atletas continuam valendo.</p>
            <div class="grid gap-3">
                <h1 class="text-3xl font-black text-brand-gold">Lista de compras da semana</h1>
                <h2 class="text-lg font-bold uppercase">Monte sua lista de compras</h2>
                <p class="text-sm text-brand-cream/70">Escolha os alimentos que pretende consumir nesta semana, selecionando os itens em cada categoria.</p>
                <p class="text-sm text-brand-cream/70">
                    Ao finalizar, clique em <strong>Gerar lista de compras</strong> e tenha tudo organizado automaticamente em uma única lista.
                </p>
            </div>

            <div class="-mt-4 flex flex-wrap items-center gap-3">
                <Link v-if="showAthleteControls && canCreate" :href="route('alimentacao.compras.create')" class="rounded-full bg-[#43A047] px-4 py-2 text-sm font-bold text-white">
                    Nova lista
                </Link>
                <button
                    type="button"
                    class="rounded-full bg-[#43A047] px-4 py-2 text-sm font-bold text-white"
                    :aria-expanded="showTips"
                    aria-controls="shopping-tips"
                    @click="showTips = !showTips"
                >
                    Dicas para uma compra funcional
                </button>
            </div>
            <div v-show="showTips" id="shopping-tips" class="surface-card space-y-3 p-4 sm:p-5">
                <ul class="flex flex-col gap-2 text-[15px] leading-relaxed">
                    <li v-for="tip in shoppingList.tips" :key="tip">{{ tip }}</li>
                </ul>
                <p v-for="line in shoppingList.closing" :key="line" class="text-sm leading-relaxed text-brand-cream/70">{{ line }}</p>
            </div>

            <section v-if="showAthleteControls" class="flex flex-col gap-4">
                <h2 class="m-0 text-xl font-black">Aqui vão aparecer as listas criadas</h2>
                <p v-if="lists.length === 0" class="m-0 text-sm text-brand-cream/70">Nenhuma lista ainda. Escolha a semana e marque o que vai comprar.</p>
                <p v-else-if="!canCreate" class="m-0 text-sm text-brand-cream/70">Todas as semanas já têm lista. Edite uma delas se precisar mudar.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <article v-for="list in summaries" :key="list.week" class="surface-card flex flex-col gap-4 p-5" style="border-top: 4px solid #43A047">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="m-0 text-lg font-black">Semana {{ list.week }}</h3>
                                <span class="text-sm text-brand-cream/70">{{ list.period }}</span>
                            </div>
                            <div class="text-right">
                                <span class="block text-4xl font-black leading-none tabular-nums text-[#43A047]">{{ list.total }}</span>
                                <span class="text-xs uppercase tracking-[2px] text-brand-cream/70">{{ list.total === 1 ? 'item' : 'itens' }}</span>
                            </div>
                        </div>
                        <ul class="m-0 flex list-none flex-col gap-1 p-0 text-sm">
                            <li v-for="row in list.counts" :key="row.title" class="flex justify-between gap-3">
                                <span>{{ row.title }}</span>
                                <span class="font-bold tabular-nums">{{ row.count }}</span>
                            </li>
                            <li v-if="list.extras.length" class="flex justify-between gap-3">
                                <span>Itens extras</span>
                                <span class="font-bold tabular-nums">{{ list.extras.length }}</span>
                            </li>
                        </ul>
                        <p class="m-0 text-sm leading-relaxed text-brand-cream/70">
                            {{ list.preview.join(', ') }}<span v-if="list.total > list.preview.length"> e mais {{ list.total - list.preview.length }}</span>
                        </p>
                        <Link :href="route('alimentacao.compras.edit', list.week)" class="w-fit text-sm font-bold text-[#43A047]">Editar lista</Link>
                    </article>
                </div>
            </section>

            <template v-if="!showAthleteControls">
                <section v-for="section in sections" :key="section.title" class="surface-card flex flex-col gap-6 p-6">
                    <h2 class="text-xl font-black">{{ section.title }}</h2>
                    <div
                        v-for="group in section.groups.filter((item) => item.items.length || item.notes.length)"
                        :key="section.title + group.title"
                        class="flex flex-col gap-3"
                    >
                        <h3 v-if="group.title" class="text-sm font-bold uppercase tracking-[2px] text-[#43A047]">{{ group.title }}</h3>
                        <ul class="m-0 flex list-none flex-col gap-2 p-0 text-[15px]">
                            <li v-for="item in group.items.filter((name) => !name.includes('____'))" :key="item">{{ item }}</li>
                        </ul>
                        <p v-for="note in group.notes" :key="note" class="text-sm leading-relaxed text-brand-cream/70">{{ note }}</p>
                    </div>
                </section>
            </template>

        </main>
    </component>
</template>
