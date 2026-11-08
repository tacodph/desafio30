<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { achieved, dayPoints, workoutMinutes, type Achieved, type DayDetails, type DayGoals } from '@/data/dayLog';
import { router, useForm } from '@inertiajs/vue3';
import { Camera, ChevronDown, Droplets, Dumbbell, Lightbulb, Moon, Plus, Salad, Star, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

type DayForm = {
    day: number;
    checked_in: boolean;
    plus_count: number;
    notes: Record<string, string>;
    details: DayDetails;
};

const props = withDefaults(
    defineProps<{
        entry: DayForm;
        date: string;
        weekday: string;
        goals: DayGoals;
        photo?: string | null;
        mealPhotos?: { id: string; url: string }[];
        mealComment?: string | null;
        saved: boolean;
        workoutTypes: string[];
        weekSkills: string[];
        defaultOpen?: boolean;
        canEdit?: boolean;
        adminPreview?: boolean;
        lockedReason?: string;
        scoreRules?: Record<string, number>;
        canComment?: boolean;
        athleteId?: number | null;
    }>(),
    { canEdit: true, canComment: false },
);

const weekdayLabels: Record<string, string> = { dom: 'domingo', seg: 'segunda', ter: 'terça', qua: 'quarta', qui: 'quinta', sex: 'sexta', sáb: 'sábado' };

const open = ref(props.defaultOpen ?? false);

type GoalKey = keyof Achieved | 'plus' | 'notes';

const showAthleteControls = computed(() => props.canEdit || props.adminPreview);
const editing = computed(() => Boolean(props.canEdit || props.adminPreview));
const saving = ref<GoalKey | null>(null);
const saveError = ref('');
const saveMessage = ref('');

const goalLabels: Record<GoalKey, string> = {
    movement: 'Treino', water: 'Água', sleep: 'Sono', strength: 'Alimentação',
    skill: 'Habilidade', plus: 'Refeição luxo', notes: 'Observação',
};

const saveGoal = (goal: GoalKey) => {
    if (!props.canEdit || props.adminPreview || saving.value) return;

    saveError.value = '';
    saveMessage.value = '';
    const current = achieved(props.entry.details, props.goals);
    router.patch(
        route('athlete.days.goal.update', { day: props.entry.day, goal }),
        {
            details: JSON.parse(JSON.stringify(props.entry.details)),
            plus_count: props.entry.plus_count,
            notes: { ...props.entry.notes },
        },
        {
            preserveState: true,
            preserveScroll: true,
            onStart: () => { saving.value = goal; },
            onSuccess: () => {
                if (goal !== 'notes') props.entry.checked_in = true;
                saveMessage.value = 'Registro de ' + goalLabels[goal].toLocaleLowerCase('pt-BR') + ' salvo.';

                if (goal === 'plus' || goal === 'notes') return;

                const newlyAchieved = current[goal] && !cheerFrom.value[goal];
                cheerFrom.value[goal] = current[goal];

                if (newlyAchieved) {
                    cheers.value = [GOAL_CHEERS[goal]];
                    burst();
                    cheersOpen.value = true;
                }
            },
            onError: (errors) => { saveError.value = Object.values(errors)[0] ?? 'Não foi possível salvar o objetivo.'; },
            onFinish: () => { saving.value = null; },
        },
    );
};

const GOAL_CHEERS: Record<keyof Achieved, { title: string; line: string }> = {
    movement: { title: 'Treino batido!', line: 'O sofá que lute. Corpo em movimento, ponto na conta.' },
    water: { title: 'Água na meta!', line: 'Hidratação de campeão. A célula agradece o gole.' },
    sleep: { title: 'Sono cumprido!', line: 'Dormiu o combinado. Amanhã o treino vai sentir a diferença.' },
    strength: { title: 'Alimentação firme!', line: 'Prato alinhado, sem drama e com sabor.' },
    skill: { title: 'Habilidade na conta!', line: 'Praticou a ideia da semana. Autonomia subindo.' },
};

const cheersOpen = ref(false);
const cheers = ref<(typeof GOAL_CHEERS)[keyof Achieved][]>([]);
const confetti = ref<{ id: number; style: Record<string, string> }[]>([]);

const burst = () => {
    confetti.value = Array.from({ length: 56 }, (_, id) => ({
        id,
        style: {
            left: `${Math.random() * 100}%`,
            width: `${6 + Math.random() * 7}px`,
            height: `${8 + Math.random() * 10}px`,
            background: ['#e8b923', '#f5f1ea', '#e85d4c', '#43A047'][id % 4],
            animationDelay: `${Math.random() * 0.45}s`,
            animationDuration: `${2.1 + Math.random()}s`,
            '--drift': `${Math.random() * 120 - 60}px`,
        },
    }));
};

const done = computed(() => achieved(props.entry.details, props.goals));
const cheerFrom = ref({ ...done.value });
const points = computed(() => dayPoints(props.entry.checked_in, props.entry.details, props.goals));
const minutes = computed(() => workoutMinutes(props.entry.details));
const luxuryDay = computed(() => ['sex', 'sáb', 'dom'].includes(props.weekday));

const pct = (value: number, goal: number) => `${Math.min(100, Math.round((value / goal) * 100))}%`;

const liters = computed({
    get: () => props.entry.details.water_ml / 1000,
    set: (value: number | string) => {
        props.entry.details.water_ml = Math.max(0, Math.round((Number(value) || 0) * 1000));
    },
});

// "Meta atingida": ao marcar, preenche o campo com a meta; ao desmarcar, devolve o que estava antes.
const before: Partial<Record<keyof DayDetails['manual'], string>> = {};

const toggleGoal = (key: 'movement' | 'water' | 'sleep' | 'strength', checked: boolean) => {
    const details = props.entry.details;
    const field = { movement: 'workouts', water: 'water_ml', sleep: 'sleep_hours', strength: 'food' }[key] as 'workouts' | 'water_ml' | 'sleep_hours' | 'food';

    details.manual[key] = checked;

    if (!checked) {
        if (before[key] !== undefined) {
            (details as Record<string, unknown>)[field] = JSON.parse(before[key] as string);
            delete before[key];
        }

        return;
    }

    before[key] = JSON.stringify(details[field]);

    if (key === 'water') {
        details.water_ml = Math.max(details.water_ml, props.goals.water_ml);
    } else if (key === 'sleep') {
        details.sleep_hours = Math.max(Number(details.sleep_hours) || 0, props.goals.sleep_hours);
    } else if (key === 'strength') {
        details.food.no_alcohol = details.food.no_fast_food = details.food.no_sweet = true;
    } else if (!details.workouts.length) {
        details.workouts.push({ type: props.workoutTypes[0], minutes: props.goals.movement_minutes });
    } else {
        addMinutes(details.workouts.length - 1, Math.max(0, props.goals.movement_minutes - minutes.value));
    }
};

// Foto do treino: envia na hora (sem depender do botão "Salvar dias"); preserveState mantém o que já foi digitado.
const photoInput = ref<HTMLInputElement | null>(null);
const uploading = ref(false);
const photoError = ref('');

const sendPhoto = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    photoError.value = '';
    router.post(
        route('athlete.days.photo.store', props.entry.day),
        { photo: file },
        {
            forceFormData: true,
            preserveState: true,
            preserveScroll: true,
            onStart: () => (uploading.value = true),
            onError: (errors) => (photoError.value = Object.values(errors)[0] ?? 'Não foi possível enviar a foto.'),
            onFinish: () => {
                uploading.value = false;
                input.value = '';
            },
        },
    );
};

