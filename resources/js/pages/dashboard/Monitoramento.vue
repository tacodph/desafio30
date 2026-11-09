<script setup lang="ts">
import { achieved, type DayDetails, type DayGoals } from '@/data/dayLog';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Apple, CalendarDays, Droplets, Dumbbell, Lightbulb, Moon, Trophy } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    days: { day: number; date: string; details: DayDetails; saved: boolean; notes: Record<string, string>; plus_count: number; points: number; workout_photo: string | null; meal_photos: { id: string; url: string }[]; meal_comment: string }[];
    goals: DayGoals;
    weeklySkills: string[][];
    startDate: string;
}>();

const habits = [
    { key: 'strength', label: 'Alimentação no plano', icon: Apple, color: '#65c96e' },
    { key: 'movement', label: 'Treino / movimento', icon: Dumbbell, color: '#facc15' },
    { key: 'water', label: 'Hidratação', icon: Droplets, color: '#55bde9' },
    { key: 'sleep', label: 'Sono de qualidade', icon: Moon, color: '#f58bb9' },
    { key: 'skill', label: 'Habilidade', icon: Lightbulb, color: '#b99bf7' },
] as const;
const weekdays = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
const summary = (details: DayDetails, key: typeof habits[number]['key']) => {
    switch (key) {
        case 'movement': return details.workouts.length ? details.workouts.map((workout) => `${workout.type}${workout.minutes !== '' ? ` · ${workout.minutes} min` : ''}`).join(', ') : 'Sem treino detalhado.';
        case 'water': return details.water_ml ? `${(details.water_ml / 1000).toLocaleString('pt-BR')} L de água` : 'Quantidade não informada.';
        case 'sleep': return details.sleep_hours !== '' ? `${details.sleep_hours} horas de sono` : 'Horas não informadas.';
        case 'strength': return [details.food.no_alcohol && 'Sem álcool', details.food.no_fast_food && 'Sem fast food', details.food.no_sweet && 'Sem doces', details.food.luxury && 'Refeição luxo'].filter(Boolean).join(' · ') || 'Sem detalhes de alimentação.';
        case 'skill': return details.skill || 'Habilidade não detalhada.';
    }
};
const calendar = computed(() => {
    const start = new Date(`${props.startDate}T12:00:00`);
    const offset = (start.getDay() + 6) % 7;
    const cells = props.days.map((day, index) => {
        const date = new Date(start);
        date.setDate(start.getDate() + index);
        return {
            ...day,
            number: date.getDate().toString().padStart(2, '0'),
            month: date.toLocaleDateString('pt-BR', { month: 'short' }).replace('.', ''),
            fullDate: date.toLocaleDateString('pt-BR'),
            done: achieved(day.details, props.goals),
        };
    });
    return [...Array(offset).fill(null), ...cells, ...Array((7 - ((offset + cells.length) % 7)) % 7).fill(null)];
});
const completed = computed(() => props.days.reduce((sum, day) => sum + (day.saved ? Object.values(achieved(day.details, props.goals)).filter(Boolean).length : 0), 0));
const totalObjectives = computed(() => props.days.length * habits.length);
const period = computed(() => `${props.days[0]?.date ?? ''} a ${props.days.at(-1)?.date ?? ''}`);
</script>

