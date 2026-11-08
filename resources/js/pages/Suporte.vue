<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, SharedData } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ contactEmail: string | null; accountEmail: string | null; accountName: string | null; status?: string }>();
const page = usePage<SharedData>();
const loggedIn = computed(() => Boolean(page.props.auth.user));
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Painel', href: '/dashboard' }, { title: 'Suporte', href: '/suporte' }];

const form = useForm({ name: props.accountName ?? '', email: props.accountEmail ?? '', subject: '', message: '' });
const submit = () => form.post(route('suporte.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
});
</script>

<template>
    <Head title="Suporte" />
    <component :is="loggedIn ? AppLayout : 'div'" v-bind="loggedIn ? { breadcrumbs } : {}">
    <main class="w-full text-brand-cream" :class="loggedIn ? 'p-4 md:p-8' : 'min-h-screen bg-brand-ink px-5 py-10 sm:py-16'">
        <div class="mx-auto max-w-xl">
            <Link :href="loggedIn ? route('dashboard') : route('home')" class="text-sm text-brand-gold hover:underline">{{ loggedIn ? '← Voltar ao painel' : '← Voltar ao início' }}</Link>
            <p class="mt-10 text-sm font-bold uppercase tracking-[3px] text-brand-gold">Fale com a gente</p>
            <h1 class="mt-3 text-4xl font-black">Como podemos ajudar?</h1>
            <p class="mt-4 text-brand-cream/70">Envie sua dúvida sobre o Desafio da Virada. {{ accountEmail ? 'Nossa resposta será enviada para o email cadastrado na sua conta.' : 'Informe seu email para receber nossa resposta.' }}</p>
            <p v-if="contactEmail" class="mt-4 text-sm">
                Email de contato:
                <a :href="`mailto:${contactEmail}`" class="break-all text-brand-gold underline">{{ contactEmail }}</a>
            </p>
            <p v-if="status" role="status" class="surface-card mt-6 p-4 text-brand-gold">{{ status }}</p>
            <form class="surface-card mt-8 flex flex-col gap-5 p-6 sm:p-8" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">Nome</Label>
                    <Input id="name" v-model="form.name" autocomplete="name" required maxlength="120" :aria-invalid="!!form.errors.name" aria-describedby="name-error" />
                    <InputError id="name-error" :message="form.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email para contato</Label>
                    <Input id="email" v-model="form.email" :readonly="!!accountEmail" type="email" autocomplete="email" required maxlength="254" placeholder="voce@exemplo.com" :aria-invalid="!!form.errors.email" :aria-describedby="accountEmail ? 'email-help email-error' : 'email-error'" :class="{ 'bg-muted/50': accountEmail }" />
                    <p v-if="accountEmail" id="email-help" class="text-xs text-brand-cream/60">Email cadastrado na sua conta. Este campo não pode ser alterado.</p>
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
                <Button type="submit" :disabled="form.processing">{{ form.processing ? 'Enviando…' : 'Enviar mensagem' }}</Button>
            </form>
        </div>
    </main>
    </component>
</template>
