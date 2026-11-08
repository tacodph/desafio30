<script setup lang="ts">
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

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
    days: { week: number }[];
    weeks: Week[];
}>();

const daysInWeek = (week: number) => props.days.filter((day) => day.week === week).length;
const needDays = (week: number) => Math.min(6, daysInWeek(week));
const weekClosed = (item: Week) => item.checkins >= needDays(item.week);
const missingDays = (item: Week) => Math.max(0, needDays(item.week) - item.checkins);
const weekParts = [
    { key: 'daily', label: 'Diários', description: 'Check-ins preenchidos na plataforma.' },
    { key: 'consistency', label: 'Consistência', description: 'Dias em que as metas foram cumpridas.' },
    { key: 'achievements', label: 'Conquistas', description: 'Vitórias, superações e evoluções registradas.' },
    { key: 'plus_bonus', label: 'Refeição luxo não utilizada', description: '1 refeição não utilizada: +5 pontos. 2 refeições: +10 pontos.' },
    { key: 'skill', label: 'Habilidades', description: 'Uma habilidade por semana, praticada 3 vezes. Cada check-in conta na pontuação.' },
    { key: 'goals_bonus', label: 'Metas', description: 'Pontos pelas metas de alimentação, movimento, hidratação e sono.' },
] as const;
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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
                    <dt class="min-w-0 text-xs text-brand-muted-2">
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <button type="button" class="cursor-help text-left underline decoration-dotted underline-offset-2">
                                    {{ part.label }}
                                </button>
                            </TooltipTrigger>
                            <TooltipContent class="max-w-64 whitespace-normal">
                                {{ part.description }}
                            </TooltipContent>
                        </Tooltip>
                    </dt>
                    <dd class="m-0 text-sm font-bold tabular-nums">{{ item[part.key] }}</dd>
                </div>
            </dl>
        </article>
    </div>
</template>
