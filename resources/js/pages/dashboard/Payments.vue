<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { CircleAlert, CircleCheck, Clock, CreditCard } from 'lucide-vue-next';

type PaymentRecord = {
    id: number;
    athlete: string | null;
    email: string | null;
    asaasPaymentId: string | null;
    status: string;
    isPastDue: boolean;
    billingType: string | null;
    value: string;
    dueDate: string | null;
    confirmedAt: string | null;
    createdAt: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaymentSummary = {
    category: string;
    quantity: number;
    amount: number;
};

type PaymentFilters = Partial<Record<'status' | 'billing_type' | 'due_from' | 'due_to', string | null>>;

const props = defineProps<{
    filters: PaymentFilters;
    statusSummary: PaymentSummary[];
    billingTypeSummary: PaymentSummary[];
    canViewBalance: boolean;
    accountBalance?: number | null;
    payments: {
        data: PaymentRecord[];
        links: PaginationLink[];
        from: number | null;
        to: number | null;
        total: number;
    };
}>();

const filterForm = ref<PaymentFilters>({ ...props.filters });
watch(() => props.filters, (filters) => { filterForm.value = { ...filters }; });
const applyFilters = () => {
    const query = Object.fromEntries(Object.entries(filterForm.value).filter(([, value]) => value));
    router.get(route('admin.payments'), query, { preserveScroll: true });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Painel', href: '/dashboard' },
    { title: 'Solicitações de pagamento', href: '/admin/pagamentos' },
];

const statusLabel = (status: string) =>
    ({
        PENDING: 'Pendente',
        RECEIVED: 'Recebido',
        CONFIRMED: 'Confirmado',
        OVERDUE: 'Vencido',
        REFUNDED: 'Reembolsado',
        DELETED: 'Excluído',
    })[status] ?? status;

const paymentIndicator = (payment: PaymentRecord) => {
    if (['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'].includes(payment.status)) {
        return payment.billingType === 'CREDIT_CARD'
            ? { icon: CreditCard, color: 'text-blue-600 dark:text-blue-400', label: 'Recebido em cartão' }
            : { icon: CircleCheck, color: 'text-green-700 dark:text-green-400', label: 'Pagamento recebido' };
    }

    if (['REFUNDED', 'DELETED'].includes(payment.status)) return null;

    if (payment.status === 'OVERDUE' || payment.isPastDue) {
        return { icon: CircleAlert, color: 'text-red-600 dark:text-red-400', label: 'Fora do prazo' };
    }

    return payment.dueDate ? { icon: Clock, color: 'text-orange-700 dark:text-orange-400', label: 'Dentro do prazo' } : null;
};

const billingTypeLabel = (billingType: string | null) =>
    ({
        PIX: 'Pix',
        BOLETO: 'Boleto',
        CREDIT_CARD: 'Cartão',
        UNDEFINED: 'A definir no Asaas',
    })[billingType ?? 'UNDEFINED'] ?? billingType;

const linkLabel = (label: string) => {
    if (label.includes('Previous')) return 'Anterior';
    if (label.includes('Next')) return 'Próxima';
    return label;
};

const money = (value: string | number) => Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
</script>

<template>
    <Head title="Solicitações de pagamento" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-3">
                <div class="eyebrow">Asaas</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)]">Solicitações de pagamento</h1>
                <p class="max-w-xl text-brand-muted">Cobranças de inscrição registradas para os atletas do desafio.</p>
            </div>

            <div class="grid items-start gap-4 md:grid-cols-2">
                <section
                    v-for="summary in [
                        { id: 'status-summary', title: 'Por status de pagamento', items: statusSummary ?? [], label: statusLabel },
                        { id: 'billing-summary', title: 'Por forma de pagamento', items: billingTypeSummary ?? [], label: billingTypeLabel },
                    ]"
                    :key="summary.id"
                    :aria-labelledby="summary.id"
                    class="surface-card space-y-3 p-5"
                >
                    <h2 :id="summary.id" class="text-lg font-semibold">{{ summary.title }}</h2>
                    <p v-if="!summary.items.length" class="text-sm text-brand-muted">Nenhuma cobrança registrada.</p>
                    <dl v-else class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,7rem),1fr))] gap-2">
                        <div v-for="item in summary.items" :key="item.category" class="min-w-0 rounded-lg border border-brand-line px-2.5 py-2">
                            <dt class="break-words text-xs font-semibold">{{ summary.label(item.category) }}</dt>
                            <dd class="mt-1 text-sm font-bold">{{ money(item.amount) }}</dd>
                            <dd class="text-xs text-brand-muted">
                                {{ item.quantity }} {{ item.quantity === 1 ? 'cobrança' : 'cobranças' }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>

            <section
                v-if="canViewBalance"
                class="surface-card flex w-fit flex-wrap items-baseline gap-x-3 gap-y-1 px-4 py-2 text-sm"
                aria-labelledby="account-balance"
            >
                <h2 id="account-balance" class="font-medium text-brand-muted">Saldo disponível na conta Asaas</h2>
                <p class="m-0 font-bold" role="status">
                    {{ accountBalance === undefined ? 'Consultando…' : accountBalance === null ? 'Saldo indisponível' : money(accountBalance) }}
                </p>
            </section>

            <Link v-if="canViewBalance" :href="route('admin.payment-reminders')" class="surface-card w-fit px-4 py-3 text-sm font-semibold">
                Enviar lembretes de pagamento
            </Link>

            <form class="surface-card space-y-4 p-4" aria-labelledby="payment-filters" @submit.prevent="applyFilters">
                <div>
                    <h2 id="payment-filters" class="font-semibold">Filtrar lista de pagamentos</h2>
                    <p class="text-sm text-brand-muted">Os cards acima mostram os totais gerais.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <label class="flex min-w-0 flex-col gap-1 text-sm">
                        Status
                        <select v-model="filterForm.status" class="rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2">
                            <option :value="undefined">Todos os status</option>
                            <option v-for="item in statusSummary" :key="item.category" :value="item.category">
                                {{ statusLabel(item.category) }}
                            </option>
                        </select>
                    </label>
                    <label class="flex min-w-0 flex-col gap-1 text-sm">
                        Forma de pagamento
                        <select v-model="filterForm.billing_type" class="rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2">
                            <option :value="undefined">Todas as formas</option>
                            <option v-for="item in billingTypeSummary" :key="item.category" :value="item.category">
                                {{ billingTypeLabel(item.category) }}
                            </option>
                        </select>
                    </label>
                    <label class="flex min-w-0 flex-col gap-1 text-sm">
                        Vencimento a partir de
                        <input v-model="filterForm.due_from" type="date" :max="filterForm.due_to || undefined" class="rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2" />
                    </label>
                    <label class="flex min-w-0 flex-col gap-1 text-sm">
                        Vencimento até
                        <input v-model="filterForm.due_to" type="date" :min="filterForm.due_from || undefined" class="rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2" />
                    </label>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-lg bg-brand-gold px-4 py-2 text-sm font-bold text-brand-ink">Aplicar filtros</button>
                    <Link :href="route('admin.payments')" preserve-scroll class="rounded-lg border border-brand-line2 px-4 py-2 text-sm">
                        Limpar filtros
                    </Link>
                </div>
            </form>

            <div class="surface-card overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="text-[13px] uppercase tracking-[2px] text-brand-muted-2">
                        <tr>
                            <th class="px-5 py-4 font-bold">Atleta</th>
                            <th class="px-5 py-4 font-bold">ID Asaas</th>
                            <th class="px-5 py-4 font-bold">Status</th>
                            <th class="px-5 py-4 font-bold">Forma</th>
                            <th class="px-5 py-4 font-bold">Valor</th>
                            <th class="px-5 py-4 font-bold">Vencimento</th>
                            <th class="px-5 py-4 font-bold">Confirmação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="payment in payments.data" :key="payment.id" class="border-t border-brand-line">
                            <td class="px-5 py-4">
                                <div class="font-semibold">{{ payment.athlete ?? 'Conta removida' }}</div>
                                <div class="text-brand-muted">{{ payment.email ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-4 font-mono text-xs text-brand-muted">{{ payment.asaasPaymentId ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <template v-for="indicator in [paymentIndicator(payment)]" :key="payment.id">
                                        <span v-if="indicator" class="inline-flex shrink-0" :title="indicator.label">
                                            <component :is="indicator.icon" class="size-4" :class="indicator.color" aria-hidden="true" />
                                            <span class="sr-only">{{ indicator.label }} </span>
                                        </span>
                                    </template>
                                    {{ statusLabel(payment.status) }}
                                </div>
                            </td>
                            <td class="px-5 py-4">{{ billingTypeLabel(payment.billingType) }}</td>
                            <td class="px-5 py-4 font-semibold">{{ money(payment.value) }}</td>
                            <td class="px-5 py-4">{{ payment.dueDate ?? '—' }}</td>
                            <td class="px-5 py-4">{{ payment.confirmedAt ?? '—' }}</td>
                        </tr>
                        <tr v-if="payments.data.length === 0">
                            <td colspan="7" class="px-5 py-10 text-center text-brand-muted">Nenhuma cobrança encontrada.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4">
                <p class="m-0 text-sm text-brand-muted">
                    {{ payments.from ?? 0 }}–{{ payments.to ?? 0 }} de {{ payments.total }} solicitações
                </p>
                <nav v-if="payments.links.length > 3" class="flex flex-wrap gap-2" aria-label="Paginação">
                    <Link
                        v-for="link in payments.links"
                        :key="link.label"
                        :href="link.url ?? ''"
                        :aria-disabled="!link.url"
                        class="rounded-lg border px-3 py-2 text-sm"
                        :class="[
                            link.active ? 'border-brand-gold bg-brand-gold text-brand-ink' : 'border-brand-line2 text-brand-cream',
                            !link.url ? 'pointer-events-none opacity-40' : 'hover:border-brand-cream',
                        ]"
                    >
                        {{ linkLabel(link.label) }}
                    </Link>
                </nav>
            </div>
        </div>
    </AppLayout>
</template>
