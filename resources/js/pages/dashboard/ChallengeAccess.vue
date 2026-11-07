<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

type Mode = 'calendar' | 'manual' | 'closed';
const props = defineProps<{
    features: Record<string, string>;
    modes: Record<string, Mode>;
    releaseDate: string;
    startDate: string;
    endDate: string;
    environment: string;
    ready: boolean;
    saved: boolean;
    athleteSaved: boolean;
    athletes: { id: number; name: string; email: string; challenge_early_access: boolean }[];
}>();
const form = useForm({ modes: { ...props.modes }, password: '' });
const save = () => form.put(route('admin.challenge-access.update'), {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
});
const athleteForm = useForm({ athlete_id: 0, enabled: false, password: '' });
const selectedAthlete = computed(() => props.athletes?.find((athlete) => athlete.id === athleteForm.athlete_id));
const saveAthlete = () => {
    if (!selectedAthlete.value) return;

    athleteForm.enabled = !selectedAthlete.value.challenge_early_access;
    athleteForm.patch(route('admin.challenge-access.athlete'), {
        errorBag: 'athleteAccess',
        preserveScroll: true,
        onFinish: () => athleteForm.reset('password'),
    });
};
const restore = () => {
    Object.keys(props.features).forEach((key) => { form.modes[key] = 'calendar'; });
};
</script>

<template>
    <Head title="Liberações do desafio" />
    <AppLayout :breadcrumbs="[{ title: 'Administração', href: '/dashboard' }, { title: 'Liberações do desafio', href: '/admin/liberacoes' }]">
        <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4 md:p-6">
            <div class="flex flex-col gap-3">
                <div class="eyebrow">Administração · {{ environment }}</div>
                <h1 class="display-title text-[clamp(36px,5vw,56px)]">Liberações do desafio</h1>
                <p class="m-0 leading-relaxed text-brand-muted">No calendário normal, as páginas abrem em {{ releaseDate }}. Cada dia aceita lançamentos e fotos somente na sua própria data, de 00h até 23h59, pelo horário de Brasília.</p>
                <p class="m-0 text-sm text-brand-muted">Escolha quais recursos seguem o calendário, ficam liberados manualmente ou ficam bloqueados para os atletas. Na visualização administrativa, só o seu perfil tem acesso fora das datas.</p>
            </div>
            <div class="surface-card flex flex-col gap-3 p-5">
                <h2 class="m-0 text-lg font-bold">Visualização exclusiva do administrador</h2>
                <p class="m-0 text-sm leading-relaxed text-brand-muted">Consulte as páginas em qualquer data. Esta visualização não permite lançamentos nem muda as liberações dos atletas.</p>
                <div class="flex flex-wrap gap-3">
                    <Link :href="route('athlete.progress.tab', 'dias')" class="rounded-full bg-brand-gold px-5 py-3 text-sm font-bold text-brand-ink">Visualizar evolução</Link>
                    <Link :href="route('alimentacao.compras')" class="rounded-full border border-brand-line2 px-5 py-3 text-sm font-bold">Visualizar cardápio</Link>
                </div>
            </div>
            <form class="surface-card flex flex-col gap-4 p-5 sm:p-6" @submit.prevent="saveAthlete">
                <div>
                    <h2 class="text-lg font-bold">Acesso antecipado por atleta</h2>
                    <p class="text-sm text-brand-muted">Libera para o atleta selecionado as páginas que seguem o calendário. Bloqueios manuais e datas de lançamentos continuam valendo.</p>
                </div>
                <p v-if="athleteSaved" role="status" class="text-sm font-semibold text-brand-gold">Acesso do atleta atualizado.</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="grid min-w-0 gap-2">
                        <Label for="early-athlete">Atleta</Label>
                        <select id="early-athlete" v-model="athleteForm.athlete_id" required :disabled="athleteForm.processing" class="h-11 w-full rounded-xl border border-brand-line2 bg-brand-card2 px-3 text-brand-cream [color-scheme:dark]">
                            <option :value="0" disabled>Selecione um atleta</option>
                            <option v-for="athlete in athletes" :key="athlete.id" :value="athlete.id">
                                {{ athlete.name }} ({{ athlete.email }}){{ athlete.challenge_early_access ? ' · Liberado' : '' }}
                            </option>
                        </select>
                        <InputError :message="athleteForm.errors.athlete_id" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="early-password">Senha de administrador</Label>
                        <Input id="early-password" v-model="athleteForm.password" type="password" autocomplete="current-password" required :disabled="athleteForm.processing" />
                        <InputError :message="athleteForm.errors.password" />
                    </div>
                </div>
                <Button class="w-fit" type="submit" :disabled="!selectedAthlete || athleteForm.processing">
                    {{ selectedAthlete?.challenge_early_access ? 'Revogar acesso antecipado' : 'Liberar atleta' }}
                </Button>
            </form>
            <p v-if="!ready" role="alert" class="surface-card p-4 text-brand-gold">Os controles precisam da atualização do banco de dados. Execute php artisan migrate no servidor antes de salvar.</p>
            <p v-if="saved" role="status" class="surface-card p-4 text-brand-gold">Liberações atualizadas com sucesso.</p>
            <form class="surface-card flex flex-col gap-6 p-5 sm:p-6" @submit.prevent="save">
                <div v-for="(label, key) in features" :key="key" class="grid gap-2">
                    <Label :for="String(key)">{{ label }}</Label>
                    <select :id="String(key)" v-model="form.modes[key]" :disabled="!ready || form.processing" class="h-11 rounded-xl border border-brand-line2 bg-brand-card2 px-3 text-brand-cream [color-scheme:dark]">
                        <option value="calendar">Seguir calendário</option>
                        <option value="manual">{{ key === 'entries' ? 'Liberar lançamentos em todos os dias' : 'Liberar para atletas' }}</option>
                        <option value="closed">Bloquear</option>
                    </select>
                    <InputError :message="form.errors[('modes.' + key) as keyof typeof form.errors]" />
                </div>
                <div class="grid max-w-sm gap-2 border-t border-brand-line2 pt-5">
                    <Label for="admin-password">Confirme sua senha de administrador</Label>
                    <Input id="admin-password" v-model="form.password" type="password" autocomplete="current-password" required :disabled="!ready || form.processing" />
                    <InputError :message="form.errors.password" />
                </div>
                <div class="flex flex-wrap gap-3">
                    <Button type="submit" :disabled="!ready || form.processing">{{ form.processing ? 'Salvando…' : 'Salvar liberações' }}</Button>
                    <button type="button" :disabled="form.processing" class="rounded-xl border border-brand-line2 px-4 py-2 text-sm font-semibold" @click="restore">Restaurar calendário no formulário</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
