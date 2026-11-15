<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import DinnerSuggestionsContent from '@/components/DinnerSuggestionsContent.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { consultationMealGroups, consultationMealImage } from '@/data/consultationMeals';
import { Head } from '@inertiajs/vue3';
import { Maximize2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const title = 'Sugestões de refeições reais - Jantar';
const activeGroup = ref('todos');
const visibleGroups = computed(() => {
    return consultationMealGroups
        .filter((group) => activeGroup.value === 'todos' || group.id === activeGroup.value)
        .map((group) => ({ ...group, gallery: group.id !== 'outros' }));
});
const totalMeals = consultationMealGroups.reduce((total, group) => total + group.meals.length, 0);
</script>

<template>
    <Head :title="title" />
    <AppLayout :breadcrumbs="[{ title: 'Alimentação', href: '/meus-objetivos/alimentacao' }, { title, href: '/alimentacao/refeicoes-reais-em-consultorio' }]">
        <main class="mx-auto flex w-full max-w-6xl flex-col gap-6 p-4 md:p-6">
            <header class="space-y-3">
                <div class="eyebrow">Alimentação</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)] leading-tight text-brand-gold">{{ title }}</h1>
            </header>

            <DinnerSuggestionsContent />

            <aside class="surface-card space-y-4 p-5 leading-relaxed text-brand-muted sm:p-6">
                <h2 class="font-display text-2xl font-bold text-brand-gold">FOTOS DE REFEIÇÕES REAIS PARA INSPIRAR SUAS ESCOLHAS</h2>
                <p>Aqui você encontra fotos de refeições minhas e dos meus pacientes, com combinações e preparações do nosso dia a dia.</p>
                <p>São exemplos reais, com alimentos simples, acessíveis e escolhas possíveis de reproduzir na sua rotina.</p>
                <p>Use essas referências para buscar ideias, experimentar combinações e deixar sua alimentação mais variada!</p>
                <p>Lembre-se de fazer a lista de compras de acordo com suas escolhas da semana.</p>
            </aside>

            <div class="flex flex-wrap gap-2" role="group" aria-label="Filtrar por tipo de prato">
                <button type="button" :aria-pressed="activeGroup === 'todos'" class="min-h-11 rounded-full border px-4 py-2 text-sm font-bold" :class="activeGroup === 'todos' ? 'border-yellow-400 bg-yellow-400 text-black' : 'border-brand-line2 bg-brand-card text-brand-cream hover:border-brand-gold'" @click="activeGroup = 'todos'">Todos ({{ totalMeals }})</button>
                <button v-for="group in consultationMealGroups" :key="group.id" type="button" :aria-pressed="activeGroup === group.id" class="min-h-11 rounded-full border px-4 py-2 text-sm font-bold" :class="activeGroup === group.id ? 'border-yellow-400 bg-yellow-400 text-black' : 'border-brand-line2 bg-brand-card text-brand-cream hover:border-brand-gold'" @click="activeGroup = group.id">{{ group.title }} ({{ group.meals.length }})</button>
            </div>

            <section v-for="group in visibleGroups" :key="group.id" class="space-y-5 rounded-3xl border border-yellow-400/60 bg-[#191919] p-4 text-white sm:p-5" :aria-labelledby="`meals-${group.id}`">
                <h2 :id="`meals-${group.id}`" class="rounded-3xl bg-yellow-400 px-4 py-3 text-center font-display text-2xl font-black uppercase leading-tight text-black">{{ group.title }}</h2>
                <div v-if="group.id === 'ovos'" class="space-y-4 text-base leading-relaxed text-white/90">
                    <p>Ovo é um alimento versátil, prático e que permite várias combinações para o dia a dia.</p>
                    <p class="font-bold text-yellow-400">Você pode variar o preparo:</p>
                    <ul class="list-disc space-y-2 pl-6">
                        <li>Mexido ou em forma de omelete;</li>
                        <li>Com arroz e vegetais, formando um mexidão;</li>
                        <li>Como recheio de uma tapioca ou acompanhado de pão;</li>
                        <li>Com legumes e verduras refogados;</li>
                        <li>Em preparações como panquecas e outras receitas simples.</li>
                    </ul>
                    <p class="rounded-2xl border border-yellow-400/40 bg-yellow-400/5 p-4"><strong class="text-yellow-400">Uma dica para deixar a refeição mais completa:</strong> sempre que possível, combine os ovos com vegetais, como alface, tomate, cenoura e folhas. Se for consumir com pão, inclua esses acompanhamentos para enriquecer a refeição.</p>
                </div>
                <div v-if="group.id === 'paes'" class="space-y-4 text-base leading-relaxed text-white/90">
                    <p>O pão pode fazer parte do jantar, mas merece atenção na combinação dos ingredientes.</p>
                    <p>Por ser um alimento à base de farinha, é interessante complementar essa refeição com boas fontes de proteína e vegetais, tornando-a mais nutritiva e equilibrada.</p>
                    <p>Prefira proteínas mais magras, como frango desfiado, atum, ovos ou carnes magras. Para escolher, consulte também a tabela de proteínas magras disponível na nossa lista de compras.</p>
                    <p>E não esqueça dos vegetais! Alface, tomate, cenoura ralada e outras opções ajudam a enriquecer o lanche.</p>
                    <p class="rounded-2xl border border-yellow-400/40 bg-yellow-400/5 p-4 font-semibold text-yellow-400">A ideia é não ficar apenas no pão com queijo: é transformar um lanche simples em uma refeição mais completa!</p>
                </div>
                <div v-if="group.id === 'pratos'" class="space-y-4 text-base leading-relaxed text-white/90">
                    <p>Sabe aquele almoço que você preparou? Ele também pode ser uma ótima opção para o jantar!</p>
                    <p>Não existe necessidade de preparar uma refeição completamente diferente à noite. Você pode repetir a combinação do almoço ou variar os ingredientes para não cair na monotonia.</p>
                    <p class="font-bold text-yellow-400">Algumas ideias:</p>
                    <ul class="list-disc space-y-2 pl-6">
                        <li>Trocar o feijão por grão-de-bico ou outra leguminosa;</li>
                        <li>Variar a proteína entre frango, carne, peixe ou ovos;</li>
                        <li>Preparar um mexidão com arroz, proteína e vegetais;</li>
                        <li>Variar os legumes e as verduras que acompanham o prato.</li>
                    </ul>
                    <p>E um lembrete importante: assim como o pão e o macarrão, o arroz também pode fazer parte do jantar. O que faz diferença é o contexto da refeição e a combinação dos alimentos.</p>
                    <p>Procure manter uma boa proporção entre arroz, proteína e vegetais, dando bastante espaço para legumes, verduras e folhas no prato.</p>
                    <p class="rounded-2xl border border-yellow-400/40 bg-yellow-400/5 p-4 font-semibold text-yellow-400">Não complique o jantar! Aproveite o que você já tem em casa e faça boas combinações. 🧡</p>
                </div>
                <div v-if="group.id === 'massas'" class="space-y-4 text-base leading-relaxed text-white/90">
                    <p>Macarrão pode fazer parte de uma refeição equilibrada! Mas vale ter atenção à composição do prato.</p>
                    <p>Uma dica importante é manter uma boa quantidade de proteína separada e bem definida na refeição, em vez de misturar tudo e acabar consumindo uma porção grande de massa com pouca proteína.</p>
                    <p>Combine o macarrão com uma fonte de proteína, como frango, carne magra ou outra opção de sua preferência, e procure incluir vegetais, como brócolis, tomate, cenoura e abobrinha.</p>
                    <p class="rounded-2xl border border-yellow-400/40 bg-yellow-400/5 p-4 font-semibold text-yellow-400">Pense na proporção: massa + proteína + vegetais.</p>
                    <p>Assim, você consegue aproveitar o macarrão sem deixar de lado outros componentes importantes da refeição, favorecendo também a saciedade.</p>
                    <p>As fotos abaixo trazem algumas ideias de combinações simples para variar no dia a dia! 🧡</p>
                </div>
                <div v-if="group.id === 'sopas'" class="space-y-4 text-base leading-relaxed text-white/90">
                    <p>Uma ótima maneira de variar as refeições é transformar a combinação tradicional de um prato de comida em uma sopa ou caldo!</p>
                    <p>Você pode combinar vegetais, tubérculos e uma fonte de proteína, como frango desfiado ou carne magra, criando preparações nutritivas e saborosas.</p>
                    <p>Abóbora, batata, mandioca, cenoura, chuchu e outros vegetais podem entrar nessas combinações. Varie os ingredientes e experimente diferentes temperos para não cair sempre na mesma receita.</p>
                    <p class="rounded-2xl border border-yellow-400/40 bg-yellow-400/5 p-4"><strong class="text-yellow-400">Dica de organização:</strong> prepare uma quantidade maior e congele em porções individuais. Assim, você terá opções práticas para os dias mais corridos.</p>
                    <p>E, se gostar, uma pequena porção de pão pode acompanhar a sopa e complementar a refeição.</p>
                    <p class="font-semibold text-yellow-400">Praticidade também é deixar boas escolhas disponíveis para quando a rotina apertar! 🧡</p>
                </div>
                <ul class="grid items-start gap-4" :class="group.gallery ? 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4' : 'lg:grid-cols-2'">
                    <li v-for="meal in group.meals" :key="meal.id" class="flex h-full flex-col items-center gap-3 rounded-3xl border border-white/10 bg-gradient-to-r from-[#131313] to-[#252525] p-3" :class="!group.gallery ? 'sm:flex-row' : ''">
                        <Dialog>
                            <DialogTrigger as-child>
                                <button type="button" class="group relative w-full shrink-0 overflow-hidden rounded-2xl bg-black/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-yellow-400" :class="!group.gallery ? 'sm:w-40 xl:w-48' : ''" :aria-label="`Ampliar foto: ${meal.title || 'Bolinhos para congelar'}`">
                                    <svg v-if="meal.crop" :viewBox="meal.crop.viewBox" preserveAspectRatio="xMidYMid slice" class="aspect-square w-full overflow-hidden transition-transform group-hover:scale-105" :class="[57, 58].includes(meal.id) ? 'rounded-full' : ''" role="img" :aria-label="meal.title || 'Bolinhos para congelar'">
                                        <image :href="consultationMealImage(meal.id)" :width="meal.crop.width" :height="meal.crop.height" />
                                    </svg>
                                    <img v-else :src="consultationMealImage(meal.id)" :alt="meal.title || 'Bolinhos para congelar'" loading="lazy" decoding="async" class="w-full object-contain" :class="meal.id === 21 ? 'h-auto' : 'aspect-square transition-transform group-hover:scale-105'" />
                                    <span class="absolute bottom-2 right-2 rounded-full bg-black/80 p-2 text-yellow-400"><Maximize2 class="size-4" aria-hidden="true" /></span>
                                </button>
                            </DialogTrigger>
                            <DialogContent class="max-h-[90dvh] max-w-3xl overflow-y-auto border-brand-line2 bg-[#191919] text-white">
                                <DialogHeader>
                                    <DialogTitle class="pr-6 text-brand-gold">{{ group.title }}</DialogTitle>
                                    <DialogDescription v-if="group.gallery" class="text-brand-muted">Uma ideia de refeição real para inspirar suas combinações.</DialogDescription>
                                    <DialogDescription v-else class="text-brand-muted">{{ meal.sides.join(' ') }}</DialogDescription>
                                </DialogHeader>
                                <svg v-if="meal.crop" :viewBox="meal.crop.viewBox" preserveAspectRatio="xMidYMid slice" :width="meal.crop.viewBox.split(' ')[2]" :height="meal.crop.viewBox.split(' ')[3]" class="mx-auto max-h-[65dvh] w-full overflow-hidden" :class="[57, 58].includes(meal.id) ? 'rounded-full' : 'rounded-xl'" role="img" :aria-label="meal.title || 'Bolinhos para congelar'">
                                    <image :href="consultationMealImage(meal.id)" :width="meal.crop.width" :height="meal.crop.height" />
                                </svg>
                                <img v-else :src="consultationMealImage(meal.id)" :alt="meal.title || 'Bolinhos para congelar'" loading="lazy" class="mx-auto max-h-[65dvh] w-full rounded-xl object-contain" />
                            </DialogContent>
                        </Dialog>
                        <div v-if="group.id === 'outros'" class="flex w-full items-start gap-3 py-2">
                            <div class="min-w-0 space-y-2 text-base leading-relaxed">
                                <h3 v-if="meal.title" class="font-bold">{{ meal.title }}</h3>
                                <p v-for="description in meal.sides" :key="description">{{ description }}</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </section>
        </main>
    </AppLayout>
</template>
