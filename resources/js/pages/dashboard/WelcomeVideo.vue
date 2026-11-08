<script setup lang="ts">
import ReleaseWelcomeModal from '@/components/ReleaseWelcomeModal.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const replay = ref(usePage().props.showReleaseWelcome !== true);
const replayKey = ref(0);
const showMessage = () => { replay.value = true; replayKey.value++; };
</script>

<template>
    <Head title="Boas-vindas com vídeo" />
    <AppLayout :breadcrumbs="[{ title: 'Boas-vindas', href: '/atleta/boas-vindas/video' }, { title: 'Mensagem com vídeo', href: '/atleta/boas-vindas/video' }]">
        <main class="mx-auto flex w-full max-w-3xl flex-col gap-5 p-4 md:p-8">
            <div class="eyebrow">Boas-vindas</div>
            <h1 class="text-3xl font-black text-brand-gold">Um recado da Helen para você ❤️</h1>
            <p class="text-brand-muted">Reveja o vídeo e a mensagem de boas-vindas ao Desafio da Virada sempre que quiser.</p>
            <section class="surface-card space-y-4 p-6">
                <h2 class="text-xl font-bold">Que venham nossos 30 dias!</h2>
                <p class="text-sm text-brand-muted">Conheça as dicas para aproveitar essa jornada e começar seus check-ins.</p>
                <Button @click="showMessage">Abrir boas-vindas com vídeo</Button>
            </section>
            <ReleaseWelcomeModal v-if="replay" :key="replayKey" preview />
        </main>
    </AppLayout>
</template>
