<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Administração', href: '/dashboard' },
    { title: 'Usuários', href: '/admin/usuarios' },
];

type ListedUser = { id: number; name: string; email: string; user_type: { name: string } | null; challenge_courtesy_at: string | null; challenge_paid_at: string | null; challenge_access_type: { code: string; label: string } | null };

defineProps<{
    users: ListedUser[];
    accessTypes: { code: string; label: string }[];
}>();
const busyUser = ref<number | null>(null);
const accessError = ref('');
const accessLabel = (user: ListedUser) => user.user_type?.name !== 'atleta' ? '—' : user.challenge_access_type?.label ?? (user.challenge_courtesy_at ? 'Cortesia' : user.challenge_paid_at ? 'Acesso legado' : 'Pagamento pendente');
const accessCode = (user: ListedUser) => user.challenge_access_type?.code ?? (user.challenge_courtesy_at ? 'courtesy' : user.challenge_paid_at ? 'legacy' : 'automatic');
const changeAccess = (user: ListedUser, event: Event) => {
    if (busyUser.value !== null) return;
    const select = event.target as HTMLSelectElement;
    const accessType = select.value;
    select.value = accessCode(user);
    accessError.value = '';
    router.patch(route('admin.users.access', user.id), { access_type: accessType }, {
        preserveScroll: true,
        onStart: () => { busyUser.value = user.id; },
        onError: (errors) => { accessError.value = Object.values(errors)[0] ?? 'Não foi possível alterar a liberação.'; },
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

            <p v-if="accessError" role="alert" class="text-sm text-red-400">{{ accessError }}</p>

            <ul class="m-0 flex list-none flex-col gap-3 p-0 md:hidden">
                <li v-for="user in users" :key="user.id" class="surface-card flex flex-col gap-1 p-4">
                    <span class="font-semibold">{{ user.name }}</span>
                    <span class="break-all text-sm text-brand-muted">{{ user.email }}</span>
                    <span class="text-xs font-bold uppercase tracking-[2px] text-brand-gold">{{ user.user_type?.name }}</span>
                    <span class="mt-2 text-sm font-semibold">{{ accessLabel(user) }}</span>
                    <select v-if="user.user_type?.name === 'atleta'" :value="accessCode(user)" :disabled="busyUser !== null" :aria-label="`Tipo de liberação de ${user.name}`" class="mt-2 w-full rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2 text-sm" @change="changeAccess(user, $event)">
                        <option value="automatic">Sem liberação manual</option>
                        <option v-for="type in accessTypes" :key="type.code" :value="type.code" :disabled="!['courtesy', 'external'].includes(type.code)">{{ type.label }}</option>
                    </select>
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
                            <th class="px-5 py-4 font-bold">Tipo de liberação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in users" :key="user.id" class="border-t border-brand-line">
                            <td class="px-5 py-4 font-semibold">{{ user.name }}</td>
                            <td class="px-5 py-4 text-brand-muted">{{ user.email }}</td>
                            <td class="px-5 py-4 uppercase">{{ user.user_type?.name }}</td>
                            <td class="px-5 py-4 font-semibold">{{ accessLabel(user) }}</td>
                            <td class="px-5 py-4">
                                <select v-if="user.user_type?.name === 'atleta'" :value="accessCode(user)" :disabled="busyUser !== null" :aria-label="`Tipo de liberação de ${user.name}`" class="w-full rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2 text-sm" @change="changeAccess(user, $event)">
                                    <option value="automatic">Sem liberação manual</option>
                                    <option v-for="type in accessTypes" :key="type.code" :value="type.code" :disabled="!['courtesy', 'external'].includes(type.code)">{{ type.label }}</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
