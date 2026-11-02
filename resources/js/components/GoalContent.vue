<script setup lang="ts">
import { type GoalDetail } from '@/data/goalDetails';
import { SKILL_OBJECTIVE } from '@/data/skillIdeas';

defineProps<{
    item: GoalDetail;
    skillCatalog?: { title: string; items: string[] }[];
}>();
</script>

<template>
    <div class="flex flex-col gap-10">
        <div class="flex flex-col gap-10">
            <div class="flex flex-col gap-5">
                <img :src="item.icon" alt="" class="size-[88px]" />
                <div v-if="item.pillar && item.key !== 'alimentacao'" class="text-sm font-bold uppercase tracking-[3px]" :style="{ color: item.color }">{{ item.pillar }}</div>
                <h2 class="display-title m-0 text-[clamp(40px,5vw,72px)]">{{ item.title }}</h2>
                <p v-if="item.key !== 'alimentacao'" class="font-display text-[clamp(32px,4vw,48px)] font-extrabold leading-none" :style="{ color: item.color }">
                    {{ item.metric }}
                </p>
                <p v-if="item.desc" class="m-0 max-w-[640px] text-[17px] leading-relaxed text-brand-muted">{{ item.desc }}</p>
            </div>

            <div class="flex max-w-[900px] flex-col gap-4">
                <div class="surface-card flex flex-col gap-3 p-7">
                    <span class="text-[13px] font-bold uppercase tracking-[2px]" :style="{ color: item.color }">A regra</span>
                    <ul class="m-0 flex list-none flex-col gap-3 p-0">
                        <li v-for="line in item.rule" :key="line" class="text-[16px] leading-relaxed text-brand-muted-3">{{ line }}</li>
                    </ul>
                </div>
                <div class="surface-card flex flex-col gap-3 p-7">
                    <span class="text-[13px] font-bold uppercase tracking-[2px] text-brand-gold">Por que funciona?</span>
                    <ul class="m-0 flex list-none flex-col gap-3 p-0">
                        <li v-for="line in item.why" :key="line" class="text-[16px] leading-relaxed text-brand-muted-3">{{ line }}</li>
                    </ul>
                </div>
                <div v-if="item.tips.length" class="surface-card flex flex-col gap-3 p-7">
                    <span class="text-[13px] font-bold uppercase tracking-[2px] text-brand-muted-2">{{ ['hidratacao', 'plus'].includes(item.key) ? 'O que você encontra no desafio!' : ['alimentacao', 'treino', 'habilidade'].includes(item.key) ? 'O que você encontra no desafio' : 'Dicas práticas' }}</span>
                    <ul class="m-0 flex list-none flex-col gap-3 p-0">
                        <li v-for="line in item.tips" :key="line" class="text-[16px] leading-relaxed text-brand-muted-3">{{ line }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div v-if="item.key === 'habilidade'" class="flex flex-col gap-6">
            <div class="flex flex-col gap-3">
                <span class="text-[13px] font-bold uppercase tracking-[2px]" :style="{ color: item.color }">Ideias para o desafio</span>
                <p class="m-0 max-w-[720px] text-[17px] leading-relaxed text-brand-muted">{{ SKILL_OBJECTIVE }}</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <article v-for="group in skillCatalog ?? []" :key="group.title" class="surface-card flex flex-col gap-3 p-7">
                    <h3 class="font-display m-0 text-2xl font-extrabold uppercase" :style="{ color: item.color }">{{ group.title }}</h3>
                    <ul class="m-0 flex list-none flex-col gap-3 p-0">
                        <li v-for="idea in group.items" :key="idea" class="text-[16px] leading-relaxed text-brand-muted-3">{{ idea }}</li>
                    </ul>
                </article>
            </div>
        </div>
    </div>
</template>
