<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

type Mode = 'calendar' | 'open' | 'closed';
const props = defineProps<{
    features: Record<string, string>;
    modes: Record<string, Mode>;
    releaseDate: string;
    startDate: string;
    endDate: string;
    environment: string;
    ready: boolean;
    saved: boolean;
}>();
const form = useForm({ modes: { ...props.modes }, password: '' });
const save = () => form.put(route('admin.challenge-access.update'), {
    preserveScroll: true,
    onFinish: () => form.reset('password'),
});
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
                <p class="m-0 text-sm text-brand-muted">As mudanças valem para todos os atletas deste ambiente. Para testar um dia fora da data, libere a página de dias e os lançamentos. Selecione “Seguir calendário” para restaurar as regras normais.</p>
            </div>
            <p v-if="!ready" role="alert" class="surface-card p-4 text-brand-gold">Os controles precisam da atualização do banco de dados. Execute php artisan migrate no servidor antes de salvar.</p>
            <p v-if="saved" role="status" class="surface-card p-4 text-brand-gold">Liberações atualizadas com sucesso.</p>
            <form class="surface-card flex flex-col gap-6 p-5 sm:p-6" @submit.prevent="save">
                <div v-for="(label, key) in features" :key="key" class="grid gap-2">
                    <Label :for="String(key)">{{ label }}</Label>
                    <select :id="String(key)" v-model="form.modes[key]" :disabled="!ready || form.processing" class="h-11 rounded-xl border border-brand-line2 bg-brand-card2 px-3 text-brand-cream [color-scheme:dark]">
                        <option value="calendar">Seguir calendário</option>
                        <option value="open">Liberar para testes</option>
                        <option value="closed">Bloquear</option>
                    </select>
                    <p v-if="form.modes[key] === 'open'" class="m-0 text-sm text-brand-gold">{{ key === 'entries' ? 'Todos os 30 dias poderão receber lançamentos, se a página de dias estiver liberada.' : 'Página disponível antes da data prevista.' }}</p>
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
