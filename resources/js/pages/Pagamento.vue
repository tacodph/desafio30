<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { images } from '@/data/images';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, watch } from 'vue';

type PaymentState = 'novo' | 'pendente' | 'vencido' | 'confirmado' | 'reembolsado';

const props = defineProps<{
    price: number;
    needsCpf: boolean;
    payment: {
        state: PaymentState;
        dueDate: string | null;
        billingType: string | null;
    };
}>();

const page = usePage();
const asaasError = computed(() => (page.props.errors as Record<string, string> | undefined)?.asaas);

const priceLabel = computed(() => props.price.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }));

const form = useForm({ cpf: '', billing_type: props.payment.billingType === 'CREDIT_CARD' ? 'CREDIT_CARD' : 'PIX' });
const billingMethodLabels: Record<string, string> = { PIX: 'Pix', CREDIT_CARD: 'Cartão de crédito', BOLETO: 'Boleto' };
const pendingMethodLabel = computed(() => billingMethodLabels[props.payment.billingType ?? ''] ?? 'Forma de pagamento a escolher');

const maskCpf = (value: string) => {
    const d = value.replace(/\D/g, '').slice(0, 11);
    return d
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d)/, '.$1-$2');
};

const onCpfInput = (event: Event) => {
    form.cpf = maskCpf((event.target as HTMLInputElement).value);
};

const submit = (changeBillingType = false) => {
    if (form.processing) return;

    // Abre a aba durante o clique para evitar bloqueios de pop-up após a requisição.
    const checkout = window.open('about:blank', '_blank');
    if (checkout) {
        checkout.opener = null;
        checkout.document.title = 'Preparando pagamento';
        checkout.document.body.textContent = 'Preparando seu pagamento no Asaas. Aguarde…';
    }

    form.transform((data) => ({ ...data, change_billing_type: changeBillingType })).post(route('pagamento.store'), {
        preserveScroll: true,
        onSuccess: () => {
            if (props.payment.state === 'pendente') {
                if (checkout && !checkout.closed) checkout.location.replace(route('pagamento.pagar'));
            } else if (checkout && !checkout.closed) {
                checkout.close();
            }
        },
        onError: () => { if (checkout && !checkout.closed) checkout.close(); },
        onCancel: () => { if (checkout && !checkout.closed) checkout.close(); },
    });
};

// Enquanto aguarda o pagamento, consulta o servidor (o webhook do Asaas libera o acesso).
let poll: ReturnType<typeof setInterval> | undefined;
let redirect: ReturnType<typeof setTimeout> | undefined;


const stopPolling = () => {
    if (poll) {
        clearInterval(poll);
        poll = undefined;
    }
};

const startPolling = () => {
    stopPolling();
    poll = setInterval(() => router.reload({ only: ['payment'] }), 5000);
};

const verifyNow = () => router.get(route('pagamento.retorno'));

watch(
    () => props.payment.state,
    (state) => {
        if (state === 'pendente') {
            startPolling();
        } else {
            stopPolling();
        }
        if (state === 'confirmado') {
            redirect = setTimeout(() => router.visit(route('dashboard')), 3000);
        }
    },
    { immediate: true },
);

onMounted(() => {
    if (props.payment.state === 'pendente') startPolling();
});

onBeforeUnmount(() => {
    stopPolling();
    if (redirect) clearTimeout(redirect);

});
</script>

