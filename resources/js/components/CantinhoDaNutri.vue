<script setup lang="ts">
import { CalendarDays, LockKeyhole } from 'lucide-vue-next';

defineProps<{ compact?: boolean }>();

const nutriMaterials = [
    {
        title: 'Kit da vida real',
        description: 'Produtos industrializados práticos para ter à mão: iogurtes, bebidas proteicas, mix de castanhas, barrinhas e outras opções de mercado.',
        image: '/images/nutri/kit.jpg',
        alt: 'Iogurtes, castanhas e lanches práticos para a rotina.',
    },
    {
        title: 'Refeições práticas',
        description: '5 opções de café da manhã, 5 almoços, 5 jantares e 5 lanches fáceis de preparar.',
        image: '/images/nutri/refeicoes.jpg',
        alt: 'Opções simples de café da manhã, almoço e jantar.',
    },
    {
        title: 'Produtos do dia a dia',
        description: 'Aprenda a ler rótulos, comparar produtos e fazer escolhas mais conscientes no supermercado.',
        image: '/images/nutri/produtos.jpg',
        alt: 'Comparação de rótulos de produtos no supermercado.',
    },
    {
        title: 'SOS da vida real',
        description: 'SOS fim de semana, vontade de doce e como fazer escolhas em restaurantes.',
        image: '/images/nutri/sos.jpg',
        alt: 'Refeições compartilhadas em um encontro no restaurante.',
    },
    {
        title: 'Guia de hidratação e sono',
        description: 'Estratégias simples para melhorar sua hidratação e qualidade do sono.',
        image: '/images/nutri/sono.jpg',
        alt: 'Quarto tranquilo com água disponível na mesa de cabeceira.',
    },
];
</script>

<template>
    <section aria-labelledby="nutri-title" class="flex flex-col gap-9">
        <div class="flex max-w-[760px] flex-col gap-3.5">
                    <div class="eyebrow">Conteúdo exclusivo para participantes</div>
                    <h2 id="nutri-title" :class="compact ? 'font-display m-0 text-3xl font-extrabold uppercase' : 'display-title m-0 text-[clamp(44px,6vw,80px)]'">Cantinho da nutri</h2>
                    <p class="m-0 text-[18px] leading-relaxed text-brand-muted-3">
                        Materiais exclusivos para facilitar sua rotina durante o desafio.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-x-6 gap-y-9 sm:grid-cols-2 lg:grid-cols-3">
                    <article v-for="item in nutriMaterials" :key="item.title" class="flex min-w-0 flex-col gap-4">
                        <img
                            :src="item.image"
                            :alt="item.alt"
                            width="1536"
                            height="1024"
                            loading="lazy"
                            decoding="async"
                            class="aspect-[16/10] w-full rounded-[22px] border border-brand-line2 object-cover"
                        />
                        <div class="flex flex-col gap-2.5">
                            <h3 class="font-display m-0 text-[26px] font-extrabold uppercase leading-tight">{{ item.title }}</h3>
                            <p class="m-0 text-base leading-relaxed text-brand-muted-3">{{ item.description }}</p>
                        </div>
                    </article>
                </div>

                <article class="flex flex-col gap-4 rounded-[22px] border border-brand-gold/30 bg-brand-gold/5 p-5 sm:flex-row sm:items-start sm:gap-5 sm:p-6">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand-gold/10 text-brand-gold">
                        <CalendarDays class="size-5" aria-hidden="true" />
                    </div>
                    <div class="flex min-w-0 flex-1 flex-col gap-4">
                        <h3 class="m-0 text-lg font-bold text-brand-cream">Bônus exclusivo</h3>
                        <div class="flex flex-col gap-4 border-t border-brand-gold/20 pt-5">
                            <p class="m-0 max-w-[780px] text-[18px] leading-relaxed text-brand-cream">
                                Material: <strong>GUIA DE CONTINUIDADE — Como não perder tudo o que você construiu nesses 30 dias.</strong>
                            </p>
                            <div class="flex flex-col gap-3">
                                <p class="m-0 text-base font-semibold text-brand-muted-3">O bônus</p>
                                <ul class="m-0 flex list-disc flex-col gap-2 pl-5 text-base leading-relaxed text-brand-muted-3">
                                    <li>Como manter os hábitos depois do desafio.</li>
                                    <li>Como organizar a próxima semana.</li>
                                    <li>Como lidar com dias ruins sem abandonar tudo.</li>
                                    <li>Como estabelecer novas metas.</li>
                                </ul>
                            </div>
                        </div>
                        <span class="mt-1 inline-flex items-center gap-2 text-sm font-semibold text-brand-gold">
                            <LockKeyhole class="size-4 shrink-0" aria-hidden="true" />
                            Exclusivo a partir da última semana do Desafio da Virada
                        </span>
                    </div>
                </article>
    </section>
</template>
