<script setup lang="ts">
import guideImage from '@/assets/alimentacao/lidando-com-o-fim-de-semana.png';
import WeekendImpact from '@/components/WeekendImpact.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { CakeSlice, CalendarDays, ClipboardList, Coffee, Dessert, Maximize2, Target, Users, UtensilsCrossed } from 'lucide-vue-next';

const tips = [
    { title: 'Escolha suas refeições livres.', text: 'Defina, por exemplo, 1 ou 2 na semana, de acordo com a sua vida social.', icon: CalendarDays },
    { title: 'Observe seus eventos.', text: 'Veja o que você tem de compromissos no fim de semana e se organize para encaixar suas escolhas.', icon: Users },
    { title: 'Nem toda oportunidade precisa ser aproveitada.', text: 'Algumas vezes, você vai precisar abrir mão de uma oferta ou de um ambiente que não te ajuda a manter o foco.', icon: UtensilsCrossed },
    { title: 'Saia do automático.', text: 'Se policie quanto às escolhas quando estiver em um bar, restaurante ou na casa de alguém. Observe a quantidade, o volume e as combinações. Evite concentrar muitos alimentos calóricos no mesmo prato.', icon: Target },
    { title: 'Pense na sobremesa.', text: 'Às vezes, vale abrir mão, principalmente se você já tem uma festa ou outro evento especial na agenda.', icon: Dessert },
    { title: 'Em festas, coffee break ou eventos.', text: 'Dê uma olhada no que tem, monte um pratinho com o que realmente tem vontade e evite ficar ao lado da mesa, para não beliscar o tempo todo.', icon: Coffee },
    { title: 'Planeje o seu dia.', text: 'Se você sabe que terá uma refeição livre à noite, mantenha o restante do dia com refeições mais leves e equilibradas.', icon: ClipboardList },
    { title: 'Priorize o que tem valor para você.', text: 'Evite doces e ultraprocessados do dia a dia, como bolos de padaria, e reserve esse espaço para alimentos que tenham um sentido afetivo, como o bolo da sua avó ou uma comida especial em família.', icon: CakeSlice },
];
</script>

<template>
    <Head title="Lidando com o fim de semana" />
    <AppLayout :breadcrumbs="[{ title: 'Alimentação', href: '/meus-objetivos/alimentacao' }, { title: 'Lidando com o fim de semana', href: '/alimentacao/lidando-com-o-fim-de-semana' }]">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-2">
                <div class="eyebrow">Alimentação</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] leading-tight text-brand-gold">Lidando com o fim de semana</h1>
            </header>
            <WeekendImpact />
            <section class="space-y-5 rounded-3xl border-2 border-yellow-400 bg-[#101010] p-4 text-black sm:p-6" aria-labelledby="weekend-heading">
                <header class="flex flex-col items-center gap-5 sm:flex-row">
                    <img src="/images/logo-mark-30-virada.png" alt="Desafio da Virada — 30 dias" width="100" height="100" class="size-24 shrink-0 object-contain" />
                    <div class="hidden h-20 w-px bg-white/60 sm:block" aria-hidden="true" />
                    <h2 id="weekend-heading" class="w-full rounded-3xl bg-yellow-400 px-5 py-4 text-center font-display text-3xl font-black italic uppercase leading-tight sm:text-4xl">Como fazer para dar certo?</h2>
                </header>
                <div class="grid items-stretch gap-3 md:grid-cols-2">
                    <article v-for="tip in tips" :key="tip.title" class="flex items-start gap-4 rounded-3xl bg-[#faf5e7] p-4 sm:items-center">
                        <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-yellow-400 sm:size-20">
                            <component :is="tip.icon" class="size-10 stroke-[2.25] sm:size-12" aria-hidden="true" />
                        </div>
                        <div class="min-w-0 space-y-2">
                            <h3 class="font-display text-xl font-black leading-tight sm:text-2xl">{{ tip.title }}</h3>
                            <p class="text-sm font-medium leading-relaxed sm:text-base">{{ tip.text }}</p>
                        </div>
                    </article>
                </div>
            </section>
            <Dialog>
                <DialogTrigger as-child>
                    <button type="button" class="flex w-fit items-center gap-2 rounded-xl border border-brand-gold/40 bg-brand-gold/10 px-4 py-3 text-sm font-bold text-brand-gold hover:bg-brand-gold/20"><Maximize2 class="size-4" aria-hidden="true" />Ver material original</button>
                </DialogTrigger>
                <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-6xl overflow-y-auto rounded-2xl border-brand-line2 bg-brand-card text-brand-cream">
                    <DialogHeader><DialogTitle>Lidando com o fim de semana</DialogTitle><DialogDescription>Como fazer para dar certo? Oito orientações para organizar suas escolhas.</DialogDescription></DialogHeader>
                    <img :src="guideImage" alt="Material original com oito orientações para lidar com as escolhas alimentares no fim de semana." class="h-auto w-full rounded-xl" />
                </DialogContent>
            </Dialog>
        </main>
    </AppLayout>
</template>
