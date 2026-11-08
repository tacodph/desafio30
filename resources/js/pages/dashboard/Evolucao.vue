<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import DayCard from '@/components/DayCard.vue';
import ProgressSummary from '@/components/ProgressSummary.vue';
import { type DayDetails, type DayGoals } from '@/data/dayLog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, onBeforeUnmount, ref, watch } from 'vue';

type Day = {
    day: number;
    date: string;
    weekday: string;
    week: number;
    checked_in: boolean;
    plus_count: number;
    details: DayDetails;
    workout_photo: string | null;
    meal_photos: { id: string; url: string }[];
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

const showAthleteControls = computed(() => props.canEdit || props.adminPreview);

const tabs: [Tab, string][] = [
    ['dias', 'Check in do dia'],
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
const needsWeekSkills = computed(() => showAthleteControls.value && savedWeekSkills.value.length === 0);
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

    weekSkillForm.picks = [idea];
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
        about: 'Sabe aquela superação que até você se surpreende? É aqui que ela merece ser celebrada. Vale qualquer esforço ou sacrifício que você conseguiu vencer, mesmo que pareça pequeno. Acordou cedo pra treinar? Foi treinar depois do trabalho mesmo cansado? Fez algo que achou que não daria conta? Registra aqui. Você merece reconhecer cada conquista.',
        form: useForm({ type: 'cantinho', detail: '' }),
    },
    {
        type: 'resistido',
        title: 'Resistido do dia',
        highlight: 'Aqui você brilha! ✨',
        fieldLabel: 'O que você conseguiu resistir hoje?',
        placeholder: 'Conta aqui: qual foi a tentação e como você lidou com ela?',
        about: "Sabe aquele ambiente que parece jogar contra você? O lanche que aparece no trabalho, o colega que sempre oferece um docinho, as tentações na casa da família ou alguém que pede iFood toda hora?\n\nQuando essas situações aparecerem e você conseguir manter sua escolha, anota aqui! Registre cada tentação que você enfrentou e cada vez que conseguiu seguir firme no que tinha decidido.",
        form: useForm({ type: 'resistido', detail: '' }),
    },
    {
        type: 'performance',
        title: 'Performance',
        highlight: 'Alô, ATLETA! 🏃💪',
        fieldLabel: 'Anota aqui!',
        placeholder: 'Qual foi sua evolução? Conta pra gente!',
        about: "Seu desempenho melhorou? Correu por mais tempo ou conseguiu correr mais rápido? Aumentou uma carga? Fez um esporte novo?\n\nVale qualquer evolução! Cada avanço, por menor que pareça, faz parte da construção do seu shape. Registra aqui e comemora essa conquista!",
        form: useForm({ type: 'performance', detail: '' }),
    },
];

const visibleDays = computed(() =>
    dias.days
        .map((entry) => ({ entry, info: props.days.find((source) => source.day === entry.day)! }))
        .filter((item) => item.info.week === week.value),
);

// Abre só o dia de hoje; se a semana não tem o dia de hoje, abre o primeiro dia dela.
const openDay = computed(() => {
    if (!needsWeekSkills.value && visibleDays.value.some((item) => item.entry.day === props.today)) return props.today;

    return visibleDays.value[0]?.entry.day;
});

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

