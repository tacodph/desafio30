<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{ workoutTypes: { id: number; name: string }[] }>();

const form = useForm({ name: '' });
const save = () => form.post(route('admin.workout-types.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
});
</script>

<template>
    <Head title="Tipos de treino" />

    <AppLayout :breadcrumbs="[
        { title: 'Administração', href: '/dashboard' },
        { title: 'Treinos', href: '/admin/treinos' },
    ]">
        <div class="mx-auto flex w-full max-w-3xl flex-col gap-5 p-4 md:p-6">
            <h1 class="display-title text-[clamp(36px,5vw,56px)]">Tipos de treino</h1>
            <p class="text-sm text-brand-muted">Cadastre as modalidades que os atletas podem escolher nos check-ins de treino.</p>
            <form class="surface-card flex flex-col gap-3 p-5" @submit.prevent="save">
                <label for="workout-name" class="text-sm font-semibold">Modalidade</label>
                <div class="flex flex-wrap gap-3">
                    <input id="workout-name" v-model="form.name" type="text" required maxlength="40" placeholder="Ex.: Pilates" class="h-11 min-w-0 flex-1 rounded-xl border border-brand-line2 bg-brand-card2 px-3 text-brand-cream" :disabled="form.processing" />
                    <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Salvando…' : 'Cadastrar treino' }}</Button>
                </div>
                <InputError :message="form.errors.name" />
                <p v-if="form.recentlySuccessful" role="status" class="text-sm text-brand-gold">Modalidade cadastrada.</p>
            </form>

            <section class="surface-card p-5" aria-labelledby="workout-types-list">
                <h2 id="workout-types-list" class="mb-3 font-semibold">Modalidades disponíveis</h2>
                <ul class="divide-y divide-brand-line">
                    <li v-for="workout in workoutTypes" :key="workout.id" class="py-2 text-sm">{{ workout.name }}</li>
                </ul>
            </section>
        </div>
    </AppLayout>
</template>
