<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-brand-ink p-6 md:p-10">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link :href="route('home')" class="flex flex-col items-center gap-3 font-medium">
                        <AppLogoIcon class="size-16" />
                        <span class="font-display text-[22px] font-extrabold italic uppercase tracking-wide text-brand-cream">
                            Desafio <span class="text-brand-gold">30</span> Dias
                        </span>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="display-title text-[32px] leading-none">{{ title }}</h1>
                        <p class="text-center text-sm text-brand-muted">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
