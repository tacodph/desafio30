<script setup lang="ts">
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Administração', href: '/dashboard' },
    { title: 'Usuários', href: '/admin/usuarios' },
];

type ListedUser = { id: number; name: string; email: string; user_type: { name: string } | null; challenge_courtesy_at: string | null; challenge_paid_at: string | null; challenge_access_type: { code: string; label: string } | null; asaas_attestation_url: string | null };

const props = defineProps<{
    users: ListedUser[];
    accessTypes: { code: string; label: string }[];
}>();
const busyUser = ref<number | null>(null);
const accessError = ref('');
const attestationUser = ref<ListedUser | null>(null);
const proofUrl = ref('');
const manualAccessTypes = ['courtesy', 'external', 'asaas_attested'];
const accessLabel = (user: ListedUser) => user.user_type?.name !== 'atleta' ? '—' : user.challenge_access_type?.label ?? (user.challenge_courtesy_at ? 'Cortesia' : user.challenge_paid_at ? 'Acesso legado' : 'Pagamento pendente');
const accessCode = (user: ListedUser) => user.challenge_access_type?.code ?? (user.challenge_courtesy_at ? 'courtesy' : user.challenge_paid_at ? 'legacy' : 'automatic');
const filters = reactive({ name: '', email: '', type: '', registration: '', access: '' });
const releaseLabel = (user: ListedUser) => {
    if (user.user_type?.name !== 'atleta') return 'Não se aplica';
    if (accessCode(user) === 'automatic') return 'Sem liberação manual';
    return accessLabel(user);
};
const fields = [
    { key: 'name', label: 'Nome', value: (user: ListedUser) => user.name, search: true },
    { key: 'email', label: 'E-mail', value: (user: ListedUser) => user.email, search: true },
    { key: 'type', label: 'Tipo', value: (user: ListedUser) => user.user_type?.name ?? 'Sem tipo' },
    { key: 'registration', label: 'Inscrição', value: (user: ListedUser) => user.user_type?.name === 'atleta' ? accessLabel(user) : 'Não se aplica' },
    { key: 'access', label: 'Tipo de liberação', value: releaseLabel },
] as const;
const filterFields = computed(() => fields.map((field) => ({
    ...field,
    options: 'search' in field ? [] : [...new Set(props.users.map(field.value))].sort((a, b) => a.localeCompare(b, 'pt-BR')),
})));
const normalizeSearch = (value: string) => value.trim().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
const filteredUsers = computed(() => props.users.filter((user) => fields.every((field) => {
    const filter = filters[field.key];
    if (!filter) return true;

    const value = field.value(user);
    return 'search' in field ? normalizeSearch(value).includes(normalizeSearch(filter)) : value === filter;
})));
const saveAccess = (user: ListedUser, accessType: string, attestationUrl?: string) => {
    if (busyUser.value !== null) return;
    accessError.value = '';
    router.patch(route('admin.users.access', user.id), { access_type: accessType, attestation_url: attestationUrl }, {
        preserveScroll: true,
        onStart: () => { busyUser.value = user.id; },
        onSuccess: () => { attestationUser.value = null; },
        onError: (errors) => { accessError.value = Object.values(errors)[0] ?? 'Não foi possível alterar a liberação.'; },
        onFinish: () => { busyUser.value = null; },
    });
};

const openAttestation = (user: ListedUser) => {
    accessError.value = '';
    proofUrl.value = user.asaas_attestation_url ?? '';
    attestationUser.value = user;
};

const changeAccess = (user: ListedUser, event: Event) => {
    const select = event.target as HTMLSelectElement;
    const accessType = select.value;
    select.value = accessCode(user);
    if (accessType === 'asaas_attested') openAttestation(user);
    else saveAccess(user, accessType);
};