const removePhoto = () =>
    router.delete(route('athlete.days.photo.destroy', props.entry.day), { preserveState: true, preserveScroll: true });

const mealInput = ref<HTMLInputElement | null>(null);
const mealUploading = ref(false);
const mealError = ref('');
const mealNote = useForm({ comment: props.mealComment ?? '' });

watch(
    () => props.mealComment,
    (value) => {
        mealNote.comment = value ?? '';
    },
);

const sendMealPhotos = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const files = Array.from(input.files ?? []);
    input.value = '';
    if (!files.length || mealUploading.value) return;

    mealError.value = '';
    if (files.some((file) => file.size > 5 * 1024 * 1024)) {
        mealError.value = 'Cada foto deve ter até 5 MB.';
        return;
    }

    mealUploading.value = true;
    try {
        for (const [index, file] of files.entries()) {
            // Envia em sequência para evitar o limite de tamanho de uma requisição.
            const uploaded = await new Promise<boolean>((resolve) => {
                let succeeded = false;
                router.post(route('athlete.days.meal.store', props.entry.day), { photo: file }, {
                    forceFormData: true,
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: () => { succeeded = true; },
                    onError: (errors) => {
                        mealError.value = `Foto ${index + 1} de ${files.length}: ${errors.photo ?? 'não foi possível enviar.'}`;
                    },
                    onFinish: () => resolve(succeeded),
                });
            });

            if (!uploaded) break;
        }
    } finally {
        mealUploading.value = false;
    }
};

