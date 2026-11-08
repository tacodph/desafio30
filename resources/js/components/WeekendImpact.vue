<script setup lang="ts">
import impactImage from '@/assets/alimentacao/impacto-fim-de-semana.png';
import { Calculator } from 'lucide-vue-next';

const plannedCalories = 1800;
const days = [
    { label: 'SEG', calories: 1800, x: 51, y: 598, width: 152, height: 253, color: '#afd141' },
    { label: 'TER', calories: 1800, x: 209, y: 598, width: 140, height: 253, color: '#afd141' },
    { label: 'QUA', calories: 1800, x: 355, y: 598, width: 145, height: 253, color: '#afd141' },
    { label: 'QUI', calories: 1800, x: 505, y: 598, width: 144, height: 253, color: '#afd141' },
    { label: 'SEX', calories: 1800, x: 653, y: 598, width: 140, height: 253, color: '#afd141' },
    { label: 'SÁB', calories: 2200, x: 799, y: 534, width: 145, height: 317, color: '#4cc2dc' },
    { label: 'DOM', calories: 3000, x: 949, y: 482, width: 151, height: 369, color: '#4cc2dc' },
];
const total = days.reduce((sum, day) => sum + day.calories, 0);
const average = total / days.length;
const extra = total - plannedCalories * days.length;
const number = (value: number) => Math.round(value).toLocaleString('pt-BR');
</script>

<template>
    <section class="space-y-6 rounded-3xl border-2 border-yellow-400 bg-[#191919] p-4 text-white sm:p-6" aria-labelledby="weekend-impact-heading">
        <header class="grid items-center gap-5 md:grid-cols-[100px_1fr] xl:grid-cols-[130px_1.3fr_1fr]">
            <img src="/images/logo-mark-30-virada.png" alt="Desafio da Virada — 30 dias" width="130" height="130" class="mx-auto size-28 object-contain xl:size-32" />
            <h2 id="weekend-impact-heading" class="font-display text-3xl font-black uppercase leading-tight md:border-l-2 md:border-white/80 md:pl-5 sm:text-4xl">Por que o final de semana <span class="block italic text-yellow-400">pode atrapalhar?</span></h2>
            <p class="border-l-4 border-yellow-400 pl-5 text-lg leading-relaxed md:col-span-2 xl:col-span-1">O seu planejamento pode estar em dia durante a semana, mas alguns excessos no final de semana são suficientes para anular (ou reduzir) os resultados.</p>
        </header>

        <div class="space-y-5 rounded-3xl border-2 border-white/90 p-3 sm:p-5">
            <div class="grid items-center gap-4 lg:grid-cols-2">
                <h3 class="rounded-full bg-yellow-400 px-5 py-3 text-center font-display text-2xl font-black italic uppercase leading-tight text-black">Na prática, veja o impacto:</h3>
                <p class="text-base leading-relaxed">Imagine um planejamento de 1.800 kcal/dia. Durante a semana, você segue o plano. No final de semana, alguns excessos aumentam bastante o consumo.</p>
            </div>
            <p class="text-sm text-white/70 md:hidden">Deslize para ver todos os dias e a média semanal.</p>
            <div class="overflow-x-auto rounded-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-yellow-400" tabindex="0" role="region" aria-label="Comparação do consumo diário e da média semanal">
                <div class="relative grid min-w-[720px] grid-cols-8 items-end gap-1.5 pt-16">
                    <div v-for="day in days" :key="day.label" class="min-w-0">
                        <div class="flex h-[300px] flex-col justify-end">
                            <div class="relative shrink-0 overflow-visible rounded-t-2xl" :style="{ height: `${day.calories / 10}px`, backgroundColor: day.color }">
                                <p class="absolute inset-x-0 bottom-full pb-3 text-center text-2xl font-black leading-none tabular-nums">{{ number(day.calories) }}<span class="block text-xl">kcal</span></p>
                                <svg :viewBox="`${day.x} ${day.y} ${day.width} ${day.height}`" class="block h-full w-full overflow-hidden rounded-t-2xl" preserveAspectRatio="none" aria-hidden="true">
                                    <image :href="impactImage" width="1312" height="1199" />
                                    <rect :x="day.x" y="615" :width="day.width" height="8" :fill="day.color" />
                                </svg>
                            </div>
                        </div>
                        <p class="flex h-16 items-center justify-center rounded-b-xl bg-[#f7edd7] px-1 text-center text-xl font-black text-black">{{ day.label }}<span class="sr-only">: {{ number(day.calories) }} kcal</span></p>
                    </div>
                    <div class="min-w-0">
                        <div class="flex h-[300px] flex-col justify-end">
                            <div class="relative shrink-0 rounded-t-2xl bg-gradient-to-b from-yellow-300 to-amber-400" :style="{ height: `${average / 10}px` }">
                                <p class="absolute inset-x-0 bottom-full pb-3 text-center text-2xl font-black leading-none tabular-nums">{{ number(average) }}<span class="block text-xl">kcal</span></p>
                            </div>
                        </div>
                        <p class="flex h-16 items-center justify-center rounded-b-xl bg-[#f7edd7] px-1 text-center text-base font-black uppercase leading-tight text-black">Média<br />semanal</p>
                    </div>
                    <div class="pointer-events-none absolute inset-x-0 bottom-[244px] border-t-2 border-dashed border-white/90" aria-hidden="true" />
                </div>
            </div>
        </div>

        <footer class="grid items-center gap-5 lg:grid-cols-2">
            <div class="flex items-start gap-4">
                <span class="flex size-20 shrink-0 items-center justify-center rounded-3xl bg-yellow-400 text-black"><Calculator class="size-12" aria-hidden="true" /></span>
                <div class="space-y-2">
                    <h3 class="text-3xl font-black italic text-yellow-400">O resultado?</h3>
                    <p class="text-lg leading-relaxed">Sua média semanal sobe de 1.800 kcal para aproximadamente {{ number(average) }} kcal/dia:</p>
                </div>
            </div>
            <p class="rounded-[2rem] bg-yellow-400 px-5 py-5 text-center font-display text-4xl font-black italic leading-tight text-black sm:text-5xl">+ {{ number(extra) }} kcal <span class="block text-3xl sm:text-4xl">na semana</span></p>
        </footer>
    </section>
</template>
