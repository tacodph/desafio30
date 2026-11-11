<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const page = usePage<SharedData>();
const current = ref<{ id: number; day: number } | null>(null);
const claiming = ref(false);
const handled = new Set<number>();

const showNext = async () => {
    if (current.value || claiming.value || page.props.showReleaseWelcome === true) return;
    const notice = page.props.mealCommentNotices;
    const checkin = notice?.checkins.find((item) => !handled.has(item.id));
    if (!notice || !checkin) return;

    claiming.value = true;
    try {
        const response = await fetch(route('athlete.meal-comment-notice', checkin.id), {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': notice.token },
        });
        if (!response.ok) return;
        const result = await response.json();
        handled.add(checkin.id);
        if (result.show) current.value = checkin;
    } catch {
        return;
    } finally {
        claiming.value = false;
    }
    if (!current.value) void showNext();
};

const close = () => {
    current.value = null;
    void showNext();
};

watch(() => [page.props.mealCommentNotices, page.props.showReleaseWelcome], () => { void showNext(); }, { immediate: true });
</script>

<template>
    <Dialog :open="Boolean(current)" @update:open="!$event && close()">
        <DialogContent class="max-h-[90dvh] overflow-y-auto rounded-2xl border-brand-gold/30 bg-brand-card p-6 text-brand-cream sm:max-w-lg">
            <DialogHeader>
                <DialogTitle class="pr-6 text-2xl leading-snug text-brand-gold">❤️ A Nutri curtiu sua refeição!</DialogTitle>
                <DialogDescription class="text-brand-muted">A nutri comentou na sua refeição do check-in do dia {{ current?.day }}.</DialogDescription>
            </DialogHeader>
            <div class="space-y-4 text-base leading-relaxed">
                <p>Muito bem! Cada escolha, cada registro e cada pequena conquista fazem parte do processo.</p>
                <p>Continue firme! Estou de olho na sua evolução. 🚀</p>
            </div>
            <Button class="mt-2 w-full" @click="close">Entendi ❤️</Button>
        </DialogContent>
    </Dialog>
</template>
