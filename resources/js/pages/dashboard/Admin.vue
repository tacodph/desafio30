<script setup lang="ts">
import PaymentOverview, { type DailyPayment, type PaymentOverviewItem } from '@/components/PaymentOverview.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Banknote, LockKeyholeOpen, Users, Utensils } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Administração', href: '/dashboard' }];

defineProps<{
    dailyPayments: DailyPayment[] | null;
    paymentOverview: PaymentOverviewItem[];
    counts: { id: number; name: string; users_count: number }[];
}>();
</script>

<template>
    <Head title="Administração" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-3">
                <div class="eyebrow">Administração</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)]">Controle do site</h1>
                <p class="max-w-xl text-brand-muted">Quem está no desafio, separado pelo tipo de usuário.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div v-for="type in counts" :key="type.id" class="surface-card flex flex-col gap-2 p-5">
                    <span class="text-[13px] font-bold uppercase tracking-[2px] text-brand-muted-2">{{ type.name }}</span>
                    <span class="font-display text-5xl font-black tabular-nums">{{ type.users_count }}</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <Link :href="route('admin.challenge-access')" class="surface-card inline-flex w-fit items-center gap-2 px-5 py-4 font-display text-lg font-bold uppercase">
                    <LockKeyholeOpen class="size-5 shrink-0" aria-hidden="true" />
                    Liberações do desafio
                </Link>
                <Link :href="route('admin.users')" class="surface-card inline-flex w-fit items-center gap-2 px-5 py-4 font-display text-lg font-bold uppercase">
                    <Users class="size-5 shrink-0" aria-hidden="true" />
                    Ver usuários
                </Link>
                <Link :href="route('admin.payments')" class="surface-card inline-flex w-fit items-center gap-2 px-5 py-4 font-display text-lg font-bold uppercase">
                    <Banknote class="size-5 shrink-0" aria-hidden="true" />
                    Solicitações de pagamento
                </Link>
                <Link :href="route('admin.foods')" class="surface-card inline-flex w-fit items-center gap-2 px-5 py-4 font-display text-lg font-bold uppercase">
                    <Utensils class="size-5 shrink-0" aria-hidden="true" />
                    Alimentos
                </Link>
            </div>
            <PaymentOverview :items="paymentOverview" :daily-payments="dailyPayments" />
        </div>
    </AppLayout>
</template>
