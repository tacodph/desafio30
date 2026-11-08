<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { handleAccordionClick } from '@/lib/accordionScroll';
import { Head, Link, usePage } from '@inertiajs/vue3';
import type { SharedData } from '@/types';
import { CircleHelp, Search } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{ faqs: { id: number; question: string; answer: string }[] }>();
const page = usePage<SharedData>();
const search = ref('');
const normalize = (text: string) => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
const filtered = computed(() => props.faqs.filter((faq) => normalize(`${faq.question} ${faq.answer}`).includes(normalize(search.value.trim()))));
</script>

<template>
    <Head title="Dúvidas Frequentes" />
    <component :is="page.props.auth.user ? AppLayout : 'div'" :breadcrumbs="[{ title: 'Ajuda', href: '/ajuda/duvidas-frequentes' }, { title: 'Dúvidas Frequentes', href: '/ajuda/duvidas-frequentes' }]">
        <main class="mx-auto w-full max-w-4xl px-4 py-8 md:p-8" @click="handleAccordionClick">
            <header class="rounded-3xl border border-brand-gold/20 bg-brand-gold/[0.04] p-7 text-center sm:p-12">
                <p class="text-sm font-extrabold uppercase tracking-[3px] text-brand-gold">Desafio da Virada</p>
                <CircleHelp class="mx-auto my-6 size-20 text-brand-gold" aria-hidden="true" />
                <h1 class="text-3xl font-black uppercase sm:text-5xl">Dúvidas frequentes</h1>
                <p class="mt-5 text-xl font-bold">Seja muito bem-vindo(a)! ❤️</p>
                <p class="mx-auto mt-4 max-w-xl text-lg text-brand-cream/70">Preparamos este espaço para esclarecer as principais dúvidas e ajudar você a aproveitar ao máximo os nossos 30 dias de desafio.</p>
                <a href="#perguntas" class="mt-7 inline-block rounded-full bg-brand-gold px-7 py-4 font-extrabold text-brand-ink hover:brightness-110">Confira as dúvidas frequentes</a>
            </header>

            <section id="perguntas" class="mt-8 scroll-mt-6" aria-labelledby="faq-heading">
                <h2 id="faq-heading" class="text-2xl font-black">Principais dúvidas sobre o desafio</h2>
                <label for="faq-search" class="mb-2 mt-5 block text-sm font-semibold">Buscar pergunta ou assunto</label>
                <div class="relative">
                    <Search class="absolute left-3 top-3 size-5 text-brand-cream/50" aria-hidden="true" />
                    <input id="faq-search" v-model="search" type="search" class="h-12 w-full rounded-xl border border-brand-line2 bg-brand-card2 pl-11 pr-4 text-brand-cream" placeholder="Ex.: metas, acesso, materiais de apoio…" />
                </div>
                <p role="status" class="my-4 text-sm text-brand-cream/60">{{ filtered.length }} {{ filtered.length === 1 ? 'pergunta encontrada' : 'perguntas encontradas' }}</p>
                <div class="grid gap-3">
                    <details v-for="faq in filtered" :key="faq.id" class="surface-card overflow-hidden p-5 open:border-brand-gold/50">
                        <summary class="cursor-pointer break-words font-bold text-brand-cream focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-gold">{{ faq.question }}</summary>
                        <p class="mt-4 whitespace-pre-line break-words leading-relaxed text-brand-cream/75">{{ faq.answer }}</p>
                    </details>
                </div>
                <p v-if="!filtered.length" class="surface-card p-6 text-brand-cream/70">{{ faqs.length ? 'Nenhuma pergunta encontrada. Tente outro termo ou fale com o suporte.' : 'As perguntas frequentes estarão disponíveis em breve. Enquanto isso, fale com o suporte.' }}</p>
            </section>
            <section class="mt-8 rounded-3xl border border-brand-gold/20 bg-brand-gold/[0.04] p-7 text-center sm:p-10" aria-labelledby="closing-title">
                <h2 id="closing-title" class="text-2xl font-black text-brand-gold">Agora é com você!</h2>
                <p class="mt-4 leading-relaxed text-brand-cream/80">Chame os amigos, organize sua rotina, acompanhe suas metas e venha viver esses 30 dias com a gente.</p>
                <p class="mt-4 leading-relaxed text-brand-cream/80">Não precisa fazer tudo perfeitamente. O importante é participar, se comprometer e aproveitar o processo!</p>
                <p class="mt-5 text-lg font-bold">Vamos juntos fazer essa virada acontecer! 🚀❤️</p>
            </section>
            <footer class="surface-card mt-8 p-6 text-center">
                <h2 class="text-xl font-bold">Sua dúvida ainda não foi respondida?</h2>
                <Link href="/suporte" class="mt-4 inline-block rounded-xl bg-brand-gold px-6 py-3 font-bold text-brand-ink">Falar com o suporte</Link>
            </footer>
        </main>
    </component>
</template>