<template>
    <Head title="Pagamento | Desafio da Virada" />

    <div class="min-h-screen bg-brand-ink text-brand-cream">
        <header class="border-b border-brand-line">
            <div class="mx-auto flex max-w-[760px] items-center justify-between px-6 py-4">
                <Link :href="route('home')" class="flex items-center gap-2.5 text-brand-cream hover:text-brand-cream">
                    <img :src="images.logoMark" alt="" class="size-11 object-contain" />
                    <span class="flex flex-col font-display font-extrabold uppercase italic leading-none tracking-wide">
                        <span class="text-[22px]">Desafio <span class="text-brand-gold">30</span> Dias</span>
                        <span class="text-[13px] text-brand-muted-3">Virada</span>
                    </span>
                </Link>
                <Link :href="route('logout')" method="post" as="button" class="text-sm font-semibold text-brand-muted-3 hover:text-brand-cream">
                    Sair
                </Link>
            </div>
        </header>

        <main class="mx-auto flex max-w-[760px] flex-col gap-8 px-6 py-12" aria-live="polite">
            <!-- Confirmado -->
            <section v-if="payment.state === 'confirmado'" class="flex flex-col gap-4 rounded-[28px] bg-brand-gold px-7 py-8 text-brand-ink">
                <span class="text-sm font-bold uppercase tracking-[2px]">Pagamento confirmado</span>
                <p class="display-title m-0 text-[clamp(40px,7vw,64px)] leading-none">Você está dentro!</p>
                <p class="m-0 max-w-[460px] text-[17px] font-semibold leading-snug">
                    Recebemos sua inscrição no Desafio da Virada. Estamos levando você para o app.
                </p>
                <Link
                    :href="route('dashboard')"
                    class="mt-2 inline-flex w-fit items-center rounded-full bg-brand-ink px-7 py-4 text-[17px] font-bold text-brand-cream hover:bg-brand-line hover:text-brand-cream"
                >
                    Entrar agora
                </Link>
            </section>

            <template v-else>
                <div class="flex flex-col gap-4">
                    <div class="text-sm font-bold uppercase tracking-[3px] text-brand-gold">9 nov → 8 dez 2026</div>
                    <h1 class="display-title m-0 text-[clamp(40px,7vw,64px)] leading-[0.95]">Falta só o pagamento para entrar no Desafio da Virada</h1>
                    <p class="m-0 text-[18px] leading-relaxed text-brand-muted-3">
                        Um pagamento para os 30 dias. Você escolhe Pix, boleto ou cartão. Assim que o pagamento for confirmado, o acesso é liberado.
                    </p>
                </div>

                <div v-if="asaasError" role="alert" class="rounded-[22px] border border-red-500/40 bg-red-500/10 px-5 py-4 text-[16px] text-red-200">
                    {{ asaasError }}
                </div>

                <!-- Reembolsado -->
                <section v-if="payment.state === 'reembolsado'" class="rounded-[22px] border border-brand-line2 bg-brand-card2 px-5 py-4">
                    <p class="m-0 text-[16px] text-brand-muted-3">
                        Seu pagamento foi reembolsado e o acesso está suspenso. Se quiser voltar para o desafio, fale com a equipe.
                    </p>
                </section>

                <!-- Pendente -->
                <section v-else-if="payment.state === 'pendente'" class="flex flex-col gap-4 rounded-[28px] bg-brand-gold px-7 py-8 text-brand-ink">
                    <h2 class="m-0 text-sm font-bold uppercase tracking-[2px]">SUA INSCRIÇÃO NO DESAFIO DA VIRADA</h2>
                    <p class="display-title m-0 text-[clamp(48px,8vw,80px)] font-extrabold italic leading-none">{{ priceLabel }}</p>
                    <p class="m-0 text-[20px] font-bold">Menos de R$ 2,70 por dia!</p>
                    <p class="m-0 text-[17px] font-bold leading-snug">Pagamento único para participar dos 30 dias.</p>
                    <p class="m-0 max-w-[460px] text-[17px] font-semibold leading-snug">{{ pendingMethodLabel }}. Vence em {{ payment.dueDate }}.</p>
                    <form class="flex flex-wrap items-end gap-3" @submit.prevent="submit(true)">
                        <div class="flex flex-col gap-1.5">
                            <label for="pending-billing-type" class="text-sm font-bold">Trocar forma de pagamento</label>
                            <select id="pending-billing-type" v-model="form.billing_type" class="rounded-xl border-2 border-brand-ink/20 bg-white px-4 py-3 text-brand-ink">
                                <option value="PIX">Pix</option>
                                <option value="CREDIT_CARD">Cartão de crédito</option>
                            </select>
                        </div>
                        <button type="submit" :disabled="form.processing || form.billing_type === payment.billingType" class="rounded-xl bg-brand-ink px-4 py-3 text-sm font-bold text-brand-cream disabled:opacity-60">{{ form.processing ? 'Atualizando…' : 'Atualizar e pagar' }}</button>
                    </form>
                    <p class="m-0 text-sm font-semibold leading-relaxed">O Asaas abre em outra aba. Se a aba não abriu, clique em Pagar. Esta página acompanha a confirmação automaticamente.</p>
                    <div class="mt-2 flex flex-wrap items-center gap-4">
                        <a
                            :href="route('pagamento.pagar')"
                            target="_blank"
                            rel="noopener"

                            class="inline-flex w-fit items-center rounded-full bg-brand-ink px-7 py-4 text-[17px] font-bold text-brand-cream hover:bg-brand-line hover:text-brand-cream focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-ink"
                        >
                            Pagar {{ priceLabel }}
                        </a>
                        <button type="button" class="text-[15px] font-bold underline underline-offset-4" @click="verifyNow">
                            Já paguei, verificar agora
                        </button>
                    </div>
                    <p class="m-0 flex items-center gap-2 text-sm font-semibold">
                        <LoaderCircle class="size-4 animate-spin" aria-hidden="true" />
                        Aguardando a confirmação do pagamento...
                    </p>
                </section>

                <!-- Novo / vencido -->
                <form v-else class="flex flex-col gap-4 rounded-[28px] bg-brand-gold px-7 py-8 text-brand-ink" @submit.prevent="submit()">
                    <h2 class="m-0 text-sm font-bold uppercase tracking-[2px]">SUA INSCRIÇÃO NO DESAFIO DA VIRADA</h2>
                    <p class="display-title m-0 text-[clamp(48px,8vw,80px)] font-extrabold italic leading-none">{{ priceLabel }}</p>
                    <p class="m-0 text-[20px] font-bold">Menos de R$ 2,70 por dia!</p>
                    <p class="m-0 text-[17px] font-bold leading-snug">Pagamento único para participar dos 30 dias.</p>
                    <p v-if="payment.state === 'vencido'" class="m-0 max-w-[460px] text-[17px] font-semibold leading-snug">
                        Seu pagamento anterior venceu. Gere um novo para continuar.
                    </p>

                    <div class="flex max-w-[360px] flex-col gap-1.5">
                        <label for="billing-type" class="text-[15px] font-bold">Forma de pagamento</label>
                        <select id="billing-type" v-model="form.billing_type" class="rounded-xl border-2 border-brand-ink/20 bg-white px-4 py-3 text-brand-ink">
                            <option value="PIX">Pix</option>
                            <option value="CREDIT_CARD">Cartão de crédito</option>
                        </select>
                        <InputError :message="form.errors.billing_type" />
                    </div>

                    <div v-if="needsCpf" class="flex max-w-[360px] flex-col gap-1.5">
                        <label for="cpf" class="text-[15px] font-bold">CPF</label>
                        <input
                            id="cpf"
                            :value="form.cpf"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="000.000.000-00"
                            class="rounded-xl border-2 border-brand-ink/20 bg-white px-4 py-3 text-[17px] text-brand-ink outline-none focus:border-brand-ink"
                            @input="onCpfInput"
                        />
                        <InputError :message="form.errors.cpf" />
                        <p class="m-0 text-sm font-semibold leading-snug">
                            O CPF é exigido para emitir a cobrança. Ele é enviado ao Asaas e não fica salvo aqui.
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-2 inline-flex w-fit items-center gap-2 rounded-full bg-brand-ink px-7 py-4 text-[17px] font-bold text-brand-cream hover:bg-brand-line hover:text-brand-cream disabled:opacity-60"
                    >
                        <LoaderCircle v-if="form.processing" class="size-4 animate-spin" aria-hidden="true" />
                        {{ form.processing ? 'Gerando pagamento…' : payment.state === 'vencido' ? 'Gerar novo pagamento' : 'Gerar pagamento' }}
                    </button>
                </form>
            </template>
        </main>
    </div>
</template>
