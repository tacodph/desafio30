<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center">
        <AppLogoIcon class="size-8" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="font-display mb-0.5 truncate text-base font-extrabold italic uppercase leading-none tracking-wide">
            Desafio <span class="text-brand-gold">da Virada</span>
        </span>
    </div>
</template>
