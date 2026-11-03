<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import DayCard from '@/components/DayCard.vue';
import { type DayDetails, type DayGoals } from '@/data/dayLog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue';

type Day = {
    day: number;
    date: string;
    weekday: string;
    week: number;
    checked_in: boolean;
    plus_count: number;
    details: DayDetails;
    workout_photo: string | null;
    meal_photo: string | null;
    meal_comment: string;
    saved: boolean;
    notes: Record<string, string>;
    points: number;
};

type Week = {
    week: number;
    period: string;
    checkins: number;
    daily: number;
    strength: number;
    skill: number;
    plus: number;
    plus_bonus: number;
    consistency: number;
    goals_bonus: number;
    achievements: number;
    total: number;
    athletes_done: number;
    athletes_total: number;
};

const props = defineProps<{
    access: { releaseDate: string; editableDays: number[]; pages: Record<string, boolean>; entryMode: string };
    tab: 'dias' | 'conquistas' | 'resumo' | null;
    currentWeek: number;
    today: number;
    goals: DayGoals;
    workoutTypes: string[];
    weeklySkills: string[][];
    skillCatalog: { title: string; items: string[] }[];
    days: Day[];
    weeks: Week[];
    achievements: { id: number; achieved_on: string; type: string; label: string; points: number; detail: string; emotion: string; comment: string; nutritionist: string }[];
    types: { type: string; label: string; points: number }[];
    total: number;
    adminPreview?: boolean;
    canEdit: boolean;
    canComment: boolean;
    athleteId: number | null;
    athleteName: string | null;
    nutritionistName: string | null;
    scoreRules?: Record<string, number>;
}>();

type Tab = 'dias' | 'conquistas' | 'resumo';

const tabs: [Tab, string][] = [
    ['dias', 'Dias'],
    ['conquistas', 'Conquistas'],
    ['resumo', 'Resumo'],
];

// O atleta navega por páginas (/atleta/evolucao/{aba}); a nutricionista alterna as abas na mesma tela.
const localTab = ref<Tab>(props.tab ?? 'dias');
watch(() => props.tab, (value) => { if (props.adminPreview && value) localTab.value = value; });
const tab = computed<Tab>(() => (props.canEdit ? (props.tab ?? 'dias') : localTab.value));

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Painel', href: '/dashboard' },
    { title: 'Evolução', href: '#' },
    { title: tabs.find((item) => item[0] === tab.value)?.[1] ?? 'Dias', href: '#' },
]);
const week = ref(props.currentWeek);
const savedWeekSkills = computed(() => (props.weeklySkills[week.value - 1] ?? []).filter(Boolean));
const needsWeekSkills = computed(() => props.canEdit && savedWeekSkills.value.length === 0);
const editingWeekSkills = ref(needsWeekSkills.value);
const weekSkillForm = useForm({
    week: week.value,
    picks: [...savedWeekSkills.value] as string[],
});

watch(week, (value) => {
    weekSkillForm.week = value;
    weekSkillForm.picks = [...(props.weeklySkills[value - 1] ?? [])].filter(Boolean);
    editingWeekSkills.value = props.canEdit && weekSkillForm.picks.length === 0;
});

const toggleWeekSkill = (idea: string) => {
    const index = weekSkillForm.picks.indexOf(idea);

    if (index >= 0) {
        weekSkillForm.picks.splice(index, 1);

        return;
    }

    if (weekSkillForm.picks.length < 3) {
        weekSkillForm.picks.push(idea);
    }
};

const saveWeekSkills = () =>
    weekSkillForm.put(route('athlete.skills.update'), {
        preserveScroll: true,
        onSuccess: () => {
            editingWeekSkills.value = false;
        },
    });