const removeMealPhoto = (photo: string) =>
    router.delete(route('athlete.days.meal.destroy', { day: props.entry.day, photo }), { preserveState: true, preserveScroll: true });

const saveMealNote = () => {
    if (!props.athleteId) {
        return;
    }

    mealNote.put(route('equipe.athlete.meal.comment', { athlete: props.athleteId, day: props.entry.day }), {
        preserveState: true,
        preserveScroll: true,
    });
};

const addWorkout = () => props.entry.details.workouts.push({ type: props.workoutTypes[0], minutes: '' });
const addMinutes = (index: number, amount: number) => {
    const workout = props.entry.details.workouts[index];
    workout.minutes = (Number(workout.minutes) || 0) + amount;
};
const addWater = (ml: number) => {
    props.entry.details.water_ml += ml;
};

const foodChips = [
    ['no_alcohol', 'Sem álcool'],
    ['no_fast_food', 'Sem fast food'],
    ['no_sweet', 'Sem açúcar'],
] as const;

const pickSkill = (idea: string, checked: boolean) => {
    props.entry.details.skill = checked ? idea : '';
    props.entry.details.manual.skill = false;
};

const fieldClass =
    'h-11 rounded-lg border border-brand-line2 bg-brand-card2 px-3 text-sm text-brand-cream placeholder:text-brand-muted-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold [color-scheme:dark]';
const chipClass = (active: boolean) =>
    ['min-h-9 rounded-full border px-3.5 py-2 text-xs font-bold', active ? 'border-brand-gold bg-brand-gold text-brand-ink' : 'border-brand-line3 text-brand-muted'];
</script>

