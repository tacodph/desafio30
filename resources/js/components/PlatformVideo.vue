<script setup lang="ts">
const videoUrl = '/videos/WhatsApp%20Video%202026-11-09%20at%2014.05.31.mp4';
</script>

<template>
    <section class="surface-card flex flex-col gap-4 p-5 sm:p-6" aria-labelledby="platform-video-title">
        <div class="space-y-2">
            <h2 id="platform-video-title" class="font-display text-xl font-extrabold text-brand-gold sm:text-2xl">Como funciona a plataforma</h2>
            <p class="text-sm text-brand-muted">Assista ao vídeo para conhecer a plataforma do Desafio da Virada.</p>
        </div>
        <video controls playsinline preload="none" class="max-h-[70dvh] w-full rounded-xl bg-black" aria-label="Como funciona a plataforma">
            <source :src="videoUrl" type="video/mp4" />
            Seu navegador não suporta a reprodução deste vídeo.
            <a :href="videoUrl">Abrir vídeo sobre a plataforma</a>
        </video>
    </section>
</template>
