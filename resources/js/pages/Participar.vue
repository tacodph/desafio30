<script setup lang="ts">
import { images } from '@/data/images';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    price: number;
}>();

const page = usePage();
const priceLabel = computed(() => props.price.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));
// Quem já está logada vai direto para o pagamento; quem não está, cria a conta primeiro.
const ctaHref = computed(() => (page.props.auth?.user ? route('pagamento.show') : route('register')));
</script>

<template>
    <Head title="Inscrição | Desafio da Virada" />

    <div class="min-h-screen bg-brand-ink text-brand-cream">
        <header class="border-b border-brand-line">
            <div class="mx-auto flex max-w-[760px] items-center justify-between px-6 py-4">
                <Link :href="route('home')" class="flex items-center gap-2.5 text-brand-cream hover:text-brand-cream">
                    <img :src="images.logoMark" alt="" class="size-11 object-contain" />
                    <span class="font-display text-[22px] font-extrabold italic uppercase tracking-wide">
                        Desafio <span class="text-brand-gold">da Virada</span>
                    </span>
                </Link>
                <Link :href="route('home')" class="text-sm font-semibold text-brand-muted-3 hover:text-brand-cream">Voltar</Link>
            </div>
        </header>

        <main class="mx-auto flex max-w-[760px] flex-col gap-10 px-6 py-12">
            <section class="flex flex-col gap-5">
                <p class="m-0 text-sm font-bold uppercase tracking-[2px] text-brand-muted-3">CHEGA DE DEIXAR PARA A PRÓXIMA SEGUNDA-FEIRA.</p>
                <h1 class="display-title m-0 text-[clamp(40px,7vw,68px)] leading-tight text-brand-gold">SUA VIRADA COMEÇA DIA 9 DE NOVEMBRO!</h1>
                <p class="m-0 text-[20px] font-semibold leading-relaxed">30 dias. 5 objetivos. Uma oportunidade de fazer diferente.</p>
            </section>

            <section class="flex flex-col gap-5 border-t border-brand-line pt-10">
                <h2 class="display-title m-0 text-[clamp(30px,5vw,44px)] leading-tight">O QUE VOCÊ VAI VIVER NESSES 30 DIAS?</h2>
                <p class="m-0 text-[18px] leading-relaxed text-brand-muted-3">
                    O Desafio da Virada reúne 5 objetivos para ajudar você a sair do automático e colocar em prática mudanças que cabem na vida real:
                </p>
                <p class="m-0 text-[20px] font-bold leading-relaxed text-brand-gold">Alimentação. Movimento. Hidratação. Sono. Habilidade nova.</p>
                <p class="m-0 text-[18px] leading-relaxed text-brand-muted-3">
                    Você terá uma plataforma para registrar seus check-ins, acompanhar sua evolução, somar pontos e visualizar suas conquistas ao longo do desafio.
                </p>
                <p class="m-0 text-[18px] font-semibold leading-relaxed">Não precisa ser perfeito. Precisa participar, praticar e continuar.</p>
                <p class="m-0 text-[20px] font-bold text-brand-gold">COMEÇAMOS DIA 9 DE NOVEMBRO!</p>
                <p class="m-0 text-[18px] leading-relaxed text-brand-muted-3">Nos vemos lá!</p>
            </section>

            <section id="pagamento" class="flex flex-col gap-4 rounded-[28px] bg-brand-gold px-7 py-8 text-brand-ink">
                <h2 class="m-0 text-sm font-bold uppercase tracking-[2px]">SUA INSCRIÇÃO NO DESAFIO DA VIRADA</h2>
                <p class="display-title m-0 text-[clamp(48px,8vw,80px)] leading-none">{{ priceLabel }}</p>
                <p class="m-0 text-[20px] font-bold">Menos de R$ 2,70 por dia!</p>
                <p class="m-0 text-[17px] font-semibold leading-snug">Pagamento único para participar dos 30 dias.</p>
                <a
                    :href="ctaHref"
                    class="mt-2 inline-flex w-fit items-center justify-center rounded-full bg-brand-ink px-7 py-4 text-center text-[17px] font-bold text-brand-cream hover:bg-brand-line hover:text-brand-cream"
                >
                    QUERO GARANTIR MINHA VAGA
                </a>
            </section>
        </main>
    </div>
</template>
