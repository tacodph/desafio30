<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    firstAccess: boolean;
    profile: {
        movement_level: string;
        movement_minutes: number | string;
        water_liters: number | string;
        sleep_goal: string;
        skill: string;
        strength_goal: string;
        test_1: string;
        test_15: string;
        test_30: string;
        test_45: string;
        penalty: boolean;
        penalty_donated_on: string;
        penalty_note: string;
    };
    penalty: { missed: number; amount: number };
    weight: number | string | null;
    waterMinimum: number | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ponto de partida', href: '#' },
    { title: 'Metas', href: '/atleta/ponto-de-partida/metas' },
];

// Meta de água já vem com o mínimo oficial (35 ml por kg de peso) quando ainda não foi definida.
const metas = useForm({ ...props.profile, water_liters: props.profile.water_liters || props.waterMinimum || '' });
const save = () => metas.put(route('athlete.goals.update'));
</script>

<template>
    <Head title="Metas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-3">
                <div class="eyebrow">{{ firstAccess ? 'Passo 2 de 2 · ponto de partida' : 'Ponto de partida' }}</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)]">Metas</h1>
            </div>

                <form class="surface-card grid gap-4 p-5 sm:p-6" @submit.prevent="save">
                    <p class="m-0 text-brand-muted">Preencha no dia 1. Pode ajustar no dia 15 e no dia 30.</p>
                    <div class="grid gap-2">
                        <Label for="movement_level">Nível de movimento</Label>
                        <select id="movement_level" v-model="metas.movement_level" class="h-11 rounded-xl border border-brand-line2 bg-brand-card2 px-3 text-brand-cream [color-scheme:dark]">
                            <option value="">Escolher</option>
                            <option value="iniciante">Iniciante (20–30 min)</option>
                            <option value="intermediario">Intermediário (30–45 min)</option>
                            <option value="avancado">Avançado (45+ min)</option>
                        </select>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="movement_minutes">Minutos de movimento por dia</Label>
                            <Input id="movement_minutes" v-model="metas.movement_minutes" type="number" min="0" max="300" />
                            <InputError :message="metas.errors.movement_minutes" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="water_liters">Meta de água (litros)</Label>
                            <Input id="water_liters" v-model="metas.water_liters" type="number" min="0" max="15" step="0.1" />
                            <p v-if="waterMinimum" class="m-0 text-xs text-brand-muted">Mínimo recomendado: {{ waterMinimum.toLocaleString('pt-BR') }} L (35 ml por kg × {{ Number(weight) }} kg).</p>
                        <p v-else class="m-0 text-xs text-brand-muted">Referência: 35 ml por kg de peso. Informe seu peso em Primeiro dia para calcular.</p>
                        <InputError :message="metas.errors.water_liters" />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label for="sleep_goal">Meta de sono</Label>
                        <Input id="sleep_goal" v-model="metas.sleep_goal" placeholder="7h por noite ou deitar às 23h" />
                    </div>
                    <p class="text-sm text-brand-muted">
                        Aqui você decide se haverá penalidade solidária no seu desafio.
                    </p>
                    <details class="text-sm">
                        <summary class="w-fit cursor-pointer font-semibold text-brand-gold underline">Clique aqui para entender essa opção</summary>
                        <div class="mt-2 space-y-2 rounded-xl border border-brand-line2 p-4 text-brand-muted">
                            <h3 class="font-bold text-brand-cream">Penalidade solidária</h3>
                            <p>É um compromisso opcional que você pode assumir no início do desafio.</p>
                            <p>Se escolher participar, reserve <strong>R$ 10,00 por dia sem check-in</strong>. Ao final do desafio, você pode usar o valor acumulado para uma doação ou uma comemoração.</p>
                            <p>Por exemplo, 3 dias sem check-in somam R$ 30,00.</p>
                        </div>
                    </details>
                    <label class="flex items-center gap-3 text-sm">
                        <input v-model="metas.penalty" type="checkbox" class="size-4 accent-brand-gold" />
                        Penalidade solidária: R$ 10 por dia sem check-in
                    </label>
                    <div v-if="metas.penalty" class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="penalty_donated_on">Data da doação</Label>
                            <Input id="penalty_donated_on" v-model="metas.penalty_donated_on" type="date" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="penalty_note">Para quem</Label>
                            <Input id="penalty_note" v-model="metas.penalty_note" />
                        </div>
                        <p class="text-sm text-brand-muted sm:col-span-2">
                            {{ penalty.missed }} dias sem check-in · R$ {{ penalty.amount }},00
                        </p>
                    </div>
                    <Button type="submit" class="w-fit" :disabled="metas.processing">{{ firstAccess ? 'Salvar e começar' : 'Salvar metas' }}</Button>
                </form>
        </div>
    </AppLayout>
</template>