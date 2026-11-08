<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ contactEmail: string | null; status?: string }>();

const form = useForm({ name: '', email: '', subject: '', message: '' });
const submit = () => form.post(route('suporte.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
});
</script>

<template>
    <Head title="Suporte" />
    <main class="min-h-screen bg-brand-ink px-5 py-10 text-brand-cream sm:py-16">
        <div class="mx-auto max-w-xl">
            <Link :href="route('home')" class="text-sm text-brand-gold hover:underline">← Voltar ao início</Link>
            <p class="mt-10 text-sm font-bold uppercase tracking-[3px] text-brand-gold">Fale com a gente</p>
            <h1 class="mt-3 text-4xl font-black">Como podemos ajudar?</h1>
            <p class="mt-4 text-brand-cream/70">Envie sua dúvida sobre o Desafio da Virada. Informe seu email para receber nossa resposta.</p>
            <p v-if="contactEmail" class="mt-4 text-sm">
                Email de contato:
                <a :href="`mailto:${contactEmail}`" class="break-all text-brand-gold underline">{{ contactEmail }}</a>
            </p>
            <p v-if="status" role="status" class="surface-card mt-6 p-4 text-brand-gold">{{ status }}</p>
            <p v-if="!contactEmail" role="status" class="surface-card mt-6 p-4">O atendimento por este formulário estará disponível em breve.</p>
            <form class="surface-card mt-8 flex flex-col gap-5 p-6 sm:p-8" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">Nome</Label>
                    <Input id="name" v-model="form.name" autocomplete="name" required maxlength="120" :aria-invalid="!!form.errors.name" aria-describedby="name-error" />
                    <InputError id="name-error" :message="form.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email para contato</Label>
                    <Input id="email" v-model="form.email" type="email" autocomplete="email" required maxlength="254" placeholder="voce@exemplo.com" :aria-invalid="!!form.errors.email" aria-describedby="email-error" />
                    <InputError id="email-error" :message="form.errors.email" />
                </div>
                <div class="grid gap-2">
                    <Label for="subject">Assunto</Label>
                    <Input id="subject" v-model="form.subject" required maxlength="150" :aria-invalid="!!form.errors.subject" aria-describedby="subject-error" />
                    <InputError id="subject-error" :message="form.errors.subject" />
                </div>
                <div class="grid gap-2">
                    <Label for="message">Como podemos ajudar?</Label>
                    <textarea id="message" v-model="form.message" required maxlength="5000" rows="6" class="w-full rounded-md border border-input bg-background px-3 py-2 text-base shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" :aria-invalid="!!form.errors.message" aria-describedby="message-error" />
                    <InputError id="message-error" :message="form.errors.message" />
                </div>
                <Button type="submit" :disabled="form.processing || !contactEmail">{{ form.processing ? 'Enviando…' : 'Enviar mensagem' }}</Button>
            </form>
        </div>
    </main>
</template>
