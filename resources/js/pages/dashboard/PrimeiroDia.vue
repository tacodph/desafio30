<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const positions = [
    { key: 'photo_front', label: 'Frente' },
    { key: 'photo_side', label: 'Lado' },
    { key: 'photo_back', label: 'Costas' },
] as const;

type PhotoKey = (typeof positions)[number]['key'];

const props = defineProps<{
    firstAccess: boolean;
    baseline: {
        weight: string | number;
        waist: string | number;
        abdomen: string | number;
        hip: string | number;
        photo: string | null;
        startPhotos: Partial<Record<PhotoKey, string>>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Ponto de partida', href: '#' },
    { title: 'Primeiro dia', href: '/atleta/ponto-de-partida/primeiro-dia' },
];

const form = useForm({
    weight: props.baseline.weight,
    waist: props.baseline.waist,
    abdomen: props.baseline.abdomen,
    hip: props.baseline.hip,
    photo_front: null as File | null,
    photo_side: null as File | null,
    photo_back: null as File | null,
});

const onPhoto = (key: PhotoKey, event: Event) => {
    form[key] = (event.target as HTMLInputElement).files?.[0] ?? null;
};

const save = () => form.post(route('athlete.start.first-day.store'));

const fieldClass = 'h-11 rounded-xl border border-brand-line2 bg-brand-card2 px-3 font-normal text-brand-cream';
</script>

<template>
    <Head title="Primeiro dia" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-3">
                <div class="eyebrow">{{ firstAccess ? 'Passo 1 de 2 · ponto de partida' : 'Ponto de partida' }}</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)]">Primeiro dia</h1>
                <p class="m-0 text-brand-muted">
                    Registre suas medidas e, se quiser, fotos do início do desafio para acompanhar sua evolução até o dia 30.
                </p>
            </div>

            <form class="surface-card flex flex-col gap-4 p-4 sm:p-6" @submit.prevent="save">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                    <label class="flex min-w-0 flex-col gap-1.5 text-sm font-semibold">
                        Peso (kg)
                        <input v-model="form.weight" type="number" min="30" max="300" step="0.1" :class="fieldClass" />
                        <InputError :message="form.errors.weight" />
                    </label>
                    <label class="flex min-w-0 flex-col gap-1.5 text-sm font-semibold">
                        Cintura (cm) <span class="font-normal text-brand-muted-2">· CC</span>
                        <input v-model="form.waist" type="number" min="40" max="200" step="0.1" :class="fieldClass" />
                        <InputError :message="form.errors.waist" />
                    </label>
                    <label class="flex min-w-0 flex-col gap-1.5 text-sm font-semibold">
                        Abdômen (cm) <span class="font-normal text-brand-muted-2">· CA</span>
                        <input v-model="form.abdomen" type="number" min="40" max="200" step="0.1" :class="fieldClass" />
                        <InputError :message="form.errors.abdomen" />
                    </label>
                    <label class="flex min-w-0 flex-col gap-1.5 text-sm font-semibold">
                        Quadril (cm) <span class="font-normal text-brand-muted-2">· CQ</span>
                        <input v-model="form.hip" type="number" min="40" max="200" step="0.1" :class="fieldClass" />
                        <InputError :message="form.errors.hip" />
                    </label>
                </div>
                <fieldset class="grid gap-3">
                    <legend class="mb-2 text-sm font-semibold">Fotos do início do desafio</legend>
                    <p class="text-sm text-brand-muted">
                        As fotos são opcionais. Você pode enviar fotos de frente, de lado e de costas.
                        <Link :href="route('athlete.start.measures-guide')" class="font-semibold text-brand-gold underline">Veja os exemplos no material de apoio.</Link>
                    </p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <label v-for="position in positions" :key="position.key" class="flex min-w-0 flex-col gap-2 rounded-xl border border-brand-line2 p-3 text-sm font-semibold">
                            {{ position.label }}
                            <img
                                v-if="baseline.startPhotos?.[position.key]"
                                :src="baseline.startPhotos[position.key]"
                                :alt="'Foto inicial · ' + position.label"
                                class="h-40 w-full rounded-lg object-contain"
                            />
                            <input type="file" accept="image/*" class="w-full text-sm font-normal text-brand-muted" @change="onPhoto(position.key, $event)" />
                            <InputError :message="form.errors[position.key]" />
                        </label>
                    </div>
                    <div v-if="baseline.photo" class="flex items-center gap-3 text-sm text-brand-muted">
                        <img :src="baseline.photo" alt="Foto inicial anterior" class="h-24 w-16 rounded-lg object-cover" />
                        <span>Foto inicial anterior</span>
                    </div>
                </fieldset>
                <Button type="submit" class="w-fit" :disabled="form.processing">{{ firstAccess ? 'Continuar para as metas' : 'Salvar' }}</Button>
            </form>

            <Link :href="route('athlete.start.measures-guide')" class="surface-card flex flex-col gap-1 p-5 sm:p-6">
                <div class="eyebrow">Material de apoio</div>
                <h2 class="m-0 text-xl font-bold text-brand-cream">Como tirar suas medidas</h2>
                <p class="m-0 mt-1 text-sm text-brand-muted">Veja como medir cintura, abdômen, quadril e tirar as fotos de evolução.</p>
            </Link>
        </div>
    </AppLayout>
</template>
