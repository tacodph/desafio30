<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

type Address = { email: string; name: string };
type EmailLog = {
    id: number; category: string; recipient_email: string; subject: string; body_text: string | null;
    status: string; mailer: string | null; attempts: number; error: string | null; message_id: string | null;
    recipients: Record<string, Address[]> | null; sender: Address[] | null; reply_to: Address[] | null;
    created_at: string; attempted_at: string | null; sent_at: string | null;
};
const props = defineProps<{
    logs: { data: EmailLog[]; total: number; current_page: number; last_page: number; prev_page_url: string | null; next_page_url: string | null };
    filters: { search?: string; status?: string; category?: string };
    counts: Record<string, number>;
    delivery: { mailer: string; queue: string; localOnly: boolean; staleQueued: number };
}>();
const filters = reactive({ search: props.filters.search ?? '', status: props.filters.status ?? '', category: props.filters.category ?? '' });
const statuses: Record<string, string> = { queued: 'Na fila', sending: 'Em processamento', sent: 'Enviado ao provedor', failed: 'Falhou', logged: 'Somente log / teste', processed: 'Processado pelo transporte', skipped: 'Não enviado: deixou de ser elegível' };
const categories: Record<string, string> = { payment_reminder: 'Lembrete de pagamento', support: 'Suporte', notification: 'Notificação de conta', platform: 'Outros emails' };
const date = (value: string | null) => value ? new Date(value).toLocaleString('pt-BR', { timeZone: 'America/Sao_Paulo' }) : '—';
const apply = () => router.get('/admin/mensagens/emails', filters, { preserveState: true, preserveScroll: true });
const addresses = (list: Address[] | null | undefined) => list?.map((address) => address.name ? `${address.name} <${address.email}>` : address.email).join(', ') || '—';
</script>