const dias = useForm({
    days: props.days.map((day) => ({
        day: day.day,
        checked_in: day.checked_in,
        plus_count: day.plus_count,
        notes: { ...day.notes },
        details: JSON.parse(JSON.stringify(day.details)) as DayDetails,
    })),
});
let accessTimer: ReturnType<typeof setInterval> | undefined;
onMounted(() => {
    if (props.canEdit) accessTimer = setInterval(() => router.reload({ only: ['access', 'today', 'currentWeek'] }), 60000);
});
onBeforeUnmount(() => { if (accessTimer) clearInterval(accessTimer); });
const conquests = [
    {
        type: 'cantinho',
        title: 'Cantinho da vitória',
        about: 'Uma superação que você não previa — diferente de uma habilidade nova. Algo que saiu do seu padrão e te pegou de surpresa.\nEx.: nunca acorda cedo para treinar, mas nesse dia acordou e foi treinar para bater a meta.',
        form: useForm({ type: 'cantinho', detail: '' }),
    },
    {
        type: 'resistido',
        title: 'Resistido do dia',
        about: 'Quais situações você conseguiu usar o auto controle e seguiu firme resistindo a tentações do dia.\nEx: bolo no trabalho;\nBatata frita no self service.\nSobremesa.',
        form: useForm({ type: 'resistido', detail: '' }),
    },
    {
        type: 'performance',
        title: 'Performance',
        about: 'Quando seu desempenho melhorou em relação ao treino anterior: mais carga, mais repetições, mais distância, mais tempo ou menos descanso. Ex.: aumentou a carga, correu mais de 10 minutos, bateu um novo recorde pessoal, aumentou a carga de um exercício, correu mais de 10 minutos sem parar, fez mais repetições do que na última vez, aumentou a carga no agachamento, correu por mais de 10 minutos, completou mais repetições que antes, aumentei a carga do supino, corri por mais de 10 minutos.',
        form: useForm({ type: 'performance', detail: '' }),
    },
];

const visibleDays = computed(() =>
    dias.days
        .map((entry) => ({ entry, info: props.days.find((source) => source.day === entry.day)! }))
        .filter((item) => item.info.week === week.value),
);

// Abre só o dia de hoje; se a semana não tem o dia de hoje, abre o primeiro dia dela.
const openDay = computed(() => (visibleDays.value.some((item) => item.entry.day === props.today) ? props.today : visibleDays.value[0]?.entry.day));

const saveConquista = (form: (typeof conquests)[number]['form']) =>
    form.post(route('athlete.achievements.store'), {
        onSuccess: () => form.reset('detail'),
    });

const nutriName = usePage<SharedData>().props.auth.user.name;
const emotions = ['👏', '🔥', '💪', '❤️', '⭐', '😍'];
const editing = ref<number | null>(null);
const commenting = ref<(typeof props.achievements)[number] | null>(null);
const editForm = useForm({ detail: '' });
const commentForm = useForm({ comment: '', emotion: '' });

const formatDate = (value: string) => {
    const [year, month, day] = value.split('-');

    return `${day}/${month}/${year}`;
};

const startEdit = (item: (typeof props.achievements)[number]) => {
    editing.value = item.id;
    editForm.detail = item.detail;
    editForm.clearErrors();
};

const saveEdit = (id: number) => {
    editForm.put(route('athlete.achievements.update', id), {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = null;
        },
    });
};

const openComment = (item: (typeof props.achievements)[number]) => {
    commenting.value = item;
    commentForm.comment = item.comment;
    commentForm.emotion = item.emotion;
    commentForm.clearErrors();
};

const daysInWeek = (week: number) => props.days.filter((day) => day.week === week).length;
const needDays = (week: number) => Math.min(6, daysInWeek(week));
const weekClosed = (item: Week) => item.checkins >= needDays(item.week);
const missingDays = (item: Week) => Math.max(0, needDays(item.week) - item.checkins);
const weekParts = [
    { key: 'daily', label: 'Diários' },
    { key: 'plus_bonus', label: 'Refeição luxo' },
    { key: 'consistency', label: 'Consistência' },
    { key: 'goals_bonus', label: 'Metas' },
    { key: 'achievements', label: 'Conquistas' },
] as const;

const saveComment = () => {
    if (!commenting.value) {
        return;
    }

    commentForm.put(route('athlete.achievements.comment', commenting.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            commenting.value = null;
        },
    });
};
</script>