<template>
    <Head title="Monitoramento 30 dias" />
    <AppLayout :breadcrumbs="[{ title: 'Cantinho da Nutri', href: '/cantinho-da-nutri' }, { title: 'Monitoramento 30 dias', href: '/cantinho-da-nutri/monitoramento' }]">
        <main class="mx-auto w-full max-w-6xl p-4 md:p-8">
            <section class="rounded-3xl border border-white/10 bg-[#101010] p-4 text-white sm:p-7">
                <header class="mb-7 flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[3px] text-yellow-400">Desafio da Virada · 30 dias</p>
                        <h1 class="mt-3 text-3xl font-black uppercase leading-tight sm:text-5xl">Monitoramento<br />de hábitos</h1>
                        <p class="mt-4 flex items-center gap-2 text-xl font-bold"><CalendarDays class="size-6 text-yellow-400" aria-hidden="true" />{{ period }}</p>
                    </div>
                    <ul class="grid gap-2 rounded-2xl border-2 border-yellow-400 p-4 text-sm font-semibold">
                        <li v-for="habit in habits" :key="habit.key" class="flex items-center gap-3">
                            <component :is="habit.icon" class="size-5" :style="{ color: habit.color }" aria-hidden="true" />{{ habit.label }}
                        </li>
                    </ul>
                </header>
                <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                    <p class="text-sm text-white/70">Os ícones coloridos mostram os objetivos cumpridos nos registros de Evolução. Clique em um objetivo cumprido para ver o resumo do dia. <strong class="text-yellow-400">{{ completed }} / {{ totalObjectives }} objetivos concluídos.</strong></p>
                    <a href="/cantinho-da-nutri/monitoramento/imprimir" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-yellow-400 px-4 py-3 text-sm font-bold text-yellow-400 hover:bg-yellow-400/10">Versão para preencher à mão</a>
                </div>
                <div class="overflow-x-auto pb-2" tabindex="0" role="region" aria-label="Calendário de monitoramento dos 30 dias">
                    <div class="sm:min-w-[560px]">
                        <div class="mb-2 hidden grid-cols-7 gap-2 sm:grid">
                            <div v-for="weekday in weekdays" :key="weekday" class="rounded-xl bg-yellow-400 py-3 text-center text-lg font-black uppercase text-black">{{ weekday }}</div>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-7">
                            <div v-for="(day, index) in calendar" :key="index" class="min-h-28 rounded-2xl p-3 sm:min-h-32" :class="!day ? 'hidden bg-white/5 sm:block' : day.day === 1 ? 'bg-emerald-200 text-black' : day.day === 15 ? 'bg-sky-200 text-black' : day.day === days.length ? 'bg-yellow-100 text-black' : 'bg-[#faf8f0] text-black'">
                                <template v-if="day">
                                    <div class="flex items-start justify-between">
                                        <div><span class="text-3xl font-black leading-none">{{ day.number }}</span><span class="mt-1 block text-xs font-bold uppercase opacity-60">{{ day.month }}</span></div>
                                        <Trophy v-if="day.day === days.length" class="size-6" aria-label="Último dia do desafio" />
                                    </div>
                                    <span v-if="day.day === 1 || day.day === 15" class="mt-2 block text-[10px] font-extrabold uppercase tracking-wide">{{ day.day === 1 ? 'Início · Dia 1' : 'Metade · Dia 15' }}</span>
                                    <div class="mt-5 flex flex-wrap gap-1">
                                        <template v-for="habit in habits" :key="habit.key">
                                            <Dialog v-if="day.saved && day.done[habit.key]">
                                                <DialogTrigger as-child>
                                                    <button type="button" class="flex size-7 items-center justify-center rounded-md border-2 transition-transform hover:scale-110 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black" :style="{ borderColor: habit.color, backgroundColor: habit.color }" :aria-label="`${habit.label} cumprido em ${day.fullDate}. Abrir resumo do dia`" :title="`${habit.label} cumprido — ver resumo do dia`">
                                                        <component :is="habit.icon" class="size-4 text-black" aria-hidden="true" />
                                                    </button>
                                                </DialogTrigger>
                                                <DialogContent class="max-h-[85vh] w-[calc(100%_-_2rem)] overflow-y-auto rounded-2xl sm:max-w-xl">
                                                    <DialogHeader>
                                                        <DialogTitle>Resumo do dia {{ day.day }} · {{ day.fullDate }}</DialogTitle>
                                                        <DialogDescription>{{ day.points }} pontos registrados neste dia. {{ habit.label }} cumprido.</DialogDescription>
                                                    </DialogHeader>
                                                    <div class="grid gap-3">
                                                        <section v-for="item in habits" :key="item.key" class="rounded-xl border p-4" :style="{ borderColor: item.key === habit.key ? item.color : undefined }">
                                                            <h3 class="flex items-center gap-2 text-sm font-bold">
                                                                <component :is="item.icon" class="size-4 shrink-0" :style="{ color: item.color }" aria-hidden="true" />{{ item.label }}
                                                                <span class="ml-auto text-xs" :class="day.done[item.key] ? 'text-brand-gold' : 'text-muted-foreground'">{{ day.done[item.key] ? 'Cumprido' : 'Não concluído' }}</span>
                                                            </h3>
                                                            <p class="mt-2 whitespace-pre-line break-words text-sm text-muted-foreground">{{ summary(day.details, item.key) }}</p>
                                                            <p v-if="day.details.manual[item.key]" class="mt-1 text-xs text-brand-gold">Meta marcada manualmente.</p>
                                                            <p v-if="day.notes[item.key]" class="mt-2 whitespace-pre-line break-words text-sm">{{ day.notes[item.key] }}</p>
                                                        </section>
                                                    </div>
                                                    <p class="text-sm">Refeições luxo: {{ day.plus_count }}</p>
                                                    <p v-if="day.notes.plus" class="whitespace-pre-line break-words text-sm">{{ day.notes.plus }}</p>
                                                    <section v-if="day.notes.day">
                                                        <h3 class="text-sm font-bold">Observações do dia</h3>
                                                        <p class="mt-2 whitespace-pre-line break-words text-sm text-muted-foreground">{{ day.notes.day }}</p>
                                                    </section>
                                                    <section v-if="day.workout_photo || day.meal_photos.length" class="grid gap-3">
                                                        <h3 class="text-sm font-bold">Fotos do dia</h3>
                                                        <img v-if="day.workout_photo" :src="day.workout_photo" alt="Registro do treino do dia" class="max-h-72 w-full rounded-xl object-contain" loading="lazy" />
                                                        <img v-for="photo in day.meal_photos" :key="photo.id" :src="photo.url" alt="Registro de refeição do dia" class="max-h-72 w-full rounded-xl object-contain" loading="lazy" />
                                                    </section>
                                                    <section v-if="day.meal_comment">
                                                        <h3 class="text-sm font-bold">Comentário da Nutri</h3>
                                                        <p class="mt-2 whitespace-pre-line break-words text-sm text-muted-foreground">{{ day.meal_comment }}</p>
                                                    </section>
                                                </DialogContent>
                                            </Dialog>
                                            <span v-else class="flex size-7 items-center justify-center rounded-md border-2" :style="{ borderColor: habit.color }" :title="`${day.fullDate}: ${habit.label} — sem conclusão registrada`"><span class="sr-only">{{ habit.label }}: sem conclusão registrada</span></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 rounded-2xl border-2 border-yellow-400 p-5">
                    <h2 class="flex items-center gap-2 text-xl font-black uppercase"><Lightbulb class="text-yellow-400" aria-hidden="true" />Habilidades da semana</h2>
                    <ul class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                        <li v-for="(skills, index) in weeklySkills" :key="index"><span class="font-bold text-yellow-400">Semana {{ index + 1 }}:</span> {{ skills.filter(Boolean).join(', ') || 'Ainda não definida' }}</li>
                    </ul>
                </div>
                <Link href="/atleta/evolucao/dias" class="mt-5 flex min-h-11 w-full items-center justify-center rounded-xl bg-yellow-400 px-5 py-3 text-center text-sm font-bold text-black hover:bg-yellow-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-yellow-400">
                    Registrar objetivos no check-in do dia
                </Link>
            </section>
        </main>
    </AppLayout>
</template>

