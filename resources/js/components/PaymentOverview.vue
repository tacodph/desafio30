<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Banknote, ChevronRight, Clock, Users } from 'lucide-vue-next';

export type PaymentOverviewItem = {
    status: 'RECEIVED' | 'CONFIRMED' | 'PENDING' | 'OVERDUE';
    quantity: number;
    customers: number;
    amount: number;
};

export type DailyPayment = {
    id: number;
    athlete: string | null;
    amount: number;
    time: string;
};

const props = defineProps<{ items: PaymentOverviewItem[]; dailyPayments?: DailyPayment[] | null }>();
const payments = computed(() => props.dailyPayments ?? []);
const dailyTotal = computed(() => payments.value.reduce((cents, payment) => cents + Math.round(payment.amount * 100), 0) / 100);

const statuses = {
    RECEIVED: { label: 'Recebidas', color: 'text-green-700 dark:text-green-400', bar: 'bg-green-600' },
    CONFIRMED: { label: 'Confirmadas', color: 'text-blue-700 dark:text-blue-400', bar: 'bg-blue-600' },
    PENDING: { label: 'Aguardando pagamento', color: 'text-orange-700 dark:text-orange-400', bar: 'bg-orange-500' },
    OVERDUE: { label: 'Vencidas', color: 'text-red-700 dark:text-red-400', bar: 'bg-red-600' },
};

const currency = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
</script>

<template>
    <section class="surface-card space-y-4 p-5" aria-labelledby="daily-payments">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="daily-payments" class="text-lg font-bold">Pagamentos do dia</h2>
                <p class="text-sm text-brand-muted">{{ payments.length }} {{ payments.length === 1 ? 'pagamento confirmado' : 'pagamentos confirmados' }} hoje</p>
            </div>
            <p class="text-xl font-bold tabular-nums text-green-700 dark:text-green-400">{{ currency.format(dailyTotal) }}</p>
        </div>

        <p v-if="!payments.length" class="text-sm text-brand-muted">Nenhum pagamento confirmado hoje.</p>
        <div v-else class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article
                v-for="payment in payments"
                :key="payment.id"
                class="flex min-w-0 flex-col gap-3 rounded-xl border border-brand-line p-4"
            >
                <h3 class="break-words text-sm font-semibold">{{ payment.athlete ?? 'Conta removida' }}</h3>
                <p class="text-xl font-bold tabular-nums" :class="statuses.RECEIVED.color">{{ currency.format(payment.amount) }}</p>
                <div class="h-2 rounded" :class="statuses.RECEIVED.bar" aria-hidden="true"></div>
                <p class="flex items-center gap-2 text-xs tabular-nums text-brand-muted">
                    <Clock class="size-4" aria-hidden="true" />
                    {{ payment.time }}
                </p>
            </article>
        </div>
    </section>

    <section class="surface-card space-y-4 p-5" aria-labelledby="payment-overview">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="payment-overview" class="text-lg font-bold">Situação das cobranças</h2>
                <p class="text-sm text-brand-muted">Cobranças de inscrição do desafio · Todos os períodos</p>
            </div>
            <Link :href="route('admin.payments')" class="inline-flex items-center gap-1 text-sm font-semibold hover:underline">
                Ver pagamentos <ChevronRight class="size-4" aria-hidden="true" />
            </Link>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article v-for="item in items" :key="item.status" class="flex min-w-0 flex-col gap-3 rounded-xl border border-brand-line p-4">
                <h3 class="text-sm font-semibold">{{ statuses[item.status].label }}</h3>
                <p class="text-xl font-bold tabular-nums" :class="statuses[item.status].color">{{ currency.format(item.amount) }}</p>
                <div class="h-2 rounded" :class="[statuses[item.status].bar, { 'opacity-25': item.quantity === 0 }]" aria-hidden="true"></div>
                <div class="space-y-1 text-xs text-brand-muted">
                    <p class="flex items-center gap-2">
                        <Users class="size-4" aria-hidden="true" />
                        {{ item.customers }} {{ item.customers === 1 ? 'cliente' : 'clientes' }}
                    </p>
                    <p class="flex items-center gap-2">
                        <Banknote class="size-4" aria-hidden="true" />
                        {{ item.quantity }} {{ item.quantity === 1 ? 'cobrança' : 'cobranças' }}
                    </p>
                </div>
            </article>
        </div>
    </section>
</template>
