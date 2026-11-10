<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    athleteId?: number | null;
    day: number;
    target: string;
    reaction?: string | null;
    label: string;
}>();

const choices = [
    { emoji: '❤️', label: 'Curtir' },
    { emoji: '👏', label: 'Aplaudir' },
    { emoji: '🙌', label: 'Celebrar' },
    { emoji: '🔥', label: 'Mandou bem' },
];
const form = useForm({ target: props.target, reaction: null as string | null });

function react(emoji: string) {
    if (!props.athleteId || form.processing) return;
    form.target = props.target;
    form.reaction = props.reaction === emoji ? null : emoji;
    form.put(route('equipe.athlete.checkin.reaction', { athlete: props.athleteId, day: props.day }), { preserveScroll: true });
}
</script>

<template>
    <div v-if="athleteId" class="space-y-1">
        <div class="flex flex-wrap gap-1" role="group" :aria-label="label">
            <button v-for="choice in choices" :key="choice.emoji" type="button" :disabled="form.processing" :aria-label="`${choice.label} · ${label}`" :title="choice.label"
                :aria-pressed="reaction === choice.emoji" class="grid size-8 place-items-center rounded-lg border text-sm disabled:opacity-60"
                :class="reaction === choice.emoji ? 'border-brand-gold bg-brand-gold/20' : 'border-brand-line2 bg-brand-card2'" @click="react(choice.emoji)">
                {{ choice.emoji }}
            </button>
        </div>
        <InputError :message="form.errors.reaction || form.errors.target" />
    </div>
    <p v-else-if="reaction" class="text-sm font-semibold">Reação da nutri {{ reaction }}</p>
</template>
