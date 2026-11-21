<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Challenge = {
    id: number;
    name: string;
    start_date: string;
    end_date: string;
    default_start_date: string;
    duration_days: number;
    objectives: string[];
};
const props = defineProps<{
    challenge: Challenge;
    canChangeStart: boolean;
    startConfirmed: boolean;
    needsStartChoice: boolean;
    objectiveLabels: Record<string, string>;
    available: { id: number; name: string; default_start_date: string; end_date: string; duration_days: number }[];
    history: {
        challenge_id: number;
        name: string;
        start_date: string;
        end_date: string;
        duration_days: number;
        checkins: number;
        profile: Record<string, string | number | null> | null;
    }[];
}>();
const today = new Date().toLocaleDateString('en-CA', { timeZone: 'America/Sao_Paulo' });
const minimum = props.challenge.default_start_date > today ? props.challenge.default_start_date : today;
const form = useForm({ start_date: props.canChangeStart && props.challenge.start_date < minimum ? minimum : props.challenge.start_date, confirmed: false });
const startDateDisplay = ref(form.start_date.split('-').reverse().join('/'));
const dateError = ref('');
const confirmOpen = ref(false);
function updateStartDate(event: Event) {
    const input = event.target as HTMLInputElement;
    const digits = input.value.replace(/\D/g, '').slice(0, 8);
    const value = digits.replace(/^(\d{2})(\d)/, '$1/$2').replace(/^(\d{2}\/\d{2})(\d)/, '$1/$2');
    startDateDisplay.value = value;
    input.value = value;
    dateError.value = '';
    form.clearErrors('start_date');
    form.start_date = '';
    const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(value);
    if (!match) return;
    const [, day, month, year] = match;
    const iso = `${year}-${month}-${day}`;
    const parsed = new Date(`${iso}T12:00:00`);
    if (parsed.getFullYear() === Number(year) && parsed.getMonth() + 1 === Number(month) && parsed.getDate() === Number(day)) {
        form.start_date = iso;
    }
}
function requestConfirmation() {
    if (!form.start_date) {
        dateError.value = 'Informe uma data válida no formato dd/mm/aaaa.';
        return;
    }
    if (form.start_date < minimum) {
        dateError.value = `Escolha uma data a partir de ${minimum.split('-').reverse().join('/')}.`;
        return;
    }
    confirmOpen.value = true;
}
function confirmStart() {
    if (form.processing) return;
    form.confirmed = true;
    form.put('/atleta/meu-desafio/inicio', {
        preserveScroll: true,
        onError: () => { confirmOpen.value = false; },
        onFinish: () => { form.confirmed = false; },
    });
}
const endDate = computed(() => {
    if (!form.start_date) return '—';
    const date = new Date(`${form.start_date}T12:00:00`);
    date.setDate(date.getDate() + props.challenge.duration_days - 1);
    return date.toLocaleDateString('pt-BR');
});
const date = (value: string) => value.split('-').reverse().join('/');
const labels: Record<string, string> = {
    weight: 'Peso (kg)',
    waist: 'Cintura (cm)',
    abdomen: 'Abdômen (cm)',
    hip: 'Quadril (cm)',
    movement_level: 'Nível de movimento',
    movement_minutes: 'Movimento (min)',
    water_liters: 'Água (L)',
    sleep_goal: 'Meta de sono',
    skill: 'Habilidade',
    strength_goal: 'Meta de alimentação',
};
</script>

