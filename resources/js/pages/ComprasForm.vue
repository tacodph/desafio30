<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { handleAccordionClick } from '@/lib/accordionScroll';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    adminPreview?: boolean;
    weeks: { week: number; period: string }[];
    week: number;
    list: { items: string[]; extras: string[] } | null;
    catalog: { title: string; groups: { title: string; items: string[]; notes: string[] }[] }[];
}>();

const editing = props.list !== null;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Alimentação', href: '/meus-objetivos/alimentacao' },
    { title: 'Lista de compras', href: '/objetivos/alimentacao/compras' },
    { title: editing ? 'Editar lista' : 'Nova lista', href: '#' },
];

const shortPeriod = (period: string) => period.replace(/^(\d{2})\/(\d{2}) a (\d{2})\/\2$/, (_, from, _month, to) => Number(from) + ' a ' + Number(to));

const isBlank = (item: string) => item.includes('____');
const itemId = (section: string, group: string, index: number) => `${section}|${group}|${index}`;

const selected = new Set(props.list?.items ?? []);
const knownItems = new Set(props.catalog.flatMap((section) => section.groups.flatMap((group) => group.items)));
const savedItems = [...selected].filter((item) => !knownItems.has(item));
const catalog = savedItems.length
    ? [...props.catalog, { title: 'Itens salvos anteriormente', groups: [{ title: '', items: savedItems, notes: [] }] }]
    : props.catalog;
const checked = ref<Record<string, boolean>>({});
const extras = ref<Record<string, string>>({});
let blank = 0;

catalog.forEach((section) => {
    section.groups.forEach((group) => {
        group.items.forEach((item, index) => {
            const id = itemId(section.title, group.title, index);

            if (isBlank(item)) {
                extras.value[id] = props.list?.extras[blank++] ?? '';
            } else if (selected.has(item)) {
                checked.value[id] = true;
            }
        });
    });
});

const form = useForm({
    week: props.week,
    items: [] as string[],
    extras: [] as string[],
});

const save = () => {
    if (props.adminPreview) return;
    const items = new Set<string>();

    catalog.forEach((section) => {
        section.groups.forEach((group) => {
            group.items.forEach((item, index) => {
                if (!isBlank(item) && checked.value[itemId(section.title, group.title, index)]) {
                    items.add(item);
                }
            });
        });
    });

    form.items = [...items];
    form.extras = [...Object.values(extras.value), ...(props.list?.extras.slice(blank) ?? [])].map((item) => item.trim()).filter(Boolean);

    if (editing) {
        form.put(route('alimentacao.compras.update'));
    } else {
        form.post(route('alimentacao.compras.store'));
    }
};
</script>

<template>
    <Head :title="editing ? 'Editar lista de compras' : 'Nova lista de compras'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <form class="mx-auto flex w-full max-w-[1100px] flex-col gap-8 p-4 md:p-6" @submit.prevent="save" @click="handleAccordionClick">
            <p v-if="adminPreview" role="status" class="surface-card p-4 text-sm text-brand-gold">Visualização administrativa, somente para consulta.</p>
            <fieldset :disabled="adminPreview" class="m-0 grid min-w-0 gap-8 border-0 p-0">
            <div>
                <div class="text-sm font-bold uppercase tracking-[3px] text-[#43A047]">Lista de compras</div>
                <h1 class="mt-2 text-3xl font-black">Vamos às compras?</h1>
                <p class="mt-2 text-sm text-brand-cream/70">Marque apenas o que pretende comprar essa semana.</p>
            </div>

            <div class="surface-card flex flex-col gap-3 p-4 sm:p-6">
                <p v-if="editing" class="m-0 text-lg font-black">Semana {{ week }}, {{ shortPeriod(weeks[0].period) }}</p>
                <select
                    v-else
                    id="semana"
                    aria-label="Semana"
                    v-model.number="form.week"
                    class="h-11 rounded-xl border border-brand-line2 bg-brand-card2 px-3 text-brand-cream [color-scheme:dark]"
                >
                    <option v-for="item in weeks" :key="item.week" :value="item.week" class="bg-brand-card2 text-brand-cream">
                        Semana {{ item.week }}, {{ shortPeriod(item.period) }}
                    </option>
                </select>
                <p v-if="form.errors.week" class="m-0 text-sm text-red-300">{{ form.errors.week }}</p>
            </div>

            <details v-for="(section, sectionIndex) in catalog" :key="section.title" name="shopping-foods" :open="sectionIndex === 0" class="surface-card group/food overflow-hidden">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#43A047] sm:p-6 [&::-webkit-details-marker]:hidden">
                    <h2 class="text-xl font-black">{{ section.title }}</h2>
                    <ChevronDown class="size-5 shrink-0 text-[#43A047] transition-transform group-open/food:rotate-180" aria-hidden="true" />
                </summary>
                <div class="flex flex-col gap-6 border-t border-brand-line p-4 sm:p-6">
                <div
                    v-for="group in section.groups.filter((item) => item.items.length || item.notes.length)"
                    :key="section.title + group.title"
                    class="flex flex-col gap-3"
                >
                    <h3 v-if="group.title" class="text-sm font-bold uppercase tracking-[2px] text-[#43A047]">{{ group.title }}</h3>
                    <div class="grid gap-x-6 gap-y-1 sm:grid-cols-2 lg:grid-cols-3">
                        <label
                            v-for="(item, index) in group.items"
                            :key="itemId(section.title, group.title, index)"
                            class="flex items-start gap-3 rounded-lg px-2 py-2.5 text-[15px] sm:py-1.5"
                            :class="isBlank(item) ? 'sm:col-span-2 lg:col-span-3' : 'cursor-pointer hover:bg-brand-card2'"
                        >
                            <input v-if="!isBlank(item)" v-model="checked[itemId(section.title, group.title, index)]" type="checkbox" class="mt-0.5 size-5 shrink-0 accent-[#43A047] sm:mt-1 sm:size-4" />
                            <span v-if="!isBlank(item)" class="leading-snug">{{ item }}</span>
                            <input
                                v-else
                                v-model="extras[itemId(section.title, group.title, index)]"
                                type="text"
                                placeholder="Outro item"
                                class="w-full rounded-lg border border-brand-line3 bg-transparent px-3 py-1.5 text-brand-cream outline-none placeholder:text-brand-cream/40"
                            />
                        </label>
                    </div>
                    <p v-for="note in group.notes" :key="note" class="text-sm leading-relaxed text-brand-cream/70">{{ note }}</p>
                </div>
                </div>
            </details>

            <div class="sticky bottom-0 z-10 -mx-4 -mb-4 flex flex-wrap gap-3 border-t border-brand-line bg-background/95 px-4 py-3 backdrop-blur md:static md:mx-0 md:mb-0 md:border-0 md:bg-transparent md:p-0 md:backdrop-blur-none">
                <button type="submit" class="rounded-full bg-[#43A047] px-5 py-3 text-sm font-bold text-white disabled:opacity-60" :disabled="form.processing">
                    Gerar lista de compras
                </button>
                <Link :href="route('alimentacao.compras')" class="rounded-full border border-brand-line3 px-5 py-3 text-sm font-bold">Cancelar</Link>
            </div>
            </fieldset>
        </form>
    </AppLayout>
</template>
