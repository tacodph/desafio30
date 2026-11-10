<script setup lang="ts">
import reference from '@/assets/alimentacao/como-funciona-alimentacao.png';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { BookOpen, CalendarDays, Camera, ClipboardList, ClipboardX, ShoppingCart, Target, UtensilsCrossed } from 'lucide-vue-next';

const title = 'Como funciona a alimentação no desafio?';
const steps = [
    { icon: BookOpen, title: 'Conheça os materiais da Nutri.', text: 'Na plataforma, você encontrará materiais com sugestões e orientações práticas, como opções de queijos, combinações de laticínios, castanhas, sugestões de pratos e outras possibilidades para variar sua alimentação.' },
    { icon: ShoppingCart, title: 'Escolha seus alimentos.', text: 'Depois de consultar os materiais, vá até a área de lista de compras e selecione os alimentos que pretende consumir durante a semana, considerando suas preferências e sua rotina.' },
    { icon: ClipboardList, title: 'Organize sua semana.', text: 'A plataforma gera sua lista de compras com os alimentos selecionados. Assim, você consegue se planejar e ter em casa opções para montar suas refeições.' },
    { icon: UtensilsCrossed, title: 'Monte suas próprias refeições.', text: 'Com os alimentos escolhidos e as sugestões dos materiais, organize seu café da manhã, almoço, jantar e lanches. A ideia é aprender a combinar os grupos alimentares e fazer escolhas mais equilibradas.' },
    { icon: Camera, title: 'Compartilhe suas refeições!', text: 'Você pode postar suas refeições na plataforma. A nutri vai acompanhar, curtir e comentar as escolhas saudáveis que vocês compartilharem!' },
];
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Ajuda', href: '/ajuda/duvidas-frequentes' }, { title, href: '/ajuda/como-funciona-a-alimentacao' }]">
        <main class="mx-auto w-full max-w-6xl p-4 md:p-6">
            <article class="overflow-hidden rounded-3xl bg-[#151515] text-white">
                <header class="grid items-center gap-4 p-5 sm:grid-cols-[auto_1fr] lg:grid-cols-[auto_1fr_24%] lg:p-0 lg:pl-6">
                    <img src="/images/logo-mark-30-virada.png" alt="Desafio da Virada · 30 dias" width="128" height="128" class="size-24 object-contain sm:size-28" />
                    <h1 class="font-display text-3xl font-black uppercase leading-tight sm:border-l-2 sm:border-white sm:pl-5 sm:text-4xl lg:py-4">
                        Como funciona a <span class="block italic text-yellow-400">alimentação</span> no desafio?
                    </h1>
                    <svg viewBox="785 0 301 210" class="hidden w-full lg:block" aria-hidden="true" overflow="hidden"><image :href="reference" width="1086" height="1448" /></svg>
                </header>

                <div class="space-y-5 p-4 pt-0 sm:p-6 sm:pt-0">
                    <section class="flex items-start gap-4 rounded-3xl bg-[#f9f3e5] p-4 text-black sm:items-center sm:p-5">
                        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-yellow-400 sm:size-20"><ClipboardX class="size-9 sm:size-12" aria-hidden="true" /></span>
                        <div>
                            <h2 class="font-display text-2xl font-black italic sm:text-3xl">Não é um cardápio pronto!</h2>
                            <p>Um plano alimentar precisa considerar a individualidade, a rotina, as necessidades e os objetivos de cada pessoa. Como temos participantes com perfis diferentes, não seria adequado disponibilizar uma dieta igual para todos.</p>
                        </div>
                    </section>

                    <p class="text-lg leading-snug sm:px-5 sm:text-2xl">A proposta do <strong class="italic text-yellow-400">Desafio da Virada</strong> é desenvolver a sua <strong class="italic underline decoration-yellow-400 underline-offset-4">autonomia</strong> para fazer boas escolhas, organizar sua alimentação e levar esses hábitos para a vida. Veja como funciona na prática:</p>

                    <ol class="space-y-3">
                        <li v-for="(step, index) in steps" :key="step.title" class="flex flex-col overflow-hidden rounded-3xl bg-[#f9f3e5] text-black sm:flex-row">
                            <div class="flex items-center gap-4 bg-gradient-to-r from-yellow-400 to-[#f9f3e5] px-4 py-3 sm:w-44 sm:shrink-0 sm:justify-center">
                                <span class="grid size-10 shrink-0 place-items-center rounded-full bg-black text-2xl font-black text-yellow-300">{{ index + 1 }}</span>
                                <component :is="step.icon" class="size-9 sm:size-12" aria-hidden="true" />
                            </div>
                            <div class="px-4 pb-4 sm:my-3 sm:border-l-2 sm:border-black sm:py-0 sm:pl-5">
                                <h2 class="font-display text-xl font-black leading-tight sm:text-2xl">{{ step.title }}</h2>
                                <p class="mt-1 leading-snug">{{ step.text }}</p>
                            </div>
                        </li>
                    </ol>

                    <div class="grid gap-4 md:grid-cols-2">
                        <section class="rounded-3xl border-2 border-yellow-400 p-5">
                            <h2 class="mb-2 flex items-center gap-3 font-display text-2xl font-black italic uppercase leading-tight text-yellow-400"><CalendarDays class="size-10 shrink-0" aria-hidden="true" /> Atenção aos novos materiais!</h2>
                            <p>Alguns conteúdos serão liberados gradualmente, a partir do <strong class="text-yellow-400">7º dia</strong> do desafio, como sugestões de jantares, lanches saudáveis e receitas.</p>
                        </section>
                        <section class="rounded-3xl bg-yellow-400 p-5 text-black">
                            <h2 class="mb-2 flex items-center gap-3 font-display text-2xl font-black italic uppercase leading-tight"><Target class="size-10 shrink-0" aria-hidden="true" /> O objetivo destes 30 dias</h2>
                            <p>É muito maior do que seguir uma prescrição: é aprender a escolher, combinar e organizar sua alimentação <strong>com mais autonomia!</strong></p>
                        </section>
                    </div>
                </div>
            </article>
        </main>
    </AppLayout>
</template>
