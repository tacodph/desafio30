<script setup lang="ts">
import PaymentOverview, { type DailyPayment, type PaymentOverviewItem } from '@/components/PaymentOverview.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Equipe', href: '/dashboard' }];

defineProps<{
    athleteCount: number;
    dailyPayments: DailyPayment[] | null;
    paymentOverview: PaymentOverviewItem[] | null;
}>();
</script>

<template>
    <Head title="Equipe" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-3">
                <div class="eyebrow">Equipe</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)]">Acompanhe os atletas</h1>
                <p class="max-w-xl text-brand-muted">Nutrição, treino e divulgação usam este painel para ver quem está no desafio.</p>
            </div>

            <div class="surface-card flex w-fit flex-col gap-2 p-6">
                <span class="text-[13px] font-bold uppercase tracking-[2px] text-brand-muted-2">Atletas</span>
                <span class="font-display text-6xl font-black tabular-nums">{{ athleteCount }}</span>
            </div>

            <Link :href="route('equipe.athletes')" class="surface-card w-fit px-5 py-4 font-display text-lg font-bold uppercase">
                Ver atletas
            </Link>
            <PaymentOverview v-if="paymentOverview" :items="paymentOverview" :daily-payments="dailyPayments" />
        </div>
    </AppLayout>
</template>
