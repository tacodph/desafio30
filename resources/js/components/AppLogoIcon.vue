<script setup lang="ts">
import { images } from '@/data/images';
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        :src="images.logo"
        alt="Desafio 30 Dias"
        :class="className"
        class="object-contain"
        v-bind="$attrs"
    />
</template>
