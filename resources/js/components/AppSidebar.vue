<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/ui/sidebar';
import { type NavGroupItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Apple, BookOpen, CircleHelp, ClipboardCheck, Droplets, Dumbbell, Flag, LayoutGrid, Lightbulb, PartyPopper, Salad, Settings, Star, Trophy, Users, Mail } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<SharedData>();
const { isMobile, setOpenMobile } = useSidebar();

function closeMobileOnLink(event: MouseEvent) {
    if (!isMobile.value || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

    const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (link && link.getAttribute('href') !== '#' && link.getAttribute('target') !== '_blank') {
        setOpenMobile(false);
    }
}

const role = computed(() => page.props.auth.user?.user_type?.name ?? 'atleta');

const menuLabel = computed(() => {
    if (role.value === 'administrador') {
        return 'Administração';
    }

    if (role.value === 'atleta') {
        return 'Meus objetivos';
    }

    return 'Equipe';
});

const objective = (goal: string) => ({ title: 'Lembre as regras', href: `/meus-objetivos/${goal}` });

const evolutionChildren = computed(() => {
    const allowed = page.props.evolutionAccess?.pages ?? {};
    return [
        { title: 'Check in do dia', href: '/atleta/evolucao/dias', feature: 'dias' },
        { title: 'Conquistas', href: '/atleta/evolucao/conquistas', feature: 'conquistas' },
        { title: 'Resumo', href: '/atleta/evolucao/resumo', feature: 'resumo' },
        { title: 'Pontuação', href: '/pontuacao', feature: 'dias' },
    ].map((item) => ({ ...item, disabled: !allowed[item.feature] }));
});

const mainNavItems = computed<NavGroupItem[]>(() => {
    if (role.value === 'administrador') {
        return [
            { title: 'Painel', href: '/dashboard', icon: LayoutGrid },
            { title: 'Administração', icon: Settings, children: [
                { title: 'Treinos', href: '/admin/treinos' },
                { title: 'Dúvidas Frequentes', href: '/admin/duvidas-frequentes' },
            ] },
            { title: 'Usuários', href: '/admin/usuarios', icon: Users },
            { title: 'Check-ins dos atletas', href: '/equipe/check-ins', icon: ClipboardCheck },
            { title: 'Conquistas dos atletas', href: '/equipe/conquistas', icon: Trophy },
            { title: 'Alimentação dos atletas', href: '/equipe/alimentacao', icon: Salad },
            { title: 'Pagamentos', href: '/admin/pagamentos', icon: ClipboardCheck },
            { title: 'Lembretes de pagamento', href: '/admin/pagamentos/lembretes', icon: Mail },
            { title: 'Alimentos', href: '/admin/alimentos', icon: Apple },
            { title: 'Objetivos', href: '/meus-objetivos/alimentacao', icon: Salad },
            { title: 'Evolução (visualizar)', icon: ClipboardCheck, children: evolutionChildren.value },
            { title: 'Monte seu Cardápio (visualizar)', href: '/objetivos/alimentacao/compras', icon: Salad },
            { title: 'Almoço - Monte seu prato (visualizar)', href: '/alimentacao/monte-seu-prato', icon: Salad },
            { title: 'Alimentação', icon: Salad, children: [
                { title: 'Recado importante da nutri', href: '/alimentacao/recado-da-nutri' },
                { title: 'Escolhendo melhor fora de casa', href: '/alimentacao/escolhendo-melhor-fora-de-casa' },
                { title: 'Lidando com o fim de semana', href: '/alimentacao/lidando-com-o-fim-de-semana' },
            ] },
            { title: 'Cantinho da Nutri', icon: BookOpen, children: [
                { title: 'A nutri indica!', href: '/cantinho-da-nutri/a-nutri-indica' },
                { title: 'Materiais e orientações', href: '/cantinho-da-nutri' },
                { title: 'Monitoramento 30 dias', href: '/cantinho-da-nutri/monitoramento' },
            ] },
            { title: 'Pontuação', href: '/pontuacao', icon: Trophy },
            { title: 'Mensagens', icon: PartyPopper, children: [
                { title: 'Histórico de emails', href: '/admin/mensagens/emails' },
                { title: 'Confirmação da inscrição', href: '/admin/boas-vindas' },
                { title: 'Boas-vindas com vídeo', href: '/admin/mensagens/boas-vindas-video' },
            ] },
            { title: 'Liberações do desafio', href: '/admin/liberacoes', icon: Settings },
            { title: 'Configurações', href: '/settings/profile', icon: Settings },
        ];
    }

    if (role.value === 'atleta') {
        return [
            { title: 'Como funciona a plataforma', href: '/atleta/boas-vindas/plataforma', icon: CircleHelp, highlighted: true },
            { title: 'Painel', href: '/dashboard', icon: LayoutGrid },
            { title: 'Boas-vindas', icon: PartyPopper, children: [
                { title: 'Mensagem de boas-vindas com vídeo', href: '/atleta/boas-vindas/video' },
                { title: 'Entenda os objetivos', href: '/atleta/boas-vindas/objetivos' },
                { title: 'O desafio não termina em 30 dias.', href: '/atleta/boas-vindas/aproveitar-melhor' },
                { title: 'Tutoriais', children: [
                    { title: 'Adicionar o site à tela inicial do celular', href: '/atleta/boas-vindas/tutoriais/tela-inicial' },
                ] },
            ] },
            {
                title: 'Ponto de partida',
                icon: Flag,
                children: [
                    { title: 'Primeiro dia', href: '/atleta/ponto-de-partida/primeiro-dia' },
                    { title: 'Metas', href: '/atleta/ponto-de-partida/metas' },
                ],
            },
            {
                title: 'Evolução',
                icon: ClipboardCheck,
                children: evolutionChildren.value,
            },
            { title: 'Alimentação', icon: Salad, children: [
                { title: 'Recado importante da nutri', href: '/alimentacao/recado-da-nutri', disabled: !page.props.evolutionAccess?.pages.alimentacao },
                { title: 'Lista de compras inteligente', href: '/objetivos/alimentacao/compras', disabled: !page.props.evolutionAccess?.pages.shopping },
                { title: 'Almoço - Monte seu prato', href: '/alimentacao/monte-seu-prato', disabled: !page.props.evolutionAccess?.pages.alimentacao },
                { title: 'Escolhendo melhor fora de casa', href: '/alimentacao/escolhendo-melhor-fora-de-casa', disabled: !page.props.evolutionAccess?.pages.alimentacao },
                { title: 'Lidando com o fim de semana', href: '/alimentacao/lidando-com-o-fim-de-semana', disabled: !page.props.evolutionAccess?.pages.alimentacao },
            ] },
            { title: 'Cantinho da Nutri', icon: BookOpen, children: [
                { title: 'A nutri indica!', href: '/cantinho-da-nutri/a-nutri-indica', disabled: !page.props.evolutionAccess?.pages.nutri },
                { title: 'Materiais e orientações', href: '/cantinho-da-nutri', disabled: !page.props.evolutionAccess?.pages.nutri },
                { title: 'Monitoramento 30 dias', href: '/cantinho-da-nutri/monitoramento', disabled: !page.props.evolutionAccess?.pages.nutri },
            ] },
            { title: 'Treino', icon: Dumbbell, children: [objective('treino')] },
            { title: 'Hidratação', icon: Droplets, children: [objective('hidratacao')] },
            { title: 'Habilidade', icon: Lightbulb, children: [objective('habilidade')] },
            { title: 'Refeição luxo', icon: Star, children: [objective('plus')] },
        ];
    }

    const items: NavGroupItem[] = [
        { title: 'Painel', href: '/dashboard', icon: LayoutGrid },
        { title: 'Atletas', href: '/equipe/atletas', icon: Users },
        {
            title: 'Objetivos',
            icon: Salad,
            children: [
                { title: 'Alimentação', href: '/meus-objetivos/alimentacao' },
                { title: 'Treino', href: '/meus-objetivos/treino' },
                { title: 'Hidratação', href: '/meus-objetivos/hidratacao' },
                { title: 'Habilidade', href: '/meus-objetivos/habilidade' },
                { title: 'Refeição luxo', href: '/meus-objetivos/plus' },
            ],
        },
        { title: 'Pontuação', href: '/pontuacao', icon: Trophy },
    ];

    if (role.value === 'nutricionista') {
        items.splice(2, 0, { title: 'Pagamentos', href: '/admin/pagamentos', icon: ClipboardCheck });
        items.splice(2, 0, { title: 'Check-ins dos atletas', href: '/equipe/check-ins', icon: ClipboardCheck });
        items.splice(3, 0, { title: 'Conquistas dos atletas', href: '/equipe/conquistas', icon: Trophy });
        items.splice(4, 0, { title: 'Alimentação dos atletas', href: '/equipe/alimentacao', icon: Salad });
    }

    return items;
});

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader @click="closeMobileOnLink">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent @click="closeMobileOnLink">
            <NavMain :items="mainNavItems" :label="menuLabel" />
        </SidebarContent>

        <SidebarFooter @click="closeMobileOnLink">
            <NavMain :items="[{ title: 'Como funciona a alimentação no desafio?', href: '/ajuda/como-funciona-a-alimentacao', icon: CircleHelp }, { title: 'Dúvidas Frequentes', href: '/ajuda/duvidas-frequentes', icon: BookOpen }, { title: 'Suporte', href: '/suporte', icon: Mail }]" label="Ajuda" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
