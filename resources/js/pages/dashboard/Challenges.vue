<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Challenge = {
    id: number;
    name: string;
    default_start_date: string;
    end_date: string;
    duration_days: number;
    objectives: string[];
    is_default: boolean;
    accepting_participants: boolean;
    started: boolean;
    participants: number;
};
const props = defineProps<{ challenges: Challenge[]; objectives: Record<string, string>; viewChallengeId: number }>();
const editing = ref<Challenge | null>(null);
const form = useForm({
    name: '',
    default_start_date: '',
    duration_days: 30,
    objectives: Object.keys(props.objectives),
    accepting_participants: true,
});
const endDate = computed(() => {
    if (!form.default_start_date || form.duration_days < 1) return '';
    const date = new Date(`${form.default_start_date}T12:00:00`);
    date.setDate(date.getDate() + Number(form.duration_days) - 1);
    return date.toLocaleDateString('pt-BR');
});
function edit(item: Challenge) {
    editing.value = item;
    Object.assign(form, {
        name: item.name,
        default_start_date: item.default_start_date,
        duration_days: item.duration_days,
        objectives: [...item.objectives],
        accepting_participants: item.accepting_participants,
    });
    form.clearErrors();
}
function reset() {
    editing.value = null;
    form.reset();
    form.clearErrors();
}
function save() {
    const options = { preserveScroll: true, onSuccess: reset };
    if (editing.value) form.put(`/admin/desafios/${editing.value.id}`, options);
    else form.post('/admin/desafios', options);
}
const date = (value: string) => value.split('-').reverse().join('/');
const field = 'w-full rounded-xl border border-brand-line2 bg-brand-card2 p-3 text-brand-cream [color-scheme:dark]';
</script>

<template>
    <Head title="Desafios" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Administração', href: '/dashboard' },
            { title: 'Desafios', href: '/admin/desafios' },
        ]"
    >
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-2">
                <div class="eyebrow">Administração</div>
                <h1 class="display-title text-4xl text-brand-gold">Desafios</h1>
                <p class="text-brand-muted">
                    Defina nome, duração, início padrão e objetivos. Cada atleta pode ajustar seu início antes do primeiro registro diário; o término
                    é calculado automaticamente.
                </p>
            </header>
            <form class="surface-card space-y-4 p-5" @submit.prevent="save">
                <h2 class="text-xl font-bold">{{ editing ? 'Editar desafio' : 'Criar desafio' }}</h2>
                <p v-if="editing?.started" class="text-sm text-brand-muted">
                    Este desafio já tem registros. Datas, duração e objetivos estão protegidos para preservar o histórico.
                </p>
                <label class="block space-y-2"
                    ><span>Nome</span><input v-model="form.name" :class="field" maxlength="150" required /><InputError :message="form.errors.name"
                /></label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block space-y-2"
                        ><span>Início padrão</span
                        ><input v-model="form.default_start_date" :disabled="editing?.started" type="date" :class="field" required /><InputError
                            :message="form.errors.default_start_date"
                    /></label>
                    <label class="block space-y-2"
                        ><span>Duração em dias</span
                        ><input
                            v-model.number="form.duration_days"
                            :disabled="editing?.started"
                            type="number"
                            min="1"
                            max="255"
                            :class="field"
                            required /><InputError :message="form.errors.duration_days"
                    /></label>
                </div>
                <p v-if="endDate" class="text-sm text-brand-gold">Término padrão: {{ endDate }}</p>
                <fieldset :disabled="editing?.started" class="space-y-3">
                    <legend class="mb-2 font-bold">Objetivos deste desafio</legend>
                    <div class="flex flex-wrap gap-4">
                        <label v-for="(label, key) in objectives" :key="key" class="flex items-center gap-2"
                            ><input v-model="form.objectives" type="checkbox" :value="key" class="size-5 accent-brand-gold" />{{ label }}</label
                        >
                    </div>
                    <InputError :message="form.errors.objectives" />
                </fieldset>
                <label class="flex items-center gap-2"
                    ><input v-model="form.accepting_participants" type="checkbox" class="size-5 accent-brand-gold" />Disponível para
                    participação</label
                >
                <p v-for="(error, key) in form.errors" :key="key" role="alert" class="text-sm text-red-400">{{ error }}</p>
                <div class="flex gap-3">
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-brand-gold px-5 py-3 font-bold text-brand-ink">
                        Salvar desafio</button
                    ><button v-if="editing" type="button" class="rounded-xl border border-brand-line2 px-4 py-3" @click="reset">
                        Cancelar edição
                    </button>
                </div>
            </form>
            <section v-for="item in challenges" :key="item.id" class="surface-card space-y-3 p-5">
                <h2 class="text-xl font-bold text-brand-gold">
                    {{ item.name }} <span v-if="item.is_default" class="text-sm">· Padrão para novos atletas</span>
                </h2>
                <p>{{ date(item.default_start_date) }} a {{ date(item.end_date) }} · {{ item.duration_days }} dias</p>
                <p class="text-sm text-brand-muted">
                    {{ item.objectives.map((key) => objectives[key]).join(' · ') }} · {{ item.participants }} participantes
                </p>
                <p class="text-sm">{{ item.accepting_participants ? 'Disponível para participação' : 'Inscrições fechadas' }}</p>
                <div class="flex flex-wrap gap-3 text-sm font-bold">
                    <button type="button" class="rounded-xl border border-brand-line2 px-4 py-2" @click="edit(item)">Editar</button>
                    <button
                        v-if="!item.is_default"
                        type="button"
                        class="rounded-xl border border-brand-line2 px-4 py-2"
                        @click="router.put(`/admin/desafios/${item.id}/padrao`, {}, { preserveScroll: true })"
                    >
                        Definir como padrão para novos atletas
                    </button>
                    <button
                        type="button"
                        class="rounded-xl bg-brand-gold px-4 py-2 text-brand-ink"
                        @click="router.put(`/equipe/desafios/${item.id}/visualizar`, {}, { preserveScroll: true })"
                    >
                        {{ viewChallengeId === item.id ? 'Selecionado para consulta da equipe' : 'Selecionar para consulta da equipe' }}
                    </button>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
