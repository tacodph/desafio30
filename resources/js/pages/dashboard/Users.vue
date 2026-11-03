<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Administração', href: '/dashboard' },
    { title: 'Usuários', href: '/admin/usuarios' },
];

type ListedUser = { id: number; name: string; email: string; user_type: { name: string } | null; challenge_courtesy_at: string | null; challenge_paid_at: string | null };

defineProps<{
    users: ListedUser[];
}>();
const busyUser = ref<number | null>(null);
const courtesyError = ref('');
const accessLabel = (user: ListedUser) => user.user_type?.name !== 'atleta' ? '—' : user.challenge_courtesy_at ? 'Cortesia' : user.challenge_paid_at ? 'Acesso liberado' : 'Pagamento pendente';
const toggleCourtesy = (user: ListedUser) => {
    if (busyUser.value !== null) return;
    courtesyError.value = '';
    router.patch(route('admin.users.courtesy', user.id), { courtesy: !user.challenge_courtesy_at }, {
        preserveScroll: true,
        onStart: () => { busyUser.value = user.id; },
        onError: (errors) => { courtesyError.value = Object.values(errors)[0] ?? 'Não foi possível alterar a cortesia.'; },
        onFinish: () => { busyUser.value = null; },
    });
};
</script>

<template>
    <Head title="Usuários" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h1 class="display-title text-[clamp(36px,5vw,56px)]">Usuários</h1>
                <Link :href="route('admin.users.create')" class="surface-card w-full px-5 py-4 text-center font-display text-lg font-bold uppercase sm:w-auto">
                    Cadastrar usuário
                </Link>
            </div>

            <p v-if="courtesyError" role="alert" class="text-sm text-red-400">{{ courtesyError }}</p>

            <ul class="m-0 flex list-none flex-col gap-3 p-0 md:hidden">
                <li v-for="user in users" :key="user.id" class="surface-card flex flex-col gap-1 p-4">
                    <span class="font-semibold">{{ user.name }}</span>
                    <span class="break-all text-sm text-brand-muted">{{ user.email }}</span>
                    <span class="text-xs font-bold uppercase tracking-[2px] text-brand-gold">{{ user.user_type?.name }}</span>
                    <span class="mt-2 text-sm font-semibold">{{ accessLabel(user) }}</span>
                    <button v-if="user.user_type?.name === 'atleta'" type="button" :disabled="busyUser !== null" class="mt-2 w-fit rounded-lg border border-brand-line2 px-3 py-2 text-sm font-bold text-brand-gold disabled:opacity-50" @click="toggleCourtesy(user)">
                        {{ busyUser === user.id ? 'Salvando…' : user.challenge_courtesy_at ? 'Remover cortesia' : 'Conceder cortesia' }}
                    </button>
                </li>
            </ul>

            <div class="surface-card hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="text-[13px] uppercase tracking-[2px] text-brand-muted-2">
                        <tr>
                            <th class="px-5 py-4 font-bold">Nome</th>
                            <th class="px-5 py-4 font-bold">E-mail</th>
                            <th class="px-5 py-4 font-bold">Tipo</th>
                            <th class="px-5 py-4 font-bold">Inscrição</th>
                            <th class="px-5 py-4 font-bold">Cortesia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-t border-brand-line">
                            <td class="px-5 py-4 font-semibold">{{ user.name }}</td>
                            <td class="px-5 py-4 text-brand-muted">{{ user.email }}</td>
                            <td class="px-5 py-4 uppercase">{{ user.user_type?.name }}</td>
                            <td class="px-5 py-4 font-semibold">{{ accessLabel(user) }}</td>
                            <td class="px-5 py-4">
                                <button v-if="user.user_type?.name === 'atleta'" type="button" :disabled="busyUser !== null" class="rounded-lg border border-brand-line2 px-3 py-2 text-sm font-bold text-brand-gold disabled:opacity-50" @click="toggleCourtesy(user)">
                                    {{ busyUser === user.id ? 'Salvando…' : user.challenge_courtesy_at ? 'Remover cortesia' : 'Conceder cortesia' }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
