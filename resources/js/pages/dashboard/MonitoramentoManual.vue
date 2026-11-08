<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Apple, Droplets, Dumbbell, Lightbulb, Moon } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{ startDate: string; totalDays: number }>();
const habits = [
    { label: 'Alimentação', icon: Apple, color: '#319348' },
    { label: 'Movimento', icon: Dumbbell, color: '#aa7a00' },
    { label: 'Hidratação', icon: Droplets, color: '#1681b3' },
    { label: 'Sono', icon: Moon, color: '#bb4379' },
    { label: 'Habilidade', icon: Lightbulb, color: '#8055b4' },
];
const days = computed(() => {
    const start = new Date(`${props.startDate}T12:00:00`);
    const offset = (start.getDay() + 6) % 7;
    const dates = Array.from({ length: props.totalDays }, (_, index) => {
        const date = new Date(start);
        date.setDate(start.getDate() + index);
        return { day: index + 1, date: date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }) };
    });
    return [...Array(offset).fill(null), ...dates, ...Array((7 - ((offset + props.totalDays) % 7)) % 7).fill(null)];
});
const period = computed(() => days.value.filter(Boolean).map((day) => day.date).filter((_, index) => index === 0 || index === props.totalDays - 1).join(' a '));
const print = () => window.print();
</script>

<template>
    <Head title="Monitoramento de hábitos — preenchimento manual" />
    <div class="manual-page">
        <nav class="toolbar" aria-label="Opções de impressão">
            <a href="/cantinho-da-nutri/monitoramento">← Voltar ao monitoramento</a>
            <button type="button" @click="print">Imprimir / Salvar PDF</button>
            <p>Imprima esta versão em branco para preencher à mão. Use a opção “Salvar como PDF” da janela de impressão se preferir guardar o modelo.</p>
        </nav>
        <div class="sheet-scroll">
            <main class="sheet">
                <header class="sheet-header">
                    <div><p class="brand">Desafio da Virada · {{ totalDays }} dias</p><h1>Monitoramento de hábitos</h1><p>{{ period }}</p></div>
                    <img src="/images/logo-mark-30-virada.png" alt="Desafio da Virada" width="80" height="80" />
                </header>
                <p class="name-line">Atleta: <span /></p>
                <ul class="legend"><li v-for="habit in habits" :key="habit.label"><component :is="habit.icon" :style="{ color: habit.color }" aria-hidden="true" />{{ habit.label }}</li></ul>
                <p class="instructions">Marque os hábitos cumpridos e escreva um breve resumo em cada dia. Os espaços seguem a ordem da legenda.</p>
                <div class="weekdays"><div v-for="weekday in ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom']" :key="weekday">{{ weekday }}</div></div>
                <div class="calendar">
                    <div v-for="(day, index) in days" :key="index" class="day" :class="{ empty: !day, first: day?.day === 1, middle: day?.day === 15, last: day?.day === totalDays }">
                        <template v-if="day">
                            <div class="date"><strong>{{ day.date }}</strong><span>Dia {{ day.day }}</span></div>
                            <div class="boxes"><span v-for="habit in habits" :key="habit.label" :style="{ borderColor: habit.color }" :aria-label="habit.label"><component :is="habit.icon" aria-hidden="true" /></span></div>
                            <p class="summary-label">Resumo do dia</p><div class="writing-line" /><div class="writing-line" />
                        </template>
                    </div>
                </div>
                <div class="notes-grid">
                    <section><h2>Conquistas</h2><div v-for="line in 5" :key="line" class="note-line" /></section>
                    <section><h2>O que preciso melhorar?</h2><div v-for="line in 5" :key="line" class="note-line" /></section>
                </div>
                <p class="closing">30 dias de comprometimento. A gente cresce junto! ❤️</p>
                <footer class="nutritionist-footer">
                    <svg viewBox="40 530 1000 240" width="1000" height="240" class="nutritionist-logo" role="img" aria-label="Helen Freitas Nutricionista" overflow="hidden">
                        <image href="/images/helen-freitas-nutricionista-logo.png" width="1169" height="1260" />
                    </svg>
                    <div class="nutritionist-contact">
                        <a href="https://helenfreitasnutri.com.br/" target="_blank" rel="noopener noreferrer">helenfreitasnutri.com.br</a>
                        <a href="tel:+5561992098384">+55 (61) 99209-8384</a>
                    </div>
                </footer>
            </main>
        </div>
    </div>
