<script setup lang="ts">
import ReleaseWelcomeModal from '@/components/ReleaseWelcomeModal.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const previewKey = ref(0);
</script>

<template>
    <Head title="Boas-vindas com vídeo" />
    <AppLayout :breadcrumbs="[{ title: 'Mensagens', href: '#' }, { title: 'Boas-vindas com vídeo', href: '/admin/mensagens/boas-vindas-video' }]">
        <main class="mx-auto flex w-full max-w-3xl flex-col gap-5 p-4 md:p-8">
            <div class="eyebrow">Mensagens</div>
            <h1 class="text-3xl font-black text-brand-gold">Boas-vindas com vídeo</h1>
            <p class="text-brand-muted">Esta mensagem aparece para o atleta após a liberação da plataforma, com vídeo e confetes. A confirmação de leitura é registrada para que ela não se repita.</p>
            <div class="surface-card space-y-4 p-6">
                <h2 class="text-xl font-bold">Prévia da mensagem</h2>
                <p class="text-sm text-brand-muted">Abra a prévia para conferir a mesma mensagem exibida aos atletas.</p>
                <Button @click="previewKey++">Visualizar mensagem</Button>
            </div>
            <ReleaseWelcomeModal :key="previewKey" preview />
        </main>
    </AppLayout>
</template>
