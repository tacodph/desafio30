<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const siteUrl = ref('');
onMounted(() => { siteUrl.value = window.location.origin; });
const firstImage = '/images/tutoriais/tela-inicial-passos-1-3.png';
const secondImage = '/images/tutoriais/tela-inicial-passos-4-5.png';
const steps = [
    { title: 'Abra o Safari', text: 'Entre no Safari do iPhone e acesse o site do Desafio da Virada.', image: firstImage, viewBox: '30 398 205 274', width: 739, height: 1600 },
    { title: 'Toque em Compartilhar', text: 'Procure o ícone de um quadrado com uma seta apontando para cima. Dependendo da versão do Safari, ele pode estar na barra inferior ou dentro do menu da página.', image: firstImage, viewBox: '30 749 205 274', width: 739, height: 1600 },
    { title: 'Procure Adicionar à Tela de Início', text: 'Role as opções do menu até encontrar “Adicionar à Tela de Início” e toque nessa opção.', image: firstImage, viewBox: '30 1098 205 276', width: 739, height: 1600 },
    { title: 'Toque em Adicionar', text: 'No campo do nome, use “Desafio da Virada”. Depois, confirme em “Adicionar”.', image: secondImage, viewBox: '47 109 325 433', width: 1170, height: 1190 },
    { title: 'Prontinho!', text: 'O ícone do desafio estará na tela inicial do iPhone. Agora é só tocar nele para acessar a plataforma.', image: secondImage, viewBox: '47 661 325 438', width: 1170, height: 1190 },
];
</script>

<template>
    <Head title="Adicionar Desafio na tela inicial" />
    <AppLayout :breadcrumbs="[{ title: 'Boas-vindas', href: '/atleta/boas-vindas/video' }, { title: 'Tutoriais', href: '#' }, { title: 'Adicionar Desafio na tela inicial', href: '/atleta/boas-vindas/tutoriais/tela-inicial' }]">
        <main class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-8">
            <header class="space-y-3">
                <div class="eyebrow">Boas-vindas · Tutoriais</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] text-brand-gold">Adicionar Desafio na tela inicial</h1>
                <p class="text-brand-muted">Crie um atalho do Desafio da Virada na tela inicial do iPhone para acessar a plataforma com mais facilidade.</p>
                <span class="inline-block rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-sm font-bold text-emerald-400">Faça pelo Safari</span>
            </header>
            <ol class="divide-y divide-brand-line" aria-label="Passo a passo para adicionar o desafio no iPhone">
                <li v-for="(step, index) in steps" :key="step.title" class="grid items-start gap-5 py-6 sm:grid-cols-[10rem_1fr] sm:gap-7">
                    <svg :viewBox="step.viewBox" :width="step.viewBox.split(' ')[2]" :height="step.viewBox.split(' ')[3]" class="mx-auto block h-auto w-full max-w-48 overflow-hidden rounded-3xl sm:max-w-none" role="img" :aria-label="'Exemplo ilustrado: ' + step.title" overflow="hidden">
                        <image :href="step.image" :width="step.width" :height="step.height" />
                    </svg>
                    <div class="space-y-4">
                        <h2 class="text-xl font-black sm:text-2xl">{{ index + 1 }}. {{ step.title }}</h2>
                        <p class="text-base leading-relaxed text-brand-cream/80">{{ step.text }}</p>
                        <a v-if="index === 0 && siteUrl" :href="siteUrl" class="inline-block break-all font-bold text-brand-gold underline">{{ siteUrl }}</a>
                    </div>
                </li>
            </ol>
            <section class="surface-card space-y-4 p-4 sm:p-6" aria-label="Tutorial de atalho na tela inicial">
                <h2 class="text-xl font-bold text-brand-gold">Veja também o vídeo do tutorial</h2>
                <video controls playsinline preload="none" class="max-h-[70dvh] w-full rounded-xl bg-black" aria-label="Como adicionar o site à tela inicial do celular">
                    <source src="/videos/atalhoSafari.mp4" type="video/mp4" />
                    Seu navegador não suporta a reprodução deste vídeo.
                    <a href="/videos/atalhoSafari.mp4">Abrir vídeo do tutorial</a>
                </video>
                <p class="text-sm text-brand-muted">Pause e reveja o vídeo para acompanhar cada etapa no seu celular.</p>
            </section>
        </main>
    </AppLayout>
</template>
