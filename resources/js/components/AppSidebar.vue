<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavGroupItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Apple, ClipboardCheck, Droplets, Dumbbell, Flag, LayoutGrid, Lightbulb, Salad, Settings, Star, Trophy, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<SharedData>();
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

const objective = (goal: string) => ({ title: 'Objetivo', href: `/meus-objetivos/${goal}` });

const mainNavItems = computed<NavGroupItem[]>(() => {
    if (role.value === 'administrador') {
        return [
            { title: 'Painel', href: '/dashboard', icon: LayoutGrid },
            { title: 'Usuários', href: '/admin/usuarios', icon: Users },
            { title: 'Pagamentos', href: '/admin/pagamentos', icon: ClipboardCheck },
            { title: 'Alimentos', href: '/admin/alimentos', icon: Apple },
            { title: 'Objetivos', href: '/meus-objetivos/alimentacao', icon: Salad },
            { title: 'Pontuação', href: '/pontuacao', icon: Trophy },
            { title: 'Liberações do desafio', href: '/admin/liberacoes', icon: Settings },
            { title: 'Configurações', href: '/settings/profile', icon: Settings },
        ];
    }

    if (role.value === 'atleta') {
        return [
            { title: 'Painel', href: '/dashboard', icon: LayoutGrid },
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
                children: [
                    { title: 'Dias', href: '/atleta/evolucao/dias' },
                    { title: 'Conquistas', href: '/atleta/evolucao/conquistas' },
                    { title: 'Resumo', href: '/atleta/evolucao/resumo' },
                    { title: 'Pontuação', href: '/pontuacao' },
                ],
            },
            { title: 'Alimentação', icon: Salad, children: [objective('alimentacao'), { title: 'Monte seu Cardápio', href: '/objetivos/alimentacao/compras' }] },
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
    }

    return items;
});

</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
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

        <SidebarContent>
            <NavMain :items="mainNavItems" :label="menuLabel" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
