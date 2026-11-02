import { GOAL_DETAILS } from '@/data/goalDetails';
import { computed, onMounted, onUnmounted, ref } from 'vue';

export const INSTAGRAM_URL = 'https://www.instagram.com/desafio60.firme/';

export const GOALS = GOAL_DETAILS.map(({ key, color, metric, title, desc, icon }) => ({
    key,
    color,
    metric,
    title,
    desc,
    icon,
}));

const MILES = [
    { day: 1, date: '9 nov', title: 'Início oficial.', line: 'Foto antes. Declare nível, habilidade e os 3 eventos com álcool.' },
    { day: 15, date: '23 nov', title: 'Tá ficando real!', line: 'Foto de 15 dias e ajuste das metas.' },
    { day: 30, date: '8 dez', title: 'Resultado final.', line: 'Foto depois, teste final e a habilidade no grupo.' },
];

export const TOTAL_DAYS = 30;

const START = new Date(2026, 10, 9, 0, 0, 0).getTime();
const END = new Date(2026, 11, 8, 23, 59, 59).getTime();

function pad(value: number): string {
    return String(value).padStart(2, '0');
}

function currentDayAt(now: number): number {
    if (now < START || now > END) {
        return 0;
    }

    return Math.min(TOTAL_DAYS, Math.floor((now - START) / 86400000) + 1);
}

function formatBrl(value: number): string {
    return (
        'R$ ' +
        value
            .toFixed(2)
            .replace('.', ',')
            .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
    );
}

export function useChallenge() {
    const now = ref(Date.now());
    const fails = ref(0);
    let timer: ReturnType<typeof setInterval> | null = null;

    const today = computed(() => currentDayAt(now.value));

    const countdown = computed(() => {
        if (now.value < START) {
            let seconds = Math.floor((START - now.value) / 1000);
            const days = Math.floor(seconds / 86400);
            seconds -= days * 86400;
            const hours = Math.floor(seconds / 3600);
            seconds -= hours * 3600;
            const minutes = Math.floor(seconds / 60);
            seconds -= minutes * 60;

            return {
                label: 'Começa em',
                isCounting: true,
                isRunning: false,
                isDone: false,
                day: 0,
                pct: '0%',
                units: [
                    { v: pad(days), l: 'dias' },
                    { v: pad(hours), l: 'horas' },
                    { v: pad(minutes), l: 'min' },
                    { v: pad(seconds), l: 'seg' },
                ],
            };
        }

        if (now.value <= END) {
            const day = today.value;

            return {
                label: 'Em andamento',
                isCounting: false,
                isRunning: true,
                isDone: false,
                day,
                pct: `${(day / TOTAL_DAYS) * 100}%`,
                units: [],
            };
        }

        return {
            label: 'Desafio concluído',
            isCounting: false,
            isRunning: false,
            isDone: true,
            day: TOTAL_DAYS,
            pct: '100%',
            units: [],
        };
    });

    const failTotal = computed(() => formatBrl(fails.value * 10));

    const failNote = computed(() => {
        if (fails.value === 0) {
            return 'Nenhum dia perdido. Siga firme!';
        }

        if (fails.value === TOTAL_DAYS) {
            return 'Todos os dias viraram doação — e ainda assim, fez diferença.';
        }

        const label = fails.value === 1 ? 'dia' : 'dias';

        return `${fails.value} ${label} × R$ 10,00. No final, doa e faz diferença real.`;
    });

    const failDots = computed(() =>
        Array.from({ length: TOTAL_DAYS }, (_, index) => ({
            c: index < fails.value ? '#F6A821' : '#2E2A25',
        })),
    );

    const milestones = computed(() => {
        const current = today.value;
        const nextIdx = now.value < START ? 0 : MILES.findIndex((mile) => mile.day >= current);

        return MILES.map((mile, index) => {
            const passed = now.value > END || (current > 0 && mile.day < current);
            const isNext = index === nextIdx && now.value <= END;

            return {
                ...mile,
                tag: isNext ? (current === mile.day ? 'Hoje' : 'Próximo') : passed ? 'Feito' : '',
                tagColor: isNext ? '#F6A821' : '#5DB33A',
                bg: isNext ? '#221E1A' : '#141210',
                border: isNext ? '#F6A821' : '#2E2A25',
                numColor: isNext || mile.day === TOTAL_DAYS ? '#F6A821' : '#F5F1EA',
            };
        });
    });

    const incFail = () => {
        fails.value = Math.min(TOTAL_DAYS, fails.value + 1);
    };

    const decFail = () => {
        fails.value = Math.max(0, fails.value - 1);
    };

    onMounted(() => {
        timer = setInterval(() => {
            now.value = Date.now();
        }, 1000);
    });

    onUnmounted(() => {
        if (timer) {
            clearInterval(timer);
        }
    });

    return {
        goals: GOALS,
        countdown,
        fails,
        failTotal,
        failNote,
        failDots,
        incFail,
        decFail,
        milestones,
        instagramUrl: INSTAGRAM_URL,
    };
}
