<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

type Faq = { id: number; question: string; answer: string; sort_order: number; published: boolean };
defineProps<{ faqs: Faq[] }>();
const editingId = ref<number | null>(null);
const form = useForm({ question: '', answer: '', sort_order: 0, published: true });
const reset = () => { editingId.value = null; form.reset(); form.clearErrors(); };
const edit = (faq: Faq) => {
    form.clearErrors();
    editingId.value = faq.id;
    form.question = faq.question;
    form.answer = faq.answer;
    form.sort_order = faq.sort_order;
    form.published = faq.published;
    document.getElementById('faq-form')?.scrollIntoView({ behavior: 'smooth' });
    document.getElementById('question')?.focus({ preventScroll: true });
};
const save = () => {
    const options = { preserveScroll: true, onSuccess: reset };
    if (editingId.value) form.put(route('admin.faqs.update', editingId.value), options);
    else form.post(route('admin.faqs.store'), options);
};
</script>

<template>
    <Head title="Administrar dúvidas frequentes" />
    <AppLayout :breadcrumbs="[{ title: 'Administração', href: '/dashboard' }, { title: 'Dúvidas Frequentes', href: '/admin/duvidas-frequentes' }]">
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-8">
            <header>
                <h1 class="text-3xl font-black">Dúvidas Frequentes</h1>
                <p class="mt-3 text-sm text-brand-cream/70">Cadastre perguntas, atualize as respostas e escolha o que aparece no menu Ajuda.</p>
                <Link href="/ajuda/duvidas-frequentes" class="mt-3 inline-block text-sm font-bold text-brand-gold underline">Ver página de dúvidas</Link>
            </header>
            <form id="faq-form" class="surface-card flex scroll-mt-6 flex-col gap-4 p-6" @submit.prevent="save">
                <h2 class="text-xl font-bold">{{ editingId ? 'Editar pergunta' : 'Cadastrar pergunta' }}</h2>
                <div class="grid gap-2">
                    <label for="question" class="text-sm font-bold">Pergunta</label>
                    <input id="question" v-model="form.question" required maxlength="255" class="rounded-xl border border-brand-line2 bg-brand-card2 p-3" />
                    <InputError :message="form.errors.question" />
                </div>
                <div class="grid gap-2">
                    <label for="answer" class="text-sm font-bold">Resposta</label>
                    <textarea id="answer" v-model="form.answer" required maxlength="10000" rows="6" class="rounded-xl border border-brand-line2 bg-brand-card2 p-3" />
                    <p class="text-xs text-brand-cream/60">Use texto simples. As quebras de linha serão preservadas.</p>
                    <InputError :message="form.errors.answer" />
                </div>
                <div class="grid gap-2">
                    <label for="sort-order" class="text-sm font-bold">Ordem de exibição (menores primeiro)</label>
                    <input id="sort-order" v-model.number="form.sort_order" type="number" min="0" max="99999" required class="w-32 rounded-xl border border-brand-line2 bg-brand-card2 p-3" />
                    <InputError :message="form.errors.sort_order" />
                </div>
                <label class="flex items-center gap-3 text-sm"><input v-model="form.published" type="checkbox" class="rounded border-brand-line2" />Publicar na página de dúvidas</label>
                <InputError :message="form.errors.published" />
                <div class="flex flex-wrap gap-3">
                    <Button :disabled="form.processing" type="submit">{{ form.processing ? 'Salvando…' : editingId ? 'Salvar alterações' : 'Cadastrar pergunta' }}</Button>
                    <Button v-if="editingId" :disabled="form.processing" type="button" variant="outline" @click="reset">Cancelar edição</Button>
                </div>
                <p v-if="form.recentlySuccessful" role="status" class="text-sm text-brand-gold">Pergunta salva com sucesso.</p>
            </form>
            <section aria-labelledby="faq-list">
                <h2 id="faq-list" class="mb-4 text-xl font-bold">Perguntas cadastradas ({{ faqs.length }})</h2>
                <div class="grid gap-3">
                    <article v-for="faq in faqs" :key="faq.id" class="surface-card p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <span class="text-xs font-bold" :class="faq.published ? 'text-brand-gold' : 'text-brand-cream/50'">{{ faq.published ? 'Publicada' : 'Oculta' }} · Ordem {{ faq.sort_order }}</span>
                            <Button type="button" variant="outline" :disabled="form.processing" :aria-label="`Editar: ${faq.question}`" @click="edit(faq)">Editar</Button>
                        </div>
                        <h3 class="mt-3 break-words font-bold">{{ faq.question }}</h3>
                        <p class="mt-3 whitespace-pre-line break-words text-sm leading-relaxed text-brand-cream/70">{{ faq.answer }}</p>
                    </article>
                </div>
                <p v-if="!faqs.length" class="text-brand-cream/70">Nenhuma pergunta cadastrada.</p>
            </section>
        </main>
    </AppLayout>
</template>
