<script setup lang="ts">
import { TransitionRoot } from '@headlessui/vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    className?: string;
    paymentInfo: {
        status: 'novo' | 'pendente' | 'vencido' | 'confirmado' | 'reembolsado';
        value: number | string;
        dueDate: string | null;
    } | null;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const preview = ref(user.avatar ?? '');

const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null as File | null,
});

const onAvatar = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.avatar = file;
    preview.value = file ? URL.createObjectURL(file) : (user.avatar ?? '');
};

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};

const paymentStatus = (status: NonNullable<Props['paymentInfo']>['status']) =>
    ({
        novo: 'Pagamento ainda não iniciado',
        pendente: 'Aguardando pagamento',
        vencido: 'Pagamento vencido',
        confirmado: 'Pagamento confirmado',
        reembolsado: 'Pagamento reembolsado',
    })[status];

const paymentValue = (value: number | string) => Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="avatar">Foto do perfil</Label>
                        <div class="mt-1 flex items-center gap-4">
                            <img v-if="preview" :src="preview" alt="" class="size-16 rounded-full object-cover" />
                            <span v-else class="flex size-16 items-center justify-center rounded-full bg-brand-line text-sm font-bold">
                                {{ user.name.slice(0, 1) }}
                            </span>
                            <Input id="avatar" type="file" accept="image/*" class="block w-full" @change="onAvatar" />
                        </div>
                        <InputError class="mt-2" :message="form.errors.avatar" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name" placeholder="Full name" />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="mt-2 text-sm text-neutral-800">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="focus:outline-hidden rounded-md text-sm text-neutral-600 underline hover:text-neutral-900 focus:ring-2 focus:ring-offset-2"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <TransitionRoot
                            :show="form.recentlySuccessful"
                            enter="transition ease-in-out"
                            enter-from="opacity-0"
                            leave="transition ease-in-out"
                            leave-to="opacity-0"
                        >
                            <p class="text-sm text-neutral-600">Saved.</p>
                        </TransitionRoot>
                    </div>
                </form>

                <section v-if="props.paymentInfo" class="rounded-xl border border-sidebar-border p-4">
                    <HeadingSmall title="Dados de pagamento" description="Acompanhe o status da inscrição no Desafio 30." />
                    <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm text-muted-foreground">Status</dt>
                            <dd class="font-semibold">{{ paymentStatus(props.paymentInfo.status) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-muted-foreground">Valor da inscrição</dt>
                            <dd class="font-semibold">{{ paymentValue(props.paymentInfo.value) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-muted-foreground">Vencimento</dt>
                            <dd class="font-semibold">{{ props.paymentInfo.dueDate ?? 'Ainda não definido' }}</dd>
                        </div>
                    </dl>
                    <Link
                        v-if="props.paymentInfo.status !== 'confirmado'"
                        :href="route('pagamento.show')"
                        class="mt-4 inline-flex text-sm font-semibold text-primary underline underline-offset-4"
                    >
                        Ver pagamento
                    </Link>
                </section>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