<template>
    <Head title="Meu desafio" />
    <AppLayout
        :breadcrumbs="[
            { title: 'Painel', href: '/dashboard' },
            { title: 'Meu desafio', href: '/atleta/meu-desafio' },
        ]"
    >
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-2">
                <div class="eyebrow">{{ needsStartChoice ? 'Escolha seu início' : 'Meu desafio' }}</div>
                <h1 class="display-title text-4xl text-brand-gold">{{ challenge.name }}</h1>
                <p>{{ date(challenge.start_date) }} a {{ date(challenge.end_date) }} · {{ challenge.duration_days }} dias</p>
                <p class="text-brand-muted">{{ challenge.objectives.map((key) => objectiveLabels[key]).join(' · ') }}</p>
            </header>
            <form class="surface-card space-y-4 p-5" @submit.prevent="requestConfirmation">
                <h2 class="text-xl font-bold">{{ canChangeStart ? 'Quando você vai começar?' : 'Seu início confirmado' }}</h2>
                <p class="text-sm text-brand-muted">
                    Escolha a data de início para os seus {{ challenge.duration_days }} dias de desafio. Essa escolha só pode ser salva uma vez,
                    antes do primeiro check-in. O término é calculado automaticamente.
                </p>
                <label class="block space-y-2"
                    ><span>Data de início (dd/mm/aaaa)</span
                    ><input
                        :value="startDateDisplay"
                        type="text"
                        inputmode="numeric"
                        placeholder="dd/mm/aaaa"
                        pattern="[0-9]{2}/[0-9]{2}/[0-9]{4}"
                        maxlength="10"
                        autocomplete="off"
                        :aria-invalid="!!(dateError || form.errors.start_date)"
                        :disabled="!canChangeStart || form.processing || confirmOpen"
                        required
                        @input="updateStartDate"
                        class="w-full rounded-xl border border-brand-line2 bg-brand-card2 p-3 text-brand-cream [color-scheme:dark]"
                /></label>
                <p class="font-bold text-brand-gold">Seu término: {{ endDate }}</p>
                <InputError :message="form.errors.start_date" />
                <InputError :message="dateError" />
                <InputError :message="form.errors.confirmed" />
                <p v-if="!canChangeStart" class="text-sm text-brand-muted">
                    {{ startConfirmed ? 'Você já confirmou a data de início. Essa escolha não pode ser alterada novamente.' : 'Você já tem registros neste desafio. A data de início está bloqueada para preservar seu acompanhamento.' }}
                </p>
                <button v-else type="submit" :disabled="form.processing" class="rounded-xl bg-brand-gold px-5 py-3 font-bold text-brand-ink">
                    Salvar início
                </button>
                <p v-if="form.recentlySuccessful" role="status" class="text-brand-gold">Data atualizada.</p>
                <Link v-if="!canChangeStart" href="/atleta/evolucao/dias" class="inline-block font-bold text-brand-gold">Ir para meus check-ins →</Link>
            </form>
            <Dialog :open="confirmOpen" @update:open="(value) => { if (!form.processing) confirmOpen = value; }">
                <DialogContent class="max-w-lg rounded-2xl border-brand-gold/40 bg-brand-card p-6 text-brand-cream">
                    <DialogHeader>
                        <DialogTitle class="pr-6 text-2xl font-bold text-brand-gold">Confirma a data escolhida?</DialogTitle>
                        <DialogDescription class="text-base leading-relaxed text-brand-muted">Seu desafio terá {{ challenge.duration_days }} dias. Depois de confirmar, você não poderá alterar essa data novamente.</DialogDescription>
                    </DialogHeader>
                    <div class="space-y-2 rounded-xl bg-brand-card2 p-4">
                        <p>Início: <strong class="text-brand-gold">{{ date(form.start_date) }}</strong></p>
                        <p>Término: <strong class="text-brand-gold">{{ endDate }}</strong></p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" :disabled="form.processing" class="rounded-xl bg-brand-gold px-5 py-3 font-bold text-brand-ink disabled:opacity-50" @click="confirmStart">{{ form.processing ? 'Salvando…' : 'Sim, confirmar e continuar' }}</button>
                        <button type="button" :disabled="form.processing" class="rounded-xl border border-brand-line2 px-5 py-3 font-bold disabled:opacity-50" @click="confirmOpen = false">Voltar e escolher outra data</button>
                    </div>
                </DialogContent>
            </Dialog>
            <div class="flex flex-wrap gap-3 text-sm font-bold text-brand-gold">
                <Link href="/atleta/ponto-de-partida/primeiro-dia">Minhas medidas neste desafio</Link
                ><Link href="/atleta/ponto-de-partida/metas">Minhas metas neste desafio</Link>
            </div>
            <section class="surface-card space-y-4 p-5">
                <h2 class="text-xl font-bold">Meu histórico por desafio</h2>
                <details v-for="item in history" :key="item.challenge_id" class="rounded-xl border border-brand-line2 p-4">
                    <summary class="cursor-pointer font-bold text-brand-gold">
                        {{ item.name }} · {{ date(item.start_date) }} a {{ date(item.end_date) }}
                    </summary>
                    <p class="my-3 text-sm">{{ item.checkins }} check-ins · {{ item.duration_days }} dias</p>
                    <dl v-if="item.profile" class="grid gap-3 sm:grid-cols-2">
                        <template v-for="(label, key) in labels" :key="key"
                            ><div v-if="item.profile[key] !== null && item.profile[key] !== '' && item.profile[key] !== undefined">
                                <dt class="text-sm text-brand-muted">{{ label }}</dt>
                                <dd>{{ item.profile[key] }}</dd>
                            </div></template
                        >
                    </dl>
                    <p v-else class="text-sm text-brand-muted">Medidas e metas ainda não cadastradas neste desafio.</p>
                </details>
            </section>
            <section v-if="available.length" class="surface-card space-y-4 p-5">
                <h2 class="text-xl font-bold">Outros desafios disponíveis</h2>
                <p class="text-sm text-brand-muted">
                    Você participa de um desafio por vez. Se já iniciou os registros, poderá entrar em outro após o término do atual. Seu histórico
                    será preservado.
                </p>
                <p v-for="(error, key) in $page.props.errors" :key="key" role="alert" class="text-sm text-red-400">{{ error }}</p>
                <article v-for="item in available" :key="item.id" class="space-y-2 rounded-xl border border-brand-line2 p-4">
                    <h3 class="font-bold">{{ item.name }}</h3>
                    <p class="text-sm">Início padrão: {{ date(item.default_start_date) }} · {{ item.duration_days }} dias</p>
                    <button
                        type="button"
                        class="rounded-xl border border-brand-gold px-4 py-2 font-bold text-brand-gold"
                        @click="router.post(`/atleta/meu-desafio/${item.id}/participar`)"
                    >
                        Participar deste desafio
                    </button>
                </article>
            </section>
        </main>
    </AppLayout>
</template>
