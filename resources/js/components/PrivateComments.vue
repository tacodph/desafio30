<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { SharedData } from '@/types';

const props = defineProps<{ context: string; reply?: boolean }>();
const page = usePage<SharedData>();
const role = computed(() => page.props.auth.user?.user_type?.name);
const allowed = computed(() => ['atleta', 'nutricionista', 'administrador'].includes(role.value ?? ''));
const open = ref(false);
const body = ref('');
const busy = ref(false);
const error = ref('');
const success = ref('');
const comments = ref<{ id: number; body: string; name: string; created_at: string }[]>([]);
const currentPage = ref(1);
const lastPage = ref(1);

async function load(number = 1) {
    error.value = '';
    busy.value = true;
    try {
        const response = await fetch(`/comentarios-privados?${new URLSearchParams({ context: props.context, page: String(number) })}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error();
        const result = await response.json();
        comments.value = result.comments.data;
        currentPage.value = result.comments.current_page;
        lastPage.value = result.comments.last_page;
    } catch { error.value = 'Não foi possível carregar os comentários. Tente novamente.'; }
    finally { busy.value = false; }
}

async function toggle() {
    open.value = !open.value;
    if (open.value) await load();
}

async function save() {
    if (busy.value || !body.value.trim()) return;
    busy.value = true;
    error.value = '';
    success.value = '';
    try {
        const token = document.cookie.split('; ').find((cookie) => cookie.startsWith('XSRF-TOKEN='))?.slice(11);
        const response = await fetch('/comentarios-privados', {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': decodeURIComponent(token ?? '') },
            body: JSON.stringify({ context: props.context, body: body.value }),
        });
        if (!response.ok) throw new Error();
        body.value = '';
        success.value = props.reply ? 'Resposta enviada.' : 'Comentário enviado.';
        await load();
    } catch { error.value = 'Não foi possível enviar. Tente novamente.'; }
    finally { busy.value = false; }
}
</script>

<template>
    <section v-if="allowed" class="my-3 space-y-3 rounded-xl border border-brand-line2 p-4" aria-label="Comentários privados">
        <button type="button" class="rounded-lg bg-brand-gold px-4 py-2 text-sm font-bold text-brand-ink" :aria-expanded="open" @click="toggle">
            {{ reply ? (role === 'atleta' ? 'Responder à nutri' : 'Ver respostas do atleta') : (role === 'atleta' ? 'Comentar o vídeo' : 'Ver comentários dos atletas') }}
        </button>
        <div v-if="open" class="space-y-3">
            <p class="text-sm text-brand-muted">Somente o autor, a nutricionista e o administrador podem ver estes comentários.</p>
            <form v-if="role === 'atleta'" class="space-y-2" @submit.prevent="save">
                <label class="block space-y-2 text-sm font-semibold">
                    <span>{{ reply ? 'Sua resposta à nutri' : 'Seu comentário sobre o vídeo' }}</span>
                    <textarea v-model="body" rows="3" maxlength="2000" required class="w-full rounded-xl border border-brand-line2 bg-brand-card2 p-3 text-brand-cream" />
                </label>
                <button type="submit" :disabled="busy || !body.trim()" class="rounded-lg bg-brand-gold px-4 py-2 text-sm font-bold text-brand-ink disabled:opacity-50">{{ busy ? 'Aguarde…' : 'Enviar' }}</button>
            </form>
            <p v-if="error" role="alert" class="text-sm text-red-400">{{ error }}</p>
            <p v-if="success" role="status" class="text-sm text-brand-gold">{{ success }}</p>
            <p v-if="busy" role="status" class="text-sm text-brand-muted">Carregando…</p>
            <p v-else-if="!comments.length && !error" class="text-sm text-brand-muted">Nenhum comentário enviado ainda.</p>
            <article v-for="comment in comments" :key="comment.id" class="space-y-1 rounded-xl bg-brand-card2 p-3">
                <p class="text-sm font-bold text-brand-gold">{{ comment.name }}</p>
                <p class="whitespace-pre-wrap break-words text-sm text-brand-cream">{{ comment.body }}</p>
            </article>
            <nav v-if="lastPage > 1" class="flex items-center gap-3 text-sm" aria-label="Páginas de comentários">
                <button type="button" :disabled="busy || currentPage === 1" class="text-brand-gold disabled:opacity-50" @click="load(currentPage - 1)">Anterior</button>
                <span>{{ currentPage }} / {{ lastPage }}</span>
                <button type="button" :disabled="busy || currentPage === lastPage" class="text-brand-gold disabled:opacity-50" @click="load(currentPage + 1)">Próxima</button>
            </nav>
        </div>
    </section>
</template>
