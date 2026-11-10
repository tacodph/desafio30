<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import NavSubItems from '@/components/NavSubItems.vue';
import { type NavGroupItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, LockKeyhole } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        items: NavGroupItem[];
        label?: string;
    }>(),
    {
        label: 'Desafio',
    },
);

const page = usePage<SharedData>();

const isCurrent = (href: string) => page.url === href || page.url.startsWith(`${href}/`) || page.url.startsWith(`${href}?`);
const hasActiveChild = (item: NavGroupItem): boolean => item.children?.some((child) => Boolean(child.href && isCurrent(child.href)) || hasActiveChild(child)) ?? false;
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <Collapsible v-if="item.children?.length" as-child :default-open="hasActiveChild(item)" class="group/collapsible">
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :is-active="hasActiveChild(item)" :class="{ 'opacity-50': item.disabled }">
                                <component v-if="item.icon" :is="item.icon" />
                                <span>{{ item.title }}</span>
                                <LockKeyhole v-if="item.disabled" class="size-3.5" aria-hidden="true" />
                                <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <NavSubItems :items="item.children" />
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <SidebarMenuItem v-else>
                    <SidebarMenuButton v-if="item.disabled" as="button" type="button" disabled aria-disabled="true" title="Acesso ainda não liberado" class="opacity-50">
                        <component v-if="item.icon" :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <LockKeyhole class="ml-auto size-3.5" aria-hidden="true" />
                    </SidebarMenuButton>
                    <SidebarMenuButton v-else as-child :is-active="item.href === page.url" :class="{ 'bg-brand-gold font-bold text-brand-ink hover:bg-yellow-300 hover:text-brand-ink active:bg-yellow-300 active:text-brand-ink data-[active=true]:bg-brand-gold data-[active=true]:text-brand-ink [&>svg]:text-brand-ink': item.highlighted }">
                        <Link :href="item.href ?? '#'">
                            <component v-if="item.icon" :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
