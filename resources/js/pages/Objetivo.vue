<script setup lang="ts">
import GoalContent from '@/components/GoalContent.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { GOAL_DETAILS, type GoalKey } from '@/data/goalDetails';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Instagram, MessageCircle, Play } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    goal: GoalKey;
    skillCatalog?: { title: string; items: string[] }[];
}>();

const item = computed(() => GOAL_DETAILS.find((detail) => detail.key === props.goal) ?? GOAL_DETAILS[0]);

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: item.value.nav, href: '#' },
    { title: 'Objetivo', href: route('meus-objetivos.show', props.goal) },
]);
</script>

<template>
    <Head :title="`Objetivo — ${item.title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <GoalContent :item="item" :skill-catalog="skillCatalog" />
            <section v-if="goal === 'treino'" class="surface-card w-full max-w-[900px] border-brand-gold/30 bg-gradient-to-br from-brand-gold/10 to-transparent p-5 sm:p-7" aria-labelledby="personal-card-title">
                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:gap-6">
                    <img src="/images/thales-araujo.jpg" alt="Thales Araújo" width="150" height="150" loading="lazy" class="size-24 shrink-0 rounded-full border-2 border-brand-gold object-cover sm:size-32" />
                    <div>
                        <p class="text-sm font-semibold text-brand-gold">Thales Araújo · Personal trainer</p>
                        <h2 id="personal-card-title" class="mt-2 text-2xl font-bold sm:text-3xl">Consultoria de treino presencial e online</h2>
                        <p class="mt-3 max-w-xl text-sm leading-relaxed text-brand-muted sm:text-base">
                            Planejamento de treinos e acompanhamento individual com foco em ganho de massa muscular e qualidade de vida.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="https://wa.me/5561982645740" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-brand-gold px-4 py-2.5 text-sm font-bold text-brand-ink">
                        <MessageCircle class="size-4" aria-hidden="true" />
                        Falar pelo WhatsApp
                    </a>
                    <a href="https://www.instagram.com/thalesaraujopersonal?rpxt=MXh5NXJ3eHBkbHNvdQ==" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg border border-brand-line2 bg-brand-card2 px-4 py-2.5 text-sm font-semibold" aria-label="Instagram de Thales Araújo">
                        <Instagram class="size-4" aria-hidden="true" />
                        @thalesaraujopersonal
                    </a>
                    <Dialog>
                        <DialogTrigger as-child>
                            <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-brand-line2 bg-brand-card2 px-4 py-2.5 text-sm font-semibold">
                                <Play class="size-4" aria-hidden="true" />
                                Assistir ao vídeo
                            </button>
                        </DialogTrigger>
                        <DialogContent class="max-h-[90dvh] overflow-y-auto border-brand-line2 bg-brand-card text-brand-cream sm:max-w-sm">
                            <DialogHeader>
                                <DialogTitle>Consultoria com Thales Araújo</DialogTitle>
                                <DialogDescription>Apresentação dos treinos presenciais e online.</DialogDescription>
                            </DialogHeader>
                            <video controls playsinline preload="metadata" class="max-h-[65dvh] w-full rounded-xl bg-black" aria-label="Vídeo de apresentação da consultoria de treino">
                                <source src="/videos/personal.mp4" type="video/mp4" />
                                <a href="/videos/personal.mp4">Abrir o vídeo da consultoria</a>
                            </video>
                        </DialogContent>
                    </Dialog>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
