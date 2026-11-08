<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import CantinhoDaNutri from '@/components/CantinhoDaNutri.vue';
import { useChallenge } from '@/composables/useChallenge';
import { images } from '@/data/images';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Painel',
        href: '/dashboard',
    },
];

defineProps<{
    total: number;
    baseline: { weight: string | null; waist: string | null; abdomen: string | null; hip: string | null; photo: string | null };
}>();

const { countdown, milestones } = useChallenge();
</script>

<template>
    <Head title="Painel" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4 md:p-6">
            <p v-if="$page.props.accessNotice" role="status" class="surface-card m-0 p-4 text-sm text-brand-muted">{{ $page.props.accessNotice }}</p>
            <div class="flex flex-col gap-3">
                <h1 class="display-title text-[clamp(36px,5vw,56px)] text-brand-gold">Desafio da virada</h1>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <div
                    v-for="mile in milestones"
                    :key="mile.day"
                    class="flex min-w-0 flex-col gap-1 rounded-[20px] p-3 sm:gap-2 sm:p-4"
                    :style="{ background: mile.bg, border: `1.5px solid ${mile.border}` }"
                >
                    <span class="text-[11px] font-bold uppercase tracking-widest text-brand-muted-2">{{ mile.date }}</span>
                    <span class="font-display text-2xl font-black italic sm:text-3xl" :style="{ color: mile.numColor }">Dia {{ mile.day }}</span>
                    <span class="font-display text-sm font-extrabold uppercase leading-tight sm:text-base">{{ mile.title }}</span>
                </div>
            </div>

            <CantinhoDaNutri compact />

            <div v-if="baseline.photo || baseline.weight || baseline.waist || baseline.abdomen || baseline.hip" class="surface-card flex items-center gap-4 p-4 sm:gap-5 sm:p-5">
                <img v-if="baseline.photo" :src="baseline.photo" alt="Foto do dia 1" class="h-24 w-20 shrink-0 rounded-xl object-cover sm:h-28 sm:w-24" />
                <div class="min-w-0">
                    <span class="text-[13px] font-bold uppercase tracking-[2px] text-brand-gold">Dia 1 · ponto de partida</span>
                    <p class="m-0 mt-2 text-sm text-brand-muted">
                        <span v-if="baseline.weight">{{ baseline.weight }} kg</span>
                        <span v-if="baseline.waist"> · cintura {{ baseline.waist }} cm</span>
                        <span v-if="baseline.abdomen"> · abdômen {{ baseline.abdomen }} cm</span>
                        <span v-if="baseline.hip"> · quadril {{ baseline.hip }} cm</span>
                    </p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="surface-card flex flex-col justify-between gap-4 p-6">
                    <span class="text-[13px] font-bold uppercase tracking-[2px] text-brand-muted-2">{{ countdown.label }}</span>
                    <div v-if="countdown.isCounting" class="flex gap-2">
                        <div v-for="unit in countdown.units.slice(0, 2)" :key="unit.l" class="text-center">
                            <div class="font-display text-4xl font-extrabold tabular-nums">{{ unit.v }}</div>
                            <div class="text-xs uppercase tracking-widest text-brand-muted-2">{{ unit.l }}</div>
                        </div>
                    </div>
                    <div v-else-if="countdown.isRunning" class="flex flex-col gap-3">
                        <div class="font-display text-5xl font-black italic uppercase">
                            Dia <span class="text-brand-gold">{{ countdown.day }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-brand-line">
                            <div class="h-full rounded-full bg-brand-gold" :style="{ width: countdown.pct }" />
                        </div>
                    </div>
                    <div v-else class="font-display text-3xl font-black italic uppercase text-brand-gold">Concluído</div>
                </div>

                <Link :href="route('objetivos.show', 'alimentacao')" class="surface-card flex items-center gap-4 p-6 md:flex-col md:items-start md:gap-3">
                    <img :src="images.alimentacao" alt="" class="size-16 shrink-0 md:size-14" />
                    <div class="flex min-w-0 flex-col gap-2 md:gap-3">
                        <span class="text-[13px] font-bold uppercase tracking-[2px] text-[#43A047]">Alimentação e organização</span>
                        <p class="m-0 text-sm text-brand-muted">Sem álcool (exceto 3 eventos). Duas refeições luxo por semana: uma refeição inteira cada.</p>
                    </div>
                </Link>

                <div class="surface-card flex items-center justify-center p-4 md:p-6">
                    <img :src="images.logo" alt="Desafio da virada" class="max-h-28 object-contain md:max-h-40" />
                </div>
            </div>

            <Link :href="route('athlete.progress.tab', 'dias')" class="surface-card flex flex-wrap items-center justify-between gap-3 p-5 sm:gap-4 sm:p-6">
                <div>
                    <span class="text-[13px] font-bold uppercase tracking-[2px] text-brand-gold">Minha evolução</span>
                    <p class="m-0 mt-1 font-display text-3xl font-black">{{ total }} pts</p>
                </div>
                <span class="font-bold">Registrar o dia →</span>
            </Link>
        </div>
    </AppLayout>
</template>
