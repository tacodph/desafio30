<script setup lang="ts">
defineProps<{ src: string; alt: string; number: number }>();
</script>

<template>
    <svg viewBox="0 0 1000 1020" width="1000" height="1020" role="img" :aria-label="`${number}. ${alt}`" overflow="hidden">
        <image :href="src" width="1000" height="940" preserveAspectRatio="xMidYMid slice" />
        <circle cx="500" cy="910" r="102" fill="#ffd000" />
        <text x="500" y="910" text-anchor="middle" dominant-baseline="central" font-family="Arial, sans-serif" font-size="148" font-weight="900" fill="#101010">{{ number }}</text>
    </svg>
</template>
