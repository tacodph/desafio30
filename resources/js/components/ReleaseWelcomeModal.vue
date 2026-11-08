<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = withDefaults(defineProps<{ preview?: boolean }>(), { preview: false });
const page = usePage();
const confirmed = ref(false);
const open = computed(() => (props.preview || page.props.showReleaseWelcome === true) && !confirmed.value);
const form = useForm({});
const error = ref('');
const confetti = ref<Record<string, string>[]>([]);
let cleanup: ReturnType<typeof setTimeout> | undefined;
const celebrate = () => {
    if (!open.value || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const colors = ['#f6a821', '#fb923c', '#f472b6', '#34d399', '#60a5fa'];
    confetti.value = Array.from({ length: 72 }, (_, index) => ({
        left: `${Math.random() * 100}%`,
        backgroundColor: colors[index % colors.length],
        animationDelay: `${Math.random() * 0.5}s`,
        animationDuration: `${2 + Math.random() * 1.5}s`,
        '--drift': `${Math.random() * 180 - 90}px`,
    }));
    clearTimeout(cleanup);
    cleanup = setTimeout(() => { confetti.value = []; }, 4500);
};
onMounted(celebrate);
watch(open, (value) => { if (value) celebrate(); });
onBeforeUnmount(() => clearTimeout(cleanup));
const acknowledge = () => {
    if (props.preview) {
        confirmed.value = true;
        confetti.value = [];
        return;
    }
    if (form.processing) return;
    error.value = '';
    form.post(route('athlete.release-welcome.acknowledge'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => { confirmed.value = true; confetti.value = []; },
        onError: () => { error.value = 'Não foi possível confirmar a leitura. Tente novamente.'; },
    });
};
const onOpenChange = (value: boolean) => { if (!value) acknowledge(); };
</script>

<template>
    <Dialog :open="open" @update:open="onOpenChange">
        <DialogContent class="max-h-[90dvh] w-[calc(100%_-_2rem)] max-w-2xl overflow-y-auto rounded-2xl border-brand-gold/30 bg-brand-card p-5 text-brand-cream sm:p-8">
            <DialogHeader>
                <DialogTitle class="pr-6 text-2xl leading-snug text-brand-gold">Oiiii! ❤️ Seja muito bem-vindo(a) ao <strong>Desafio da Virada!</strong> 🚀</DialogTitle>
                <DialogDescription class="text-base font-bold text-brand-cream">Sua plataforma já está liberada!</DialogDescription>
            </DialogHeader>
            <section aria-label="Vídeo de boas-vindas" class="space-y-3">
                <h2 class="font-bold text-brand-gold">Um recado da Helen para você ❤️</h2>
                <video v-if="open" controls playsinline preload="none" class="max-h-[55dvh] w-full rounded-xl bg-black" aria-label="Vídeo de boas-vindas ao Desafio da Virada">
                    <source src="/videos/boasVindas.mp4" type="video/mp4" />
                    Seu navegador não suporta a reprodução deste vídeo.
                    <a href="/videos/boasVindas.mp4">Abrir vídeo de boas-vindas</a>
                </video>
            </section>
            <div class="space-y-5 text-sm leading-relaxed sm:text-base">
                <p>Você já pode entrar, conhecer o ambiente do desafio, explorar os materiais de apoio e conferir tudo o que preparei para esses próximos 30 dias.</p>
                <p>A partir de amanhã, você já pode começar a realizar seus check-ins e registrar suas metas diariamente!</p>
                <section>
                    <h2 class="mb-3 font-bold text-brand-gold">Algumas dicas para começar:</h2>
                    <ul class="space-y-2">
                        <li>✨ Explore todos os materiais disponíveis.</li>
                        <li>✨ Conheça os objetivos e as metas do desafio.</li>
                        <li>✨ Organize sua rotina para conseguir colocar as metas em prática.</li>
                        <li>✨ Não deixe de fazer seus check-ins diariamente!</li>
                    </ul>
                </section>
                <p>E lembre-se: não estamos em busca de 30 dias de perfeição, mas de 30 dias de comprometimento. Se um dia não sair como planejado, no outro você tem uma nova oportunidade de fazer acontecer!</p>
                <p>Chame sua motivação, prepare-se e aproveite essa experiência!</p>
                <p class="font-bold text-brand-gold">Agora é pra valer! Que venham nossos 30 dias! 💃🔥</p>
                <p>Conte comigo nessa jornada!</p>
                <p class="font-bold">Helen Freitas Nutri ❤️</p>
            </div>
            <p v-if="error" role="alert" class="text-sm text-red-400">{{ error }}</p>
            <Button :disabled="form.processing" class="mt-2 w-full" @click="acknowledge">{{ form.processing ? 'Confirmando…' : 'Vamos fazer essa virada! 🚀' }}</Button>
        </DialogContent>
    </Dialog>
    <Teleport to="body">
        <div v-if="open && confetti.length" class="pointer-events-none fixed inset-0 z-[70] overflow-hidden" aria-hidden="true">
            <span v-for="(style, index) in confetti" :key="index" class="release-confetti" :style="style" />
        </div>
    </Teleport>
</template>

<style scoped>
.release-confetti { position: absolute; top: -1rem; width: .5rem; height: .75rem; animation: release-confetti-fall linear forwards; }
@keyframes release-confetti-fall { to { transform: translate(var(--drift), calc(100dvh + 2rem)) rotate(720deg); opacity: 0; } }
@media (prefers-reduced-motion: reduce) { .release-confetti { animation: none; display: none; } }
</style>