const saveAttestation = () => {
    if (attestationUser.value) saveAccess(attestationUser.value, 'asaas_attested', proofUrl.value.trim());
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

            <p v-if="accessError && !attestationUser" role="alert" class="text-sm text-red-400">{{ accessError }}</p>

            <div class="surface-card grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 xl:grid-cols-5" role="group" aria-label="Filtros de usuários">
                <label v-for="field in filterFields" :key="field.key" class="grid gap-1 text-sm font-semibold">
                    {{ field.label }}
                    <input v-if="'search' in field" v-model="filters[field.key]" type="search" class="min-w-0 w-full rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2" />
                    <select v-else v-model="filters[field.key]" class="min-w-0 w-full rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2">
                        <option value="">Todos</option>
                        <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
                    </select>
                </label>
                <button type="button" class="w-fit text-sm font-bold text-brand-gold underline" @click="Object.assign(filters, { name: '', email: '', type: '', registration: '', access: '' })">Limpar filtros</button>
            </div>
            <p v-if="!filteredUsers.length" role="status" class="text-sm text-brand-muted">Nenhum usuário encontrado.</p>

            <ul class="m-0 flex list-none flex-col gap-3 p-0 md:hidden">
                <li v-for="user in filteredUsers" :key="user.id" class="surface-card flex flex-col gap-1 p-4">
                    <span class="font-semibold">{{ user.name }}</span>
                    <span class="break-all text-sm text-brand-muted">{{ user.email }}</span>
                    <span class="text-xs font-bold uppercase tracking-[2px] text-brand-gold">{{ user.user_type?.name }}</span>
                    <span class="mt-2 text-sm font-semibold">{{ accessLabel(user) }}</span>
                    <select v-if="user.user_type?.name === 'atleta'" :value="accessCode(user)" :disabled="busyUser !== null" :aria-label="`Tipo de liberação de ${user.name}`" class="mt-2 w-full rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2 text-sm" @change="changeAccess(user, $event)">
                        <option value="automatic">Sem liberação manual</option>
                        <option v-for="type in accessTypes" :key="type.code" :value="type.code" :disabled="!manualAccessTypes.includes(type.code)">{{ type.label }}</option>
                    </select>
                    <a v-if="user.asaas_attestation_url" :href="user.asaas_attestation_url" target="_blank" rel="noopener noreferrer" class="mt-2 w-fit text-sm font-bold text-brand-gold underline">Ver comprovante</a>
                    <button v-if="accessCode(user) === 'asaas_attested'" type="button" class="mt-2 w-fit text-sm font-bold underline" @click="openAttestation(user)">Editar link do comprovante</button>
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
                        <tr v-for="user in filteredUsers" :key="user.id" class="border-t border-brand-line">
                            <td class="px-5 py-4 font-semibold">{{ user.name }}</td>
                            <td class="px-5 py-4 text-brand-muted">{{ user.email }}</td>
                            <td class="px-5 py-4 uppercase">{{ user.user_type?.name }}</td>
                            <td class="px-5 py-4 font-semibold">{{ accessLabel(user) }}</td>
                            <td class="px-5 py-4">
                                <select v-if="user.user_type?.name === 'atleta'" :value="accessCode(user)" :disabled="busyUser !== null" :aria-label="`Tipo de liberação de ${user.name}`" class="w-full rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2 text-sm" @change="changeAccess(user, $event)">
                                    <option value="automatic">Sem liberação manual</option>
                                    <option v-for="type in accessTypes" :key="type.code" :value="type.code" :disabled="!manualAccessTypes.includes(type.code)">{{ type.label }}</option>
                                </select>
                                <a v-if="user.asaas_attestation_url" :href="user.asaas_attestation_url" target="_blank" rel="noopener noreferrer" class="mt-2 block w-fit font-bold text-brand-gold underline">Ver comprovante</a>
                                <button v-if="accessCode(user) === 'asaas_attested'" type="button" class="mt-2 block w-fit font-bold underline" @click="openAttestation(user)">Editar link do comprovante</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <Dialog :open="Boolean(attestationUser)" @update:open="!$event && busyUser === null && (attestationUser = null)">
            <DialogContent class="border-brand-line2 bg-brand-card text-brand-cream">
                <DialogHeader>
                    <DialogTitle>Atestar pagamento no Asaas</DialogTitle>
                    <DialogDescription>Registre o comprovante de {{ attestationUser?.name }} para liberar o acesso enquanto o site aguarda a confirmação do Asaas.</DialogDescription>
                </DialogHeader>
                <form class="grid gap-3" @submit.prevent="saveAttestation">
                    <label for="asaas-proof-url" class="text-sm font-bold">Link do comprovante</label>
                    <input id="asaas-proof-url" v-model="proofUrl" type="url" required maxlength="2048" placeholder="https://…" class="w-full rounded-lg border border-brand-line2 bg-brand-card2 px-3 py-2 text-sm" />
                    <p v-if="accessError" role="alert" class="text-sm text-red-400">{{ accessError }}</p>
                    <button type="submit" :disabled="busyUser !== null || !proofUrl.trim()" class="w-fit rounded-lg bg-brand-gold px-4 py-2 text-sm font-bold text-brand-ink disabled:opacity-60">{{ busyUser !== null ? 'Salvando…' : 'Salvar ateste' }}</button>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
