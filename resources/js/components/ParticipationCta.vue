<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex w-full">
        <Link
            :href="route('participar')"
            class="inline-flex min-h-12 w-full max-w-xs items-center justify-center gap-3 rounded-full bg-brand-gold px-8 py-4 text-[17px] font-bold text-brand-ink transition-colors hover:bg-brand-gold-hover focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-gold sm:w-auto"
        >
            Participar <span aria-hidden="true">→</span>
        </Link>
    </div>
</template>