<script setup lang="ts">
import PaymentOverview, { type DailyPayment, type PaymentOverviewItem } from '@/components/PaymentOverview.vue';
import MealCommentForm from '@/components/MealCommentForm.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronDown, ClipboardCheck, Eye, Salad, Users } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Equipe', href: '/dashboard' }];

defineProps<{
    athleteCount: number;
    dailyPayments: DailyPayment[] | null;
    paymentOverview: PaymentOverviewItem[] | null;
    checkinOverview: {
        total: number;
        today: number;
        athletes: number;
        pending: number;
        meals: {
            id: number; athleteId: number; athlete: string; day: number; date: string;
            comment: string | null; reaction: string | null; photos: string[];
        }[];
    } | null;
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

            <Button as-child variant="outline" class="w-fit">
                <Link :href="route('equipe.athletes')"><Users aria-hidden="true" />Ver atletas</Link>
            </Button>
            <section v-if="checkinOverview" class="surface-card space-y-5 p-5 sm:p-6" aria-labelledby="checkin-overview-title">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 id="checkin-overview-title" class="text-xl font-bold">Check-ins dos atletas</h2>
                    <Button as-child variant="outline" class="h-auto min-h-9 whitespace-normal text-center">
                        <Link :href="route('equipe.checkins')"><ClipboardCheck aria-hidden="true" />Ver todos os check-ins</Link>
                    </Button>
                </div>
                <dl class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div v-for="item in [
                        { label: 'Check-ins de hoje', value: checkinOverview.today },
                        { label: 'Check-ins registrados', value: checkinOverview.total },
                        { label: 'Atletas com check-in', value: checkinOverview.athletes },
                        { label: 'Refeições sem comentário', value: checkinOverview.pending },
                    ]" :key="item.label" class="rounded-xl border border-brand-line2 p-3">
                        <dt class="text-sm text-brand-muted">{{ item.label }}</dt>
                        <dd class="mt-1 text-3xl font-bold tabular-nums text-brand-gold">{{ item.value }}</dd>
                    </div>
                </dl>
                <div class="space-y-1">
                    <h3 class="font-semibold">Refeições aguardando comentário</h3>
                    <p v-if="checkinOverview.pending" class="text-sm text-brand-muted">Até 8 registros, começando pelos dias mais antigos.</p>
                    <p v-else class="text-sm text-brand-muted">Todas as refeições com foto já receberam comentário.</p>
                </div>
                <div v-if="checkinOverview.meals.length" class="divide-y divide-brand-line">
                    <article v-for="meal in checkinOverview.meals" :key="meal.id" class="space-y-3 py-4 first:pt-0">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h4 class="break-words font-bold">{{ meal.athlete }}</h4>
                                <p class="text-sm text-brand-muted">Dia {{ meal.day }} · {{ meal.date }} · {{ meal.photos.length }} {{ meal.photos.length === 1 ? 'foto' : 'fotos' }}</p>
                            </div>
                            <Button as-child variant="outline" size="sm">
                                <Link :href="route('equipe.athlete.progress', { athlete: meal.athleteId, dia: meal.day })"><Eye aria-hidden="true" />Ver check-in</Link>
                            </Button>
                        </div>
                        <details class="group rounded-xl border border-brand-line2 p-3">
                            <summary :class="buttonVariants()" class="w-fit cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                Ver fotos e comentar
                                <ChevronDown class="transition-transform group-open:rotate-180" aria-hidden="true" />
                            </summary>
                            <div class="mt-4 grid items-start gap-4 md:grid-cols-2">
                                <div class="flex flex-wrap gap-2">
                                    <a v-for="(photo, index) in meal.photos" :key="photo" :href="photo" target="_blank" rel="noopener noreferrer" :aria-label="`Ampliar foto ${index + 1} da refeição de ${meal.athlete}`">
                                        <img :src="photo" :alt="`Refeição de ${meal.athlete}, foto ${index + 1}`" loading="lazy" class="size-24 rounded-lg border border-brand-line2 bg-brand-card2 object-contain sm:size-32" />
                                    </a>
                                </div>
                                <MealCommentForm :athlete-id="meal.athleteId" :day="meal.day" :comment="meal.comment" :reaction="meal.reaction" />
                            </div>
                        </details>
                    </article>
                </div>
                <Button as-child variant="secondary" class="h-auto min-h-9 max-w-full whitespace-normal text-center">
                    <Link :href="route('equipe.meals')"><Salad aria-hidden="true" />Ver alimentação de todos os atletas</Link>
                </Button>
            </section>
            <PaymentOverview v-if="paymentOverview" :items="paymentOverview" :daily-payments="dailyPayments" />
        </div>
    </AppLayout>
</template>