const commentedAchievementId = ref<number | null>(null);
const restoreCommentPosition = async (event: Event) => {
    if (commentedAchievementId.value === null) return;
    event.preventDefault();
    const id = commentedAchievementId.value;
    commentedAchievementId.value = null;
    await nextTick();
    const achievement = document.getElementById(`achievement-${id}`);
    achievement?.focus({ preventScroll: true });
    achievement?.scrollIntoView({ block: 'center', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
};

const saveComment = () => {
    if (!commenting.value) {
        return;
    }

    const id = commenting.value.id;
    commentForm.put(route('athlete.achievements.comment', id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            localTab.value = 'conquistas';
            commentedAchievementId.value = id;
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
            <div v-if="tab !== 'dias' || !showAthleteControls" class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <div class="eyebrow">{{ canComment ? nutriName : 'Meu desafio' }}</div>
                    <h1 class="display-title text-[clamp(36px,5vw,56px)]">{{ athleteName ?? 'Evolução' }}</h1>
                    <p v-if="!canComment && nutritionistName" class="eyebrow m-0 mt-2">{{ nutritionistName }}</p>
                </div>
                <span class="font-display text-4xl font-black text-brand-gold">{{ total }} pts</span>
            </div>

            <div v-if="tab !== 'dias' || !showAthleteControls" class="flex flex-wrap gap-2">
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
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="item in weeks"
                        :key="item.week"
                        type="button"
                        class="rounded-full px-3 py-1.5 text-sm font-bold"
                        :class="week === item.week ? 'bg-brand-gold text-brand-ink' : 'border border-brand-line2 text-brand-muted'"
                        :aria-pressed="week === item.week"
                        @click="week = item.week"
                    >
                        Semana {{ item.week }}
                    </button>
                </div>
                <InputError :message="dias.errors.days" />
                <DayCard
                    v-for="(item, index) in visibleDays"
                    :key="item.entry.day"
                    :entry="item.entry"
                    :admin-preview="adminPreview"
                    :date="item.info.date"
                    :weekday="item.info.weekday"
                    :goals="goals"
                    :photo="item.info.workout_photo"
                    :meal-photos="item.info.meal_photos"
                    :meal-comment="item.info.meal_comment"
                    :saved="item.info.saved"
                    :workout-types="workoutTypes"
                    :week-skills="weeklySkills[item.info.week - 1] ?? []"
                    :default-open="item.entry.day === openDay"
                    :can-edit="canEdit && access.editableDays.includes(item.entry.day)"
                    :locked-reason="canEdit && !access.editableDays.includes(item.entry.day) ? 'Lançamentos indisponíveis para esta data.' : undefined"
                    :can-comment="canComment"
                    :athlete-id="athleteId"
                    :score-rules="scoreRules"
                >
                    <template v-if="showAthleteControls && index === 0" #before-check-in>
                        <fieldset :disabled="adminPreview" class="m-0 grid min-w-0 gap-3 rounded-xl border border-brand-line2 p-3">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="eyebrow">Escolha sua habilidade</div>
                                    <p class="m-0 mt-1 text-sm text-brand-muted">Escolha a habilidade da semana para praticar três vezes por semana.</p>
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
                                            @click="toggleWeekSkill(idea)"
                                        >
                                            {{ idea }}
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <Button type="button" class="w-fit" :disabled="weekSkillForm.processing || weekSkillForm.picks.length !== 1" @click="saveWeekSkills">
                                        Salvar habilidade da semana
                                    </Button>
                                    <button v-if="savedWeekSkills.length" type="button" class="text-sm font-semibold text-brand-muted" @click="editingWeekSkills = false; weekSkillForm.picks = [...savedWeekSkills]">
                                        Cancelar
                                    </button>
                                </div>
                                <InputError :message="weekSkillForm.errors.picks || weekSkillForm.errors['picks.0']" />
                            </div>
                        </fieldset>
                    </template>
                </DayCard>
            </div>

            <div v-else-if="tab === 'conquistas'" class="flex max-w-3xl flex-col gap-4">
                <section v-for="card in conquests" :key="card.type" class="surface-card grid gap-4 p-4 sm:p-5">
                    <div>
                        <h2 class="font-display m-0 text-2xl font-extrabold uppercase">{{ card.title }}</h2>
                        <p v-if="card.highlight" class="m-0 mt-2 font-bold text-brand-cream">{{ card.highlight }}</p>
                        <p class="m-0 mt-2 whitespace-pre-line text-sm leading-relaxed text-brand-muted">{{ card.about }}</p>
                    </div>
                    <form v-if="showAthleteControls" @submit.prevent="!adminPreview && saveConquista(card.form)">
                        <fieldset :disabled="adminPreview" class="m-0 grid min-w-0 gap-4 border-0 p-0">
                        <div class="grid gap-2">
                            <Label :for="`conquista-${card.type}`">{{ card.fieldLabel ?? 'Conquista' }}</Label>
                            <Input :id="`conquista-${card.type}`" v-model="card.form.detail" :placeholder="card.placeholder ?? 'O que aconteceu'" />
                            <InputError :message="card.form.errors.detail" />
                        </div>
                        <Button type="submit" class="w-fit" :disabled="adminPreview || card.form.processing">Adicionar</Button>
                        </fieldset>
                    </form>
                    <article
                        v-for="item in achievements.filter((entry) => entry.type === card.type)"
                        :key="item.id"
                        :id="`achievement-${item.id}`"
                        tabindex="-1"
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

            <ProgressSummary v-else :days="days" :weeks="weeks" />
        </div>

        <Dialog :open="commenting !== null" @update:open="(open) => !open && (commenting = null)">
            <DialogContent class="border-brand-line2 bg-brand-card text-brand-cream" @close-auto-focus="restoreCommentPosition">
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
