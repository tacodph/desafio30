<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Administração', href: '/dashboard' },
    { title: 'Usuários', href: '/admin/usuarios' },
    { title: 'Cadastro', href: '/admin/usuarios/novo' },
];

const props = defineProps<{
    types: { id: number; name: string }[];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    user_type_id: '',
    courtesy: false,
});

const isAthlete = computed(() => props.types.find((type) => type.id === Number(form.user_type_id))?.name === 'atleta');
watch(isAthlete, (athlete) => { if (!athlete) form.courtesy = false; });

const submit = () => {
    form.post(route('admin.users.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Cadastrar usuário" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <h1 class="display-title text-[clamp(36px,5vw,56px)]">Cadastrar usuário</h1>

            <form class="surface-card grid max-w-xl gap-5 p-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">Nome</Label>
                    <Input id="name" v-model="form.name" type="text" required autofocus autocomplete="name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">E-mail</Label>
                    <Input id="email" v-model="form.email" type="email" required autocomplete="email" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="user_type_id">Tipo</Label>
                    <select
                        id="user_type_id"
                        v-model="form.user_type_id"
                        required
                        class="flex h-11 w-full rounded-xl border border-brand-line2 bg-brand-card2 px-3 text-base text-brand-cream focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-gold md:text-sm"
                    >
                        <option value="" disabled>Escolha o tipo</option>
                        <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                    </select>
                    <InputError :message="form.errors.user_type_id" />
                </div>

                <div v-if="isAthlete" class="grid gap-2 rounded-xl border border-brand-line2 p-4">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input v-model="form.courtesy" type="checkbox" class="mt-1 size-5 shrink-0 accent-brand-gold" />
                        <span><span class="block font-semibold">Atleta cortesia</span><span class="mt-1 block text-sm leading-relaxed text-brand-muted">Libera a participação sem pagamento no Asaas. A inscrição ficará identificada como cortesia.</span></span>
                    </label>
                    <InputError :message="form.errors.courtesy" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Senha</Label>
                    <Input id="password" v-model="form.password" type="password" required autocomplete="new-password" />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar senha</Label>
                    <Input id="password_confirmation" v-model="form.password_confirmation" type="password" required autocomplete="new-password" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button type="submit" :disabled="form.processing">Cadastrar</Button>
            </form>
        </div>
    </AppLayout>
</template>
