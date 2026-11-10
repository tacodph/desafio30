<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{ athleteId: number; day: number; comment: string | null; reaction: string | null }>();
const form = useForm({ comment: props.comment ?? '' });
const reactionForm = useForm({ reaction: null as string | null });
const reactions = [
    { emoji: '❤️', label: 'Curtir' },
    { emoji: '👏', label: 'Aplaudir' },
    { emoji: '🙌', label: 'Celebrar' },
    { emoji: '🔥', label: 'Mandou bem' },
];

watch(() => props.comment, (comment) => { form.comment = comment ?? ''; });

const save = () => form.put(route('equipe.athlete.meal.comment', { athlete: props.athleteId, day: props.day }), { preserveScroll: true });
const react = (emoji: string) => {
    reactionForm.reaction = props.reaction === emoji ? null : emoji;
    reactionForm.put(route('equipe.athlete.meal.reaction', { athlete: props.athleteId, day: props.day }), { preserveScroll: true });
};
</script>

<template>
    <form class="grid min-w-0 gap-2" @submit.prevent="save">
        <div class="flex flex-wrap gap-2" role="group" aria-label="Reagir à refeição">
            <button v-for="item in reactions" :key="item.emoji" type="button" :disabled="reactionForm.processing || form.processing"
                :aria-label="item.label" :title="item.label" :aria-pressed="reaction === item.emoji"
                class="grid size-10 place-items-center rounded-xl border text-lg disabled:opacity-60"
                :class="reaction === item.emoji ? 'border-brand-gold bg-brand-gold/20' : 'border-brand-line2 bg-brand-card2'" @click="react(item.emoji)">
                {{ item.emoji }}
            </button>
        </div>
        <InputError :message="reactionForm.errors.reaction" />
        <label :for="`meal-comment-${athleteId}-${day}`" class="text-sm font-bold">Comentário da nutri</label>
        <textarea :id="`meal-comment-${athleteId}-${day}`" v-model="form.comment" rows="4" maxlength="500" required class="w-full rounded-xl border border-brand-line2 bg-brand-card2 p-3 text-sm" />
        <InputError :message="form.errors.comment" />
        <Button type="submit" class="w-fit" :disabled="form.processing || reactionForm.processing || !form.comment.trim()">{{ form.processing ? 'Salvando…' : 'Salvar comentário' }}</Button>
        <p v-if="form.recentlySuccessful" role="status" class="text-sm text-brand-gold">Comentário salvo.</p>
    </form>
</template>
