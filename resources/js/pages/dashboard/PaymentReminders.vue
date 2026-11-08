<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    athletes: { id: number; name: string; email: string }[];
    defaultSubject: string;
    defaultBody: string;
    queued: number | null;
}>();

const form = useForm({
    athlete_ids: [] as number[],
    subject: props.defaultSubject,
    body: props.defaultBody,
});

const allSelected = computed(() => props.athletes.length > 0 && props.athletes.every((athlete) => form.athlete_ids.includes(athlete.id)));

const toggleAll = (event: Event) => {
    form.athlete_ids = (event.target as HTMLInputElement).checked ? props.athletes.map((athlete) => athlete.id) : [];
};

const send = () => form.post(route('admin.payment-reminders.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset('athlete_ids'),
});
</script>

<template>
    <Head title="Lembretes de pagamento" />

    <AppLayout :breadcrumbs="[
        { title: 'Painel', href: '/dashboard' },
        { title: 'Pagamentos', href: '/admin/pagamentos' },
        { title: 'Lembretes', href: '/admin/pagamentos/lembretes' },
    ]">
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-5 p-4 md:p-6">
            <h1 class="display-title text-[clamp(32px,5vw,52px)]">Lembretes de pagamento</h1>
            <p class="text-sm text-brand-muted">Selecione os atletas com cobrança pendente e revise o e-mail antes de enviar.</p>
            <p class="text-sm text-brand-muted">Os lembretes entram em uma fila e dependem do processador de filas para envio. <Link href="/admin/mensagens/emails?category=payment_reminder" class="font-bold text-brand-gold underline">Acompanhar histórico e resultado dos envios</Link>.</p>
            <p v-if="queued" role="status" class="surface-card p-4 text-sm text-brand-gold">
                {{ queued }} {{ queued === 1 ? 'lembrete colocado' : 'lembretes colocados' }} na fila de envio.
            </p>

            <form class="grid gap-4" @submit.prevent="send">
                <fieldset class="surface-card grid gap-3 p-4" :disabled="form.processing" aria-labelledby="reminder-athletes">
                    <h2 id="reminder-athletes" class="font-semibold">Atletas com pagamento pendente</h2>
                    <p v-if="!athletes.length" class="text-sm text-brand-muted">Nenhum atleta com cobrança pendente.</p>
                    <template v-else>
                        <label class="flex items-center gap-2 text-sm font-semibold">
                            <input type="checkbox" class="accent-brand-gold" :checked="allSelected" @change="toggleAll" />
                            Selecionar todos
                        </label>
                        <div class="max-h-72 divide-y divide-brand-line overflow-y-auto">
                            <label v-for="athlete in athletes" :key="athlete.id" class="flex items-center gap-3 py-2 text-sm">
                                <input v-model="form.athlete_ids" type="checkbox" :value="athlete.id" class="accent-brand-gold" />
                                <span class="min-w-0 break-words">{{ athlete.name }} <span class="text-brand-muted">· {{ athlete.email }}</span></span>
                            </label>
                        </div>
                    </template>
                    <InputError :message="form.errors.athlete_ids" />
                </fieldset>

                <fieldset class="surface-card grid gap-3 p-4" :disabled="form.processing" aria-labelledby="reminder-message">
                    <h2 id="reminder-message" class="font-semibold">E-mail</h2>
                    <label class="grid gap-1 text-sm font-semibold">
                        Assunto
                        <input v-model="form.subject" required maxlength="180" class="rounded-lg border border-brand-line2 bg-brand-card2 p-2 font-normal" />
                    </label>
                    <InputError :message="form.errors.subject" />
                    <label class="grid gap-1 text-sm font-semibold">
                        Mensagem
                        <textarea v-model="form.body" required maxlength="10000" rows="10" class="rounded-lg border border-brand-line2 bg-brand-card2 p-2 font-normal" />
                    </label>
                    <InputError :message="form.errors.body" />
                </fieldset>

                <Button type="submit" class="w-fit" :disabled="form.processing || !form.athlete_ids.length">
                    {{ form.processing ? 'Preparando envio…' : `Enviar lembretes (${form.athlete_ids.length})` }}
                </Button>
            </form>
        </div>
    </AppLayout>
</template>