</template>

<style scoped>
.manual-page{min-height:100vh;background:#eee;color:#171410;padding:24px;font-family:Arial,sans-serif}.toolbar{max-width:900px;margin:0 auto 24px;display:flex;flex-wrap:wrap;align-items:center;gap:16px}.toolbar a{color:#28251d;text-decoration:underline}.toolbar button{border:0;border-radius:12px;padding:12px 20px;background:#facc15;color:#171410;font-weight:700}.toolbar p{width:100%;font-size:14px}.sheet-scroll{overflow-x:auto}.sheet{width:760px;margin:auto;background:white;padding:28px;box-sizing:border-box}.sheet-header{display:flex;align-items:center;justify-content:space-between;gap:20px}.sheet-header img{object-fit:contain}.brand{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:2px}h1{font-size:30px;font-weight:900;line-height:1.1;margin:10px 0}h2{font-size:15px;font-weight:800;margin:0 0 10px}.name-line{display:flex;gap:10px;margin:18px 0;font-size:13px}.name-line span,.week-line span{flex:1;border-bottom:1px solid #999}.legend{display:flex;justify-content:space-between;gap:8px;list-style:none;padding:0;font-size:11px}.legend li{display:flex;align-items:center;gap:5px}.legend svg{width:16px;height:16px}.instructions{font-size:11px;margin:10px 0}.weekdays,.calendar{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:5px}.weekdays{margin-bottom:5px}.weekdays div{text-align:center;padding:8px;background:#facc15;border:1px solid #b39b31;font-weight:800;font-size:12px}.day{border:1px solid #999;border-radius:8px;padding:7px;min-height:113px;break-inside:avoid}.empty{background:#eee;border-color:#ddd}.first{background:#e1f4e6;border:2px solid #378854}.middle{background:#e2f1fc;border:2px solid #397aa3}.last{background:#fff6ce;border:2px solid #ac8500}.date{display:flex;flex-wrap:wrap;justify-content:space-between;gap:2px}.date strong{font-size:14px}.date span{font-size:9px}.boxes{display:flex;justify-content:space-between;gap:3px;margin:11px 0}.boxes span{border:1.5px solid;width:13px;height:15px;border-radius:3px;display:flex;align-items:center;justify-content:center}.boxes svg{width:8px;height:8px;color:#aaa;opacity:.45}.summary-label{font-size:8px;margin:4px 0}.writing-line{height:13px;border-bottom:1px solid #bbb}.notes-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px}.notes-grid section{border:1px solid #999;border-radius:10px;padding:12px;break-inside:avoid}.week-line{display:flex;gap:7px;font-size:11px;margin:10px 0}.note-line{height:23px;border-bottom:1px solid #aaa}.closing{text-align:center;font-size:11px;margin-top:15px}
@page{size:A4 portrait;margin:10mm}
.nutritionist-footer{display:flex;align-items:center;justify-content:space-between;gap:20px;border-top:1px solid #ddd;padding-top:10px;margin-top:12px;break-inside:avoid}.nutritionist-logo{display:block;width:240px;height:auto;flex-shrink:0}.nutritionist-contact{display:flex;flex-direction:column;gap:5px;text-align:right;font-size:11px;color:#650565}.nutritionist-contact a{color:inherit;text-decoration:none}
@media print{.manual-page{background:white;padding:0}.toolbar{display:none}.sheet-scroll{overflow:visible}.sheet{width:100%;padding:0}.day{min-height:96px}.boxes{margin:6px 0}.writing-line{height:10px}.note-line{height:18px}.sheet,.day,.weekdays div{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
