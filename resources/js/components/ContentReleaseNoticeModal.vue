<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import type { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';

const page = usePage<SharedData>();
const open = ref(false);
const claiming = ref(false);
const pages = ref<{ title: string; href: string }[]>([]);
const confetti = ref<Record<string, string>[]>([]);
let cleanup: ReturnType<typeof setTimeout> | undefined;

async function showNotice() {
    const notice = page.props.contentReleaseNotice;
    if (!notice || open.value || claiming.value || page.props.showReleaseWelcome === true) return;
    claiming.value = true;
    try {
        const response = await fetch('/atleta/aviso-conteudos-liberados', {
            method: 'POST', credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': notice.token },
        });
        if (!response.ok) return;
        const result = await response.json();
        if (!result.show) return;
        pages.value = result.pages;
        open.value = true;
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            const colors = ['#f6a821', '#fb923c', '#f472b6', '#34d399', '#60a5fa'];
            confetti.value = Array.from({ length: 72 }, (_, i) => ({
                left: `${Math.random() * 100}%`, backgroundColor: colors[i % colors.length],
                animationDelay: `${Math.random() * .5}s`, animationDuration: `${2 + Math.random() * 1.5}s`,
                '--drift': `${Math.random() * 180 - 90}px`,
            }));
            cleanup = setTimeout(() => { confetti.value = []; }, 4500);
        }
    } catch { /* A próxima navegação ou login permite tentar novamente. */ }
    finally { claiming.value = false; }
}

function close() {
    open.value = false;
    confetti.value = [];
    router.reload({ only: ['contentReleaseNotice'] });
}
function visit(href: string) {
    open.value = false;
    confetti.value = [];
    router.visit(href);
}
watch(() => [page.props.contentReleaseNotice, page.props.showReleaseWelcome], () => { void showNotice(); }, { immediate: true });
onBeforeUnmount(() => clearTimeout(cleanup));
</script>

<template>
    <Dialog :open="open" @update:open="(value) => { if (!value) close(); }">
        <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-xl overflow-y-auto rounded-2xl border-brand-gold/40 bg-brand-card p-5 text-brand-cream sm:p-8">
            <DialogHeader>
                <DialogTitle class="pr-6 text-2xl font-black text-brand-gold">🎉 Novos conteúdos liberados para você!</DialogTitle>
                <DialogDescription class="text-base leading-relaxed text-brand-cream">As páginas abaixo já estão disponíveis. Confira as ideias e aproveite para variar suas refeições! 🧡</DialogDescription>
            </DialogHeader>
            <ul class="space-y-3">
                <li v-for="item in pages" :key="item.href">
                    <button type="button" class="w-full rounded-xl border border-brand-gold/40 bg-brand-gold/10 p-4 text-left font-bold text-brand-gold hover:bg-brand-gold/20" @click="visit(item.href)">{{ item.title }} →</button>
                </li>
            </ul>
            <p class="text-sm leading-relaxed text-brand-muted">Explore as sugestões, escolha suas combinações favoritas e organize sua lista de compras da semana.</p>
            <Button class="w-full" @click="close">Entendi, vou conferir! 🧡</Button>
        </DialogContent>
    </Dialog>
    <Teleport to="body">
        <div v-if="open && confetti.length" class="pointer-events-none fixed inset-0 z-[70] overflow-hidden" aria-hidden="true">
            <span v-for="(style, index) in confetti" :key="index" class="content-confetti" :style="style" />
        </div>
    </Teleport>
</template>

<style scoped>
.content-confetti { position: absolute; top: -1rem; width: .5rem; height: .75rem; animation: content-confetti-fall linear forwards; }
@keyframes content-confetti-fall { to { transform: translate(var(--drift), calc(100dvh + 2rem)) rotate(720deg); opacity: 0; } }
@media (prefers-reduced-motion: reduce) { .content-confetti { display: none; } }
</style>
