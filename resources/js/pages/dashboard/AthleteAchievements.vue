<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ChevronLeft, ChevronRight, Eye, MessageSquare, Pencil } from 'lucide-vue-next';

const title = 'Conquistas dos atletas';

type AchievementRecord = {
    id: number; detail: string; date: string; day: number;
    comment: string | null; emotion: string | null; nutritionist: string | null;
};

defineProps<{
    athletes: {
        data: { id: number; name: string; achievements_count: number; records: AchievementRecord[] }[];
        total: number;
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    categories: Record<string, string>;
    category: string;
    canViewProgress: boolean;
}>();

const commenting = ref<{ record: AchievementRecord; athlete: string } | null>(null);
const form = useForm({ comment: '', emotion: '' });
const emotions = ['👏', '🔥', '💪', '❤️', '⭐', '😍'];
const openComment = (record: AchievementRecord, athlete: string) => {
    form.comment = record.comment ?? '';
    form.emotion = record.emotion ?? '';
    form.clearErrors();
    commenting.value = { record, athlete };
};
const saveComment = () => {
    if (!commenting.value || form.processing) return;
    form.put(route('athlete.achievements.comment', commenting.value.record.id), {
        preserveScroll: true,
        onSuccess: () => { commenting.value = null; },
    });
};
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Painel', href: '/dashboard' }, { title, href: '/equipe/conquistas' }]">
        <main class="mx-auto flex w-full max-w-5xl flex-col gap-5 p-4 md:p-6">
            <h1 class="display-title text-4xl text-brand-gold">{{ title }}</h1>
            <nav class="flex flex-wrap gap-2" aria-label="Categorias de conquistas">
                <Button v-for="(label, key) in categories" :key="key" as-child :variant="category === key ? 'default' : 'outline'" class="h-auto min-h-9 whitespace-normal text-center">
                    <Link :href="route('equipe.achievements', { categoria: key })" :aria-current="category === key ? 'page' : undefined">{{ label }}</Link>
                </Button>
            </nav>
            <p class="text-brand-muted">{{ athletes.total }} {{ athletes.total === 1 ? 'atleta com registros nesta categoria' : 'atletas com registros nesta categoria' }}</p>
            <ul v-if="athletes.data.length" class="grid gap-4">
                <li v-for="athlete in athletes.data" :key="athlete.id" class="surface-card space-y-4 p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="min-w-0 break-words font-bold">{{ athlete.name }}</h2>
                        <span class="text-sm font-bold text-brand-gold">{{ athlete.achievements_count }} {{ athlete.achievements_count === 1 ? 'registro' : 'registros' }}</span>
                    </div>
                    <ul class="grid gap-3">
                        <li v-for="record in athlete.records" :key="record.id" class="space-y-3 rounded-xl border border-brand-line p-4">
                            <p class="text-sm font-bold text-brand-muted">{{ record.date }}</p>
                            <p class="whitespace-pre-line break-words">{{ record.detail }}</p>
                            <div v-if="record.comment" class="space-y-1 rounded-lg bg-brand-card2 p-3 text-sm">
                                <p class="font-semibold text-brand-gold">{{ record.emotion }} {{ record.nutritionist ?? 'Nutri' }}</p>
                                <p class="whitespace-pre-line break-words">{{ record.comment }}</p>
                            </div>
                            <div v-if="canViewProgress" class="flex flex-wrap items-center gap-2">
                                <Button type="button" :variant="record.comment ? 'secondary' : 'default'" @click="openComment(record, athlete.name)">
                                    <component :is="record.comment ? Pencil : MessageSquare" aria-hidden="true" />
                                    {{ record.comment ? 'Editar comentário' : 'Comentar conquista' }}
                                </Button>
                                <Button as-child variant="outline">
                                    <Link :href="route('equipe.athlete.progress', { athlete: athlete.id, dia: record.day })"><Eye aria-hidden="true" />Ver evolução do dia</Link>
                                </Button>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
            <p v-else class="surface-card p-5 text-brand-muted">Nenhum atleta registrou conquistas nesta categoria até agora.</p>
            <nav v-if="athletes.last_page > 1" class="flex flex-wrap items-center gap-4 text-sm" aria-label="Paginação dos atletas">
                <Button v-if="athletes.prev_page_url" as-child variant="outline" size="sm">
                    <Link :href="athletes.prev_page_url"><ChevronLeft aria-hidden="true" />Anterior</Link>
                </Button>
                <span>Página {{ athletes.current_page }} de {{ athletes.last_page }}</span>
                <Button v-if="athletes.next_page_url" as-child variant="outline" size="sm">
                    <Link :href="athletes.next_page_url">Próxima<ChevronRight aria-hidden="true" /></Link>
                </Button>
            </nav>
        </main>
        <Dialog :open="Boolean(commenting)" @update:open="!$event && !form.processing && (commenting = null)">
            <DialogContent class="max-h-[90dvh] overflow-y-auto border-brand-line2 bg-brand-card text-brand-cream">
                <DialogHeader>
                    <DialogTitle>Comentar conquista</DialogTitle>
                    <DialogDescription>{{ commenting?.athlete }} · {{ commenting?.record.date }}</DialogDescription>
                </DialogHeader>
                <p class="whitespace-pre-line break-words text-sm">{{ commenting?.record.detail }}</p>
                <form class="grid gap-3" @submit.prevent="saveComment">
                    <div class="flex flex-wrap gap-2" role="group" aria-label="Reação à conquista">
                        <button v-for="emotion in emotions" :key="emotion" type="button" :disabled="form.processing" :aria-label="`Reagir com ${emotion}`" :aria-pressed="form.emotion === emotion" class="grid size-10 place-items-center rounded-lg border text-xl" :class="form.emotion === emotion ? 'border-brand-gold bg-brand-gold/20' : 'border-brand-line2'" @click="form.emotion = emotion">{{ emotion }}</button>
                    </div>
                    <InputError :message="form.errors.emotion" />
                    <label for="achievement-comment" class="text-sm font-bold">Comentário da nutri</label>
                    <textarea id="achievement-comment" v-model="form.comment" required maxlength="500" rows="4" :disabled="form.processing" class="w-full rounded-xl border border-brand-line2 bg-brand-card2 p-3 text-sm" />
                    <InputError :message="form.errors.comment" />
                    <Button type="submit" :disabled="form.processing || !form.comment.trim() || !form.emotion">{{ form.processing ? 'Salvando…' : 'Salvar comentário' }}</Button>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
