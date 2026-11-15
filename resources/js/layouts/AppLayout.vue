<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import ReleaseWelcomeModal from '@/components/ReleaseWelcomeModal.vue';
import MealCommentNoticeModal from '@/components/MealCommentNoticeModal.vue';
import ContentReleaseNoticeModal from '@/components/ContentReleaseNoticeModal.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
        <ReleaseWelcomeModal />
        <ContentReleaseNoticeModal />
        <MealCommentNoticeModal />
    </AppLayout>
</template>