<template>
    <Head title="Histórico de emails" />
    <AppLayout :breadcrumbs="[{ title: 'Mensagens', href: '#' }, { title: 'Histórico de emails', href: '/admin/mensagens/emails' }]">
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 md:p-8">
            <header class="space-y-3">
                <h1 class="text-3xl font-black text-brand-gold">Histórico de emails</h1>
                <p class="text-sm text-brand-muted">Consulte o que foi preparado, para quem e o resultado de cada envio. O aceite pelo provedor não confirma entrega na caixa de entrada.</p>
                <p class="text-xs text-brand-muted">Serviço: {{ delivery.mailer }} · Fila: {{ delivery.queue }} · Horários de Brasília</p>
            </header>
            <p v-if="delivery.localOnly" role="status" class="surface-card border-amber-400/50 p-4 text-sm text-amber-300">O serviço de email está em modo de log/teste. As mensagens são registradas, mas não são enviadas para os destinatários.</p>
            <p v-if="delivery.staleQueued" role="status" class="surface-card border-amber-400/50 p-4 text-sm text-amber-300">{{ delivery.staleQueued }} email(s) aguardando na fila há mais de cinco minutos. Verifique se o processador de filas está ativo no servidor.</p>
            <div class="flex flex-wrap gap-2 text-xs"><span v-for="(count, status) in counts" :key="status" class="rounded-full border border-brand-line2 px-3 py-2">{{ statuses[status] ?? status }}: {{ count }}</span></div>
            <form class="surface-card grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4" @submit.prevent="apply">
                <label class="grid gap-2 text-sm">Destinatário ou assunto<input v-model="filters.search" maxlength="150" class="min-w-0 rounded-lg border border-brand-line2 bg-brand-card2 p-2" placeholder="Buscar email ou assunto" /></label>
                <label class="grid gap-2 text-sm">Status<select v-model="filters.status" class="rounded-lg border border-brand-line2 bg-brand-card2 p-2"><option value="">Todos</option><option v-for="(label, value) in statuses" :key="value" :value="value">{{ label }}</option></select></label>
                <label class="grid gap-2 text-sm">Tipo<select v-model="filters.category" class="rounded-lg border border-brand-line2 bg-brand-card2 p-2"><option value="">Todos</option><option v-for="(label, value) in categories" :key="value" :value="value">{{ label }}</option></select></label>
                <div class="flex items-end gap-3"><button type="submit" class="rounded-lg bg-brand-gold px-4 py-2 text-sm font-bold text-brand-ink">Filtrar</button><button type="button" class="px-2 py-2 text-sm text-brand-gold underline" @click="router.reload()">Atualizar</button></div>
            </form>
            <p class="text-sm text-brand-muted">{{ logs.total }} registro(s) encontrado(s)</p>
            <article v-for="log in logs.data" :key="log.id" class="surface-card min-w-0 space-y-3 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3"><span class="rounded-full border px-3 py-1 text-xs font-bold" :class="log.status === 'failed' ? 'border-red-400/50 text-red-300' : log.status === 'sent' ? 'border-green-400/50 text-green-300' : 'border-brand-gold/40 text-brand-gold'">{{ statuses[log.status] ?? log.status }}</span><span class="text-xs text-brand-muted">#{{ log.id }} · {{ date(log.created_at) }}</span></div>
                <h2 class="break-words font-bold">{{ log.subject }}</h2>
                <p class="break-all text-sm">Para: {{ log.recipient_email }}</p>
                <p class="text-xs text-brand-muted">{{ categories[log.category] ?? log.category }} · Tentativas: {{ log.attempts }} · Serviço: {{ log.mailer ?? 'Aguardando processamento' }}</p>
                <p v-if="log.error" class="whitespace-pre-line break-words rounded-lg border border-red-400/30 p-3 text-sm text-red-300">{{ log.error }}</p>
                <details class="rounded-lg border border-brand-line2 p-3">
                    <summary class="cursor-pointer text-sm font-bold text-brand-gold">Ver mensagem e detalhes</summary>
                    <dl class="mt-4 grid gap-2 break-words text-xs text-brand-muted">
                        <div><dt class="inline font-bold">Remetente: </dt><dd class="inline">{{ addresses(log.sender) }}</dd></div>
                        <div v-for="(list, kind) in log.recipients" :key="kind"><dt class="inline font-bold">{{ kind }}: </dt><dd class="inline">{{ addresses(list) }}</dd></div>
                        <div><dt class="inline font-bold">Responder para: </dt><dd class="inline">{{ addresses(log.reply_to) }}</dd></div>
                        <div><dt class="inline font-bold">Última tentativa: </dt><dd class="inline">{{ date(log.attempted_at) }}</dd></div>
                        <div><dt class="inline font-bold">Aceito pelo provedor: </dt><dd class="inline">{{ date(log.sent_at) }}</dd></div>
                        <div v-if="log.message_id"><dt class="inline font-bold">Identificador: </dt><dd class="inline break-all">{{ log.message_id }}</dd></div>
                    </dl>
                    <p class="mt-4 whitespace-pre-wrap break-words rounded-lg bg-brand-card2 p-4 text-sm leading-relaxed">{{ log.body_text || 'Sem conteúdo em texto.' }}</p>
                </details>
            </article>
            <p v-if="!logs.data.length" class="surface-card p-6 text-sm text-brand-muted">Nenhum email registrado para estes filtros. O histórico passa a ser gravado a partir da implantação desta atualização.</p>
            <nav class="flex items-center justify-between gap-3 text-sm" aria-label="Paginação do histórico">
                <Link v-if="logs.prev_page_url" :href="logs.prev_page_url" class="text-brand-gold underline">Anterior</Link><span v-else />
                <span>Página {{ logs.current_page }} de {{ logs.last_page }}</span>
                <Link v-if="logs.next_page_url" :href="logs.next_page_url" class="text-brand-gold underline">Próxima</Link><span v-else />
            </nav>
        </main>
    </AppLayout>
</template>