<template>
    <Head title="Evolução" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <p v-if="adminPreview" role="status" class="surface-card m-0 border-brand-gold/40 p-4 text-sm leading-relaxed text-brand-gold">Visualização administrativa, somente para consulta. Você pode revisar as páginas fora da data, sem liberar atletas nem registrar dados reais.</p>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="eyebrow">{{ canComment ? nutriName : 'Meu desafio' }}</div>
                    <h1 class="display-title text-[clamp(36px,5vw,56px)]">{{ athleteName ?? 'Evolução' }}</h1>
                    <p v-if="!canComment && nutritionistName" class="eyebrow m-0 mt-2">{{ nutritionistName }}</p>
                </div>
                <span class="font-display text-4xl font-black text-brand-gold">{{ total }} pts</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <template v-for="item in tabs" :key="item[0]">
                    <Link
                        v-if="canEdit && access.pages[item[0]]"
                        :href="route('athlete.progress.tab', item[0])"
                        class="rounded-full px-4 py-2 text-sm font-bold disabled:cursor-not-allowed disabled:opacity-40"
                        :class="tab === item[0] ? 'bg-brand-gold text-brand-ink' : 'border border-brand-line2 text-brand-muted'"
                    >
                        {{ item[1] }}
                    </Link>
                    <button
                        v-else
                        :disabled="canEdit && !access.pages[item[0]]"
                        type="button"
                        class="rounded-full px-4 py-2 text-sm font-bold"
                        :class="tab === item[0] ? 'bg-brand-gold text-brand-ink' : 'border border-brand-line2 text-brand-muted'"
                        @click="localTab = item[0]"
                    >
                        {{ item[1] }}
                    </button>
                </template>
            </div>

            <div v-if="tab === 'dias'" class="flex flex-col gap-4">
                <p v-if="canEdit" role="status" class="surface-card m-0 p-4 text-sm leading-relaxed text-brand-muted">
                    {{ access.entryMode === 'closed' ? 'Os lançamentos estão temporariamente bloqueados pela administração.' : 'Cada dia libera seus lançamentos e fotos apenas na data correspondente, de 00h até 23h59, pelo horário de Brasília. Os demais dias ficam disponíveis para consulta.' }}
                </p>
                <InputError :message="dias.errors.days" />
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="item in weeks"
                        :key="item.week"
                        type="button"
                        class="rounded-full px-3 py-1.5 text-sm font-bold"
                        :class="week === item.week ? 'bg-brand-gold text-brand-ink' : 'border border-brand-line2 text-brand-muted'"
                        @click="week = item.week"
                    >
                        Semana {{ item.week }}
                    </button>
                </div>
                <section v-if="canEdit" class="surface-card grid gap-3 p-4 sm:p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="eyebrow">Habilidades da semana {{ week }}</div>
                            <p class="m-0 mt-1 text-sm text-brand-muted">Escolha 3 ideias para praticar nesta semana. Sem isso, os dias ficam travados.</p>
                        </div>
                        <button v-if="savedWeekSkills.length && !editingWeekSkills" type="button" class="text-sm font-semibold text-brand-gold" @click="editingWeekSkills = true">
                            Alterar
                        </button>
                    </div>
                    <div v-if="!editingWeekSkills && savedWeekSkills.length" class="grid gap-1.5">
                        <p v-for="idea in savedWeekSkills" :key="idea" class="m-0 text-sm">{{ idea }}</p>
                    </div>
                    <div v-else class="grid gap-3">
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-for="idea in weekSkillForm.picks"
                                :key="idea"
                                type="button"
                                class="rounded-full border border-brand-gold bg-brand-gold px-3 py-1.5 text-left text-xs font-bold text-brand-ink"
                                @click="toggleWeekSkill(idea)"
                            >
                                {{ idea }} ×
                            </button>
                            <span v-if="weekSkillForm.picks.length < 3" class="self-center text-xs text-brand-muted">Faltam {{ 3 - weekSkillForm.picks.length }}</span>
                        </div>
                        <div class="grid max-h-72 gap-3 overflow-y-auto">
                            <div v-for="group in skillCatalog" :key="group.title" class="grid gap-1">
                                <p class="m-0 text-[11px] font-bold uppercase tracking-widest text-brand-gold">{{ group.title }}</p>
                                <button
                                    v-for="idea in group.items"
                                    :key="idea"
                                    type="button"
                                    class="rounded-lg px-2 py-2 text-left text-sm leading-snug disabled:opacity-40"
                                    :class="weekSkillForm.picks.includes(idea) ? 'bg-brand-gold text-brand-ink' : 'text-brand-cream hover:bg-brand-line2'"
                                    :disabled="weekSkillForm.picks.length >= 3 && !weekSkillForm.picks.includes(idea)"
                                    @click="toggleWeekSkill(idea)"
                                >
                                    {{ idea }}
                                </button>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <Button type="button" class="w-fit" :disabled="weekSkillForm.processing || weekSkillForm.picks.length !== 3" @click="saveWeekSkills">
                                Salvar habilidades da semana
                            </Button>
                            <button v-if="savedWeekSkills.length" type="button" class="text-sm font-semibold text-brand-muted" @click="editingWeekSkills = false; weekSkillForm.picks = [...savedWeekSkills]">
                                Cancelar
                            </button>
                        </div>
                        <InputError :message="weekSkillForm.errors.picks || weekSkillForm.errors['picks.0']" />
                    </div>
                </section>
                <p v-if="needsWeekSkills" class="m-0 text-sm text-brand-muted">Escolha as 3 habilidades da semana para lançar os dias.</p>
                <DayCard
                    v-for="item in visibleDays"
                    :key="item.entry.day"
                    :entry="item.entry"
                    :date="item.info.date"
                    :weekday="item.info.weekday"
                    :goals="goals"
                    :photo="item.info.workout_photo"
                    :meal-photo="item.info.meal_photo"
                    :meal-comment="item.info.meal_comment"
                    :saved="item.info.saved"
                    :workout-types="workoutTypes"
                    :week-skills="weeklySkills[item.info.week - 1] ?? []"
                    :default-open="item.entry.day === openDay"
                    :can-edit="canEdit && !needsWeekSkills && access.editableDays.includes(item.entry.day)"
                    :locked-reason="canEdit && !access.editableDays.includes(item.entry.day) ? 'Lançamentos indisponíveis para esta data.' : needsWeekSkills ? 'Escolha as habilidades da semana para lançar.' : undefined"
                    :can-comment="canComment"
                    :athlete-id="athleteId"
                    :score-rules="scoreRules"
                />
            </div>

            <div v-else-if="tab === 'conquistas'" class="flex max-w-3xl flex-col gap-4">
                <section v-for="card in conquests" :key="card.type" class="surface-card grid gap-4 p-4 sm:p-5">
                    <div>
                        <h2 class="font-display m-0 text-2xl font-extrabold uppercase">{{ card.title }}</h2>
                        <p class="m-0 mt-2 whitespace-pre-line text-sm leading-relaxed text-brand-muted">{{ card.about }}</p>
                    </div>
                    <form v-if="canEdit" class="grid gap-4" @submit.prevent="saveConquista(card.form)">
                        <div class="grid gap-2">
                            <Label :for="`conquista-${card.type}`">Conquista</Label>
                            <Input :id="`conquista-${card.type}`" v-model="card.form.detail" placeholder="O que aconteceu" />
                            <InputError :message="card.form.errors.detail" />
                        </div>
                        <Button type="submit" class="w-fit" :disabled="card.form.processing">Adicionar</Button>
                    </form>
                    <article
                        v-for="item in achievements.filter((entry) => entry.type === card.type)"
                        :key="item.id"
                        class="grid gap-2 border-t border-brand-line2 pt-3"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="grid min-w-0 flex-1 gap-1">
                                <p class="m-0 text-xs font-semibold uppercase tracking-[1px] text-brand-muted">{{ formatDate(item.achieved_on) }}</p>
                                <form v-if="editing === item.id" class="grid gap-2" @submit.prevent="saveEdit(item.id)">
                                    <Input v-model="editForm.detail" />
                                    <InputError :message="editForm.errors.detail" />
                                    <Button type="submit" class="w-fit" :disabled="editForm.processing">Salvar</Button>
                                </form>
                                <p v-else class="m-0 text-brand-cream">{{ item.detail }}</p>
                            </div>
                            <div class="flex shrink-0 flex-wrap justify-end gap-x-1 gap-y-0">
                                <button v-if="canEdit && editing !== item.id" type="button" class="px-2 py-1.5 text-sm font-semibold text-brand-gold" @click="startEdit(item)">
                                    Editar
                                </button>
                                <button v-if="canComment" type="button" class="px-2 py-1.5 text-sm font-semibold text-brand-gold" @click="openComment(item)">
                                    Comentar
                                </button>
                                <button
                                    v-if="canEdit"
                                    type="button"
                                    class="px-2 py-1.5 text-sm font-semibold text-brand-muted"
                                    @click="router.delete(route('athlete.achievements.destroy', item.id))"
                                >
                                    Apagar
                                </button>
                            </div>
                        </div>
                        <p v-if="item.comment" class="m-0 text-sm text-brand-muted">
                            <span v-if="item.nutritionist || nutritionistName" class="font-semibold text-brand-cream">{{ item.nutritionist || nutritionistName }} · </span>{{ item.emotion }} {{ item.comment }}
                        </p>
                    </article>
                </section>
            </div>

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <article v-for="item in weeks" :key="item.week" class="surface-card flex flex-col gap-4 p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="eyebrow">Semana {{ item.week }}</div>
                            <p class="m-0 mt-1 text-sm text-brand-muted">{{ item.period }}</p>
                        </div>
                        <div class="text-right">
                            <div class="font-display text-4xl font-black leading-none text-brand-gold">{{ item.total }}</div>
                            <div class="mt-1 text-[11px] font-bold uppercase tracking-[2px] text-brand-muted-2">pts</div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-semibold text-brand-cream">{{ item.checkins }} de {{ daysInWeek(item.week) }} dias</span>
                            <span
                                class="rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider"
                                :class="weekClosed(item) ? 'bg-brand-gold text-brand-ink' : 'border border-brand-line2 text-brand-muted'"
                            >
                                {{ weekClosed(item) ? 'Concluiu' : missingDays(item) === 1 ? 'Falta 1 dia' : `Faltam ${missingDays(item)} dias` }}
                            </span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-brand-line2">
                            <div
                                class="h-full rounded-full bg-brand-gold"
                                :style="{ width: `${Math.min(100, (item.checkins / daysInWeek(item.week)) * 100)}%` }"
                            />
                        </div>
                    </div>

                    <p class="m-0 rounded-xl border border-brand-line2 bg-brand-card2 px-3 py-2.5 text-sm">
                        <span class="font-display text-2xl font-black text-brand-gold">{{ item.athletes_done }}</span>
                        <span class="text-brand-muted">
                            de {{ item.athletes_total }} {{ item.athletes_total === 1 ? 'atleta concluiu' : 'atletas concluíram' }} a semana
                        </span>
                    </p>

                    <dl class="grid grid-cols-2 gap-x-3 gap-y-2">
                        <div v-for="part in weekParts" :key="part.key" class="flex items-baseline justify-between gap-2">
                            <dt class="text-xs text-brand-muted-2">{{ part.label }}</dt>
                            <dd class="m-0 text-sm font-bold tabular-nums">{{ item[part.key] }}</dd>
                        </div>
                    </dl>
                </article>
            </div>
        </div>

        <Dialog :open="commenting !== null" @update:open="(open) => !open && (commenting = null)">
            <DialogContent class="border-brand-line2 bg-brand-card text-brand-cream">
                <DialogHeader>
                    <DialogTitle>Comentário de {{ nutriName }}</DialogTitle>
                    <DialogDescription class="text-brand-muted">{{ commenting?.detail }}</DialogDescription>
                </DialogHeader>
                <div class="grid gap-3">
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="emoji in emotions"
                            :key="emoji"
                            type="button"
                            class="rounded-full border px-3 py-1 text-xl"
                            :class="commentForm.emotion === emoji ? 'border-brand-gold' : 'border-brand-line2'"
                            @click="commentForm.emotion = emoji"
                        >
                            {{ emoji }}
                        </button>
                    </div>
                    <InputError :message="commentForm.errors.emotion" />
                    <textarea
                        v-model="commentForm.comment"
                        rows="4"
                        placeholder="Comentário"
                        class="w-full rounded-xl border border-brand-line2 bg-brand-card2 px-3 py-2 text-sm text-brand-cream placeholder:text-brand-muted-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold"
                    />
                    <InputError :message="commentForm.errors.comment" />
                </div>
                <DialogFooter>
                    <Button type="button" :disabled="commentForm.processing" @click="saveComment">Salvar</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
