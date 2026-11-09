<script setup lang="ts">
import GoalContent from '@/components/GoalContent.vue';
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
        </div>
    </AppLayout>
</template>
