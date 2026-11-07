<script setup lang="ts">
import GoalContent from '@/components/GoalContent.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { GOAL_DETAILS, type GoalKey } from '@/data/goalDetails';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
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
            <Dialog v-if="goal === 'treino'">
                <DialogTrigger as-child>
                    <Button class="h-auto w-fit max-w-full whitespace-normal text-left">
                        Clique aqui para ver opções de treino e consultoria com personal
                    </Button>
                </DialogTrigger>
                <DialogContent class="max-h-[90dvh] max-w-lg overflow-y-auto border-brand-line2 bg-brand-card text-brand-cream">
                    <DialogHeader>
                        <DialogTitle>Rafael Coelho · Personal trainer</DialogTitle>
                        <DialogDescription class="text-brand-muted">Atendimento presencial.</DialogDescription>
                    </DialogHeader>
                    <img
                        src="/images/rafael-coelho-personal.jpg"
                        alt="Material de Rafael Coelho, personal trainer com atendimento presencial"
                        width="1024"
                        height="1536"
                        class="h-auto w-full rounded-lg"
                    />
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
