<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{ athleteId: number; day: number; comment: string | null }>();
const form = useForm({ comment: props.comment ?? '' });

watch(() => props.comment, (comment) => { form.comment = comment ?? ''; });

const save = () => form.put(route('equipe.athlete.meal.comment', { athlete: props.athleteId, day: props.day }), { preserveScroll: true });
</script>

<template>
    <form class="grid min-w-0 gap-2" @submit.prevent="save">
        <label :for="`meal-comment-${athleteId}-${day}`" class="text-sm font-bold">Comentário da nutri</label>
        <textarea :id="`meal-comment-${athleteId}-${day}`" v-model="form.comment" rows="4" maxlength="500" required class="w-full rounded-xl border border-brand-line2 bg-brand-card2 p-3 text-sm" />
        <InputError :message="form.errors.comment" />
        <Button type="submit" class="w-fit" :disabled="form.processing || !form.comment.trim()">{{ form.processing ? 'Salvando…' : 'Salvar comentário' }}</Button>
        <p v-if="form.recentlySuccessful" role="status" class="text-sm text-brand-gold">Comentário salvo.</p>
    </form>
</template>