<template>
    <article class="surface-card grid gap-4 p-4 sm:p-5">
        <button type="button" class="flex w-full items-center justify-between gap-3 text-left" :aria-expanded="open" @click="open = !open">
            <span class="flex min-w-0 flex-col gap-2">
                <span class="font-display text-xl font-extrabold uppercase">
                    Dia {{ entry.day }}
                    <span class="text-sm font-semibold text-brand-muted">· {{ weekdayLabels[weekday] ?? weekday }}, {{ date }}</span>
                </span>
                <span class="flex items-center gap-2.5">
                    <Lightbulb class="size-4" :class="done.skill ? 'text-brand-gold' : 'text-brand-line3'" />
                    <Dumbbell class="size-4" :class="done.movement ? 'text-brand-gold' : 'text-brand-line3'" />
                    <Droplets class="size-4" :class="done.water ? 'text-brand-gold' : 'text-brand-line3'" />
                    <Moon class="size-4" :class="done.sleep ? 'text-brand-gold' : 'text-brand-line3'" />
                    <Salad class="size-4" :class="done.strength ? 'text-brand-gold' : 'text-brand-line3'" />
                    <Star class="size-4" :class="entry.plus_count > 0 ? 'text-brand-gold' : 'text-brand-line3'" />
                </span>
            </span>
            <span class="flex shrink-0 items-center gap-3">
                <span class="font-display text-2xl font-black text-brand-gold">{{ points }}</span>
                <ChevronDown class="size-5 text-brand-muted transition-transform" :class="open ? 'rotate-180' : ''" />
            </span>
        </button>

        <p v-if="lockedReason" class="m-0 text-xs text-brand-muted">{{ lockedReason }}</p>
        <div v-show="open" class="grid gap-3">
            <slot name="before-check-in" />
            <fieldset :disabled="!canEdit || !editing" class="m-0 grid min-w-0 gap-3 border-0 p-0" :class="editing ? '' : 'pointer-events-none opacity-80'">
            <!-- Habilidade -->
            <section class="grid gap-3 rounded-xl border px-3 py-3" :class="[canEdit ? 'pointer-events-auto' : '', done.skill ? 'border-brand-gold bg-brand-gold/10' : 'border-brand-line2']">
                <div class="flex items-center justify-between gap-3 text-sm font-semibold">
                    <span class="flex items-center gap-2"><Lightbulb class="size-4 text-brand-gold" /> Habilidade</span>
                    <span :class="done.skill ? 'text-brand-gold' : 'text-brand-muted'">{{ done.skill ? 'Praticada' : 'Ainda não' }}</span>
                </div>
                <div class="grid gap-1.5">
                    <div v-for="skill in weekSkills" :key="skill" class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-brand-line3 px-3 py-2.5 text-sm">
                        <span class="min-w-0 flex-1">{{ skill }}</span>
                        <label class="flex min-h-9 shrink-0 items-center gap-2.5 text-xs text-brand-muted">
                            <input
                                type="checkbox"
                                :checked="entry.details.skill === skill || entry.details.manual.skill"
                                class="size-5 accent-brand-gold"
                                @change="pickSkill(skill, ($event.target as HTMLInputElement).checked)"
                            />
                            Meta atingida
                        </label>
                    </div>
                    <p v-if="!weekSkills.length" class="m-0 text-xs text-brand-muted">Escolha a habilidade da semana no primeiro dia.</p>
                </div>
                <button v-if="showAthleteControls" type="button" class="h-10 w-fit rounded-lg bg-brand-gold px-4 text-sm font-bold text-brand-ink disabled:opacity-60" :disabled="Boolean(saving) || adminPreview" @click="saveGoal('skill')">
                    {{ saving === 'skill' ? 'Salvando…' : 'Salvar habilidade' }}
                </button>
            </section>

            <!-- Treino -->
            <section class="grid gap-3 rounded-xl border px-3 py-3" :class="done.movement ? 'border-brand-gold bg-brand-gold/10' : 'border-brand-line2'">
                <div class="flex items-center justify-between gap-3 text-sm font-semibold">
                    <span class="flex items-center gap-2"><Dumbbell class="size-4 text-brand-gold" /> Treino</span>
                    <span :class="done.movement ? 'text-brand-gold' : 'text-brand-muted'">{{ minutes }} / {{ goals.movement_minutes }} min</span>
                </div>
                <p class="m-0 text-xs text-brand-muted">Faça este check-in ao concluir sua meta de treino.</p>
                <div class="h-2 overflow-hidden rounded-full bg-brand-line3/40">
                    <div class="h-full rounded-full bg-brand-gold" :style="{ width: pct(minutes, goals.movement_minutes) }" />
                </div>
                <div v-for="(workout, index) in entry.details.workouts" :key="index" class="grid gap-2">
                    <p class="m-0 text-sm font-semibold text-brand-cream">{{ workout.type }} · {{ workout.minutes || 0 }} min</p>
                    <template v-if="editing">
                        <div class="flex flex-wrap gap-1.5">
                            <button v-for="type in workoutTypes" :key="type" type="button" :class="chipClass(workout.type === type)" @click="workout.type = type">{{ type }}</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input v-model.number="workout.minutes" type="number" min="0" max="600" placeholder="min" :class="[fieldClass, 'w-20 shrink-0']" />
                            <span class="text-sm text-brand-muted">min</span>
                            <button type="button" class="shrink-0 rounded-full p-1.5 text-brand-muted hover:text-brand-cream" aria-label="Remover treino" @click="entry.details.workouts.splice(index, 1)">
                                <X class="size-4" />
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <button v-for="amount in [15, 30, 45, 60]" :key="amount" type="button" :class="chipClass(false)" @click="addMinutes(index, amount)">+{{ amount }}</button>
                        </div>
                    </template>
                </div>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <button v-if="showAthleteControls" type="button" class="flex min-h-9 w-fit items-center gap-1.5 text-sm font-semibold text-brand-gold" @click="addWorkout"><Plus class="size-4" /> Adicionar treino</button>
                    <button v-if="showAthleteControls" type="button" class="flex min-h-9 w-fit items-center gap-1.5 text-sm font-semibold text-brand-gold disabled:opacity-60" :disabled="uploading" @click="photoInput?.click()">
                        <Camera class="size-4" /> {{ uploading ? 'Enviando...' : photo ? 'Trocar foto' : 'Adicionar foto' }}
                    </button>
                    <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="sendPhoto" />
                </div>
                <div v-if="photo" class="relative w-fit">
                    <img :src="photo" alt="Foto do treino" class="max-h-48 max-w-full rounded-lg border border-brand-line2 object-cover" />
                    <button v-if="showAthleteControls" type="button" class="absolute right-1.5 top-1.5 rounded-full bg-brand-ink/80 p-1.5 text-brand-cream" aria-label="Remover foto do treino" @click="removePhoto"><X class="size-4" /></button>
                </div>
                <p v-if="photoError" class="m-0 text-xs text-red-400">{{ photoError }}</p>
                <label v-if="minutes < goals.movement_minutes || entry.details.manual.movement" class="flex min-h-9 items-center gap-2.5 text-xs text-brand-muted">
                    <input :checked="entry.details.manual.movement" type="checkbox" class="size-5 shrink-0 accent-brand-gold" @change="toggleGoal('movement', ($event.target as HTMLInputElement).checked)" />
                    Meta atingida
                </label>
                <button v-if="showAthleteControls" type="button" class="h-10 w-fit rounded-lg bg-brand-gold px-4 text-sm font-bold text-brand-ink disabled:opacity-60" :disabled="Boolean(saving) || adminPreview" @click="saveGoal('movement')">
                    {{ saving === 'movement' ? 'Salvando…' : 'Salvar treino' }}
                </button>
            </section>

            <!-- Água -->
            <section class="grid gap-3 rounded-xl border px-3 py-3" :class="done.water ? 'border-brand-gold bg-brand-gold/10' : 'border-brand-line2'">
                <div class="flex items-center justify-between gap-3 text-sm font-semibold">
                    <span class="flex items-center gap-2"><Droplets class="size-4 text-brand-gold" /> Água</span>
                    <span :class="done.water ? 'text-brand-gold' : 'text-brand-muted'">{{ (entry.details.water_ml / 1000).toLocaleString('pt-BR', { maximumFractionDigits: 2 }) }} / {{ (goals.water_ml / 1000).toLocaleString('pt-BR', { maximumFractionDigits: 2 }) }} L</span>
                </div>
                <p class="m-0 text-xs text-brand-muted">Faça este check-in ao atingir sua meta de água.</p>
                <div class="h-2 overflow-hidden rounded-full bg-brand-line3/40">
                    <div class="h-full rounded-full bg-brand-gold" :style="{ width: pct(entry.details.water_ml, goals.water_ml) }" />
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                    <input v-model.number="liters" type="number" min="0" max="10" step="0.1" aria-label="Litros de água" :class="[fieldClass, 'w-24']" />
                    <span class="text-sm text-brand-muted">L</span>
                    <button type="button" :class="chipClass(false)" @click="addWater(250)">+ copo 250 ml</button>
                    <button type="button" :class="chipClass(false)" @click="addWater(500)">+ garrafa 500 ml</button>
                    <button type="button" :class="chipClass(false)" @click="addWater(1000)">+ 1 L</button>
                    <button type="button" :class="chipClass(false)" @click="entry.details.water_ml = 0">Zerar</button>
                </div>
                <label v-if="entry.details.water_ml < goals.water_ml || entry.details.manual.water" class="flex min-h-9 items-center gap-2.5 text-xs text-brand-muted">
                    <input :checked="entry.details.manual.water" type="checkbox" class="size-5 shrink-0 accent-brand-gold" @change="toggleGoal('water', ($event.target as HTMLInputElement).checked)" />
                    Meta atingida
                </label>
                <button v-if="showAthleteControls" type="button" class="h-10 w-fit rounded-lg bg-brand-gold px-4 text-sm font-bold text-brand-ink disabled:opacity-60" :disabled="Boolean(saving) || adminPreview" @click="saveGoal('water')">
                    {{ saving === 'water' ? 'Salvando…' : 'Salvar água' }}
                </button>
            </section>

            <!-- Sono -->
            <section class="grid gap-3 rounded-xl border px-3 py-3" :class="done.sleep ? 'border-brand-gold bg-brand-gold/10' : 'border-brand-line2'">
                <div class="flex items-center justify-between gap-3 text-sm font-semibold">
                    <span class="flex items-center gap-2"><Moon class="size-4 text-brand-gold" /> Sono</span>
                    <span :class="done.sleep ? 'text-brand-gold' : 'text-brand-muted'">{{ entry.details.sleep_hours || 0 }} / {{ goals.sleep_hours }} h</span>
                </div>
                <p class="m-0 text-xs text-brand-muted">Faça este check-in pela manhã.</p>
                <div class="h-2 overflow-hidden rounded-full bg-brand-line3/40">
                    <div class="h-full rounded-full bg-brand-gold" :style="{ width: pct(Number(entry.details.sleep_hours) || 0, goals.sleep_hours) }" />
                </div>
                <div class="flex items-center gap-2">
                    <input v-model.number="entry.details.sleep_hours" type="number" min="0" max="24" step="0.5" placeholder="Horas dormidas" aria-label="Horas dormidas" :class="[fieldClass, 'w-40']" />
                    <span class="text-sm text-brand-muted">horas</span>
                </div>
                <label v-if="(Number(entry.details.sleep_hours) || 0) < goals.sleep_hours || entry.details.manual.sleep" class="flex min-h-9 items-center gap-2.5 text-xs text-brand-muted">
                    <input :checked="entry.details.manual.sleep" type="checkbox" class="size-5 shrink-0 accent-brand-gold" @change="toggleGoal('sleep', ($event.target as HTMLInputElement).checked)" />
                    Meta atingida
                </label>
                <button v-if="showAthleteControls" type="button" class="h-10 w-fit rounded-lg bg-brand-gold px-4 text-sm font-bold text-brand-ink disabled:opacity-60" :disabled="Boolean(saving) || adminPreview" @click="saveGoal('sleep')">
                    {{ saving === 'sleep' ? 'Salvando…' : 'Salvar sono' }}
                </button>
            </section>

            <!-- Alimentação -->
            <section class="grid gap-3 rounded-xl border px-3 py-3" :class="done.strength ? 'border-brand-gold bg-brand-gold/10' : 'border-brand-line2'">
                <div class="flex items-center justify-between gap-3 text-sm font-semibold">
                    <span class="flex items-center gap-2"><Salad class="size-4 text-brand-gold" /> Alimentação</span>
                    <span :class="done.strength ? 'text-brand-gold' : 'text-brand-muted'">{{ done.strength ? 'Cumprida' : 'Marque o que cumpriu' }}</span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <button v-for="chip in foodChips" :key="chip[0]" type="button" :class="chipClass(entry.details.food[chip[0]])" @click="entry.details.food[chip[0]] = !entry.details.food[chip[0]]">
                        {{ chip[1] }}
                    </button>
                    <button v-if="luxuryDay" type="button" :class="chipClass(entry.details.food.luxury)" @click="entry.details.food.luxury = !entry.details.food.luxury">
                        Refeição luxo hoje
                    </button>
                </div>
                <label v-if="!done.strength || entry.details.manual.strength" class="flex min-h-9 items-center gap-2.5 text-xs text-brand-muted">
                    <input :checked="entry.details.manual.strength" type="checkbox" class="size-5 shrink-0 accent-brand-gold" @change="toggleGoal('strength', ($event.target as HTMLInputElement).checked)" />
                    <span class="leading-relaxed">Meta atingida (Seguiu o planejamento alimentar hoje? Fez as refeições programadas e boas escolhas alimentares ao longo do dia?)</span>
                </label>
                <div v-if="showAthleteControls" class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <button type="button" class="flex min-h-9 w-fit items-center gap-1.5 text-sm font-semibold text-brand-gold disabled:opacity-60" :disabled="mealUploading" @click="mealInput?.click()">
                        <Camera class="size-4" /> {{ mealUploading ? 'Enviando fotos…' : 'Adicionar foto da refeição (opcional)' }}
                    </button>
                </div>
                <input ref="mealInput" type="file" accept="image/*" multiple class="hidden" @change="sendMealPhotos" />
                <div v-if="mealPhotos?.length" class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <div v-for="(photo, index) in mealPhotos" :key="photo.id" class="relative">
                        <img :src="photo.url" :alt="'Foto da refeição ' + (index + 1)" loading="lazy" class="aspect-square w-full rounded-lg border border-brand-line2 object-cover" />
                        <button v-if="showAthleteControls" type="button" class="absolute right-1.5 top-1.5 rounded-full bg-brand-ink/80 p-1.5 text-brand-cream disabled:opacity-60" :disabled="mealUploading" :aria-label="'Remover foto da refeição ' + (index + 1)" @click="removeMealPhoto(photo.id)">
                            <X class="size-4" />
                        </button>
                    </div>
                </div>
                <p v-else-if="canComment" class="m-0 text-xs text-brand-muted">Ainda sem fotos da refeição neste dia.</p>
                <p v-if="mealError" class="m-0 text-xs text-red-400">{{ mealError }}</p>
                <p v-if="mealComment && !canComment" class="m-0 text-sm leading-relaxed text-brand-muted">
                    <span class="font-semibold text-brand-cream">Nutri · </span>{{ mealComment }}
                </p>
                <button v-if="showAthleteControls" type="button" class="h-10 w-fit rounded-lg bg-brand-gold px-4 text-sm font-bold text-brand-ink disabled:opacity-60" :disabled="Boolean(saving) || adminPreview" @click="saveGoal('strength')">
                    {{ saving === 'strength' ? 'Salvando…' : 'Salvar alimentação' }}
                </button>
            </section>

            <!-- Refeição luxo -->
            <section class="grid gap-2 rounded-xl border px-3 py-3" :class="entry.plus_count > 0 ? 'border-brand-gold bg-brand-gold/10' : 'border-brand-line2'">
                <div class="flex items-center justify-between gap-3 text-sm font-semibold">
                    <span class="flex items-center gap-2"><Star class="size-4 text-brand-gold" /> Refeição luxo</span>
                    <input v-model.number="entry.plus_count" type="number" min="0" max="9" aria-label="Quantidade de refeição luxo" :class="[fieldClass, 'w-16']" />
                </div>
                <input v-model="entry.notes.plus" maxlength="255" placeholder="O que foi? Ex.: pizza, hambúrguer, churrasco" :class="fieldClass" />
                <button v-if="showAthleteControls" type="button" class="h-10 w-fit rounded-lg bg-brand-gold px-4 text-sm font-bold text-brand-ink disabled:opacity-60" :disabled="Boolean(saving) || adminPreview" @click="saveGoal('plus')">
                    {{ saving === 'plus' ? 'Salvando…' : 'Salvar refeição luxo' }}
                </button>
            </section>

            <input v-model="entry.notes.day" maxlength="255" placeholder="Observação do dia (opcional)" :class="fieldClass" />
                <button v-if="showAthleteControls" type="button" class="h-10 w-fit rounded-lg border border-brand-line3 px-4 text-sm font-semibold disabled:opacity-60" :disabled="Boolean(saving) || adminPreview" @click="saveGoal('notes')">
                    {{ saving === 'notes' ? 'Salvando…' : 'Salvar observação' }}
                </button>
            </fieldset>

            <section v-if="canComment" class="grid gap-2 rounded-xl border border-brand-line2 px-3 py-3">
                <p class="m-0 text-sm font-semibold text-brand-cream">Avaliação da refeição</p>
                <textarea v-model="mealNote.comment" maxlength="500" rows="3" placeholder="Elogio ou orientação sobre o prato" class="min-h-[5.5rem] rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2 text-sm text-brand-cream placeholder:text-brand-muted-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold" />
                <button type="button" class="h-11 w-fit rounded-lg bg-brand-gold px-5 text-sm font-bold text-brand-ink disabled:opacity-60" :disabled="mealNote.processing || !mealNote.comment.trim()" @click="saveMealNote">
                    {{ mealNote.processing ? 'Enviando...' : mealComment ? 'Atualizar elogio' : 'Enviar elogio' }}
                </button>
                <p v-if="mealNote.errors.comment" class="m-0 text-xs text-red-400">{{ mealNote.errors.comment }}</p>
            </section>

            <p v-if="saveMessage" role="status" class="m-0 text-sm text-brand-gold">{{ saveMessage }}</p>
            <p v-if="saveError" class="m-0 text-xs text-red-400">{{ saveError }}</p>
        </div>

        <Dialog v-model:open="cheersOpen">
            <DialogContent class="overflow-hidden border-brand-gold bg-brand-card text-brand-cream sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="font-display text-3xl font-black uppercase text-brand-gold">
                        {{ cheers.length > 1 ? 'Metas batidas!' : cheers[0]?.title }}
                    </DialogTitle>
                    <DialogDescription class="text-brand-muted">
                        Dia {{ entry.day }} · {{ weekday }} {{ date }}
                    </DialogDescription>
                </DialogHeader>
                <ul class="m-0 grid list-none gap-3 p-0">
                    <li v-for="item in cheers" :key="item.title" class="grid gap-1">
                        <span v-if="cheers.length > 1" class="font-display text-lg font-extrabold uppercase text-brand-gold">{{ item.title }}</span>
                        <span class="text-sm leading-relaxed">{{ item.line }}</span>
                    </li>
                </ul>
                <DialogFooter>
                    <Button type="button" @click="cheersOpen = false">Bora seguir!</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <Teleport to="body">
            <div v-if="cheersOpen" class="pointer-events-none fixed inset-0 z-[60] overflow-hidden">
                <span v-for="piece in confetti" :key="piece.id" class="cheer-piece" :style="piece.style" />
            </div>
        </Teleport>
    </article>
</template>
