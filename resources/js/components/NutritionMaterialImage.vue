<script setup lang="ts">
defineProps<{
    src: string;
    alt: string;
    panel?: 'left' | 'right';
}>();
</script>

<template>
    <svg
        v-if="panel"
        :viewBox="panel === 'left' ? '0 0 768 1024' : '768 0 768 1024'"
        width="768"
        height="1024"
        role="img"
        :aria-label="alt || undefined"
        :aria-hidden="!alt ? true : undefined"
        overflow="hidden"
    >
        <image :href="src" width="1536" height="1024" />
    </svg>
    <img v-else :src="src" :alt="alt" loading="lazy" />
</template>
