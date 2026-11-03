<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
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
const hasActiveChild = (item: NavGroupItem) => item.children?.some((child) => isCurrent(child.href)) ?? false;
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <Collapsible v-if="item.children?.length" as-child :default-open="hasActiveChild(item)" class="group/collapsible">
                    <SidebarMenuItem>
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton :is-active="hasActiveChild(item)">
                                <component v-if="item.icon" :is="item.icon" />
                                <span>{{ item.title }}</span>
                                <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem v-for="child in item.children" :key="child.href">
                                    <SidebarMenuSubButton v-if="child.disabled" as="button" type="button" disabled aria-disabled="true" title="Acesso ainda não liberado" class="opacity-50">
                                        <span>{{ child.title }}</span>
                                        <LockKeyhole class="ml-auto size-3.5" aria-hidden="true" />
                                    </SidebarMenuSubButton>
                                    <SidebarMenuSubButton v-else as-child :is-active="isCurrent(child.href)">
                                        <Link :href="child.href">
                                            <span>{{ child.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>
                    </SidebarMenuItem>
                </Collapsible>

                <SidebarMenuItem v-else>
                    <SidebarMenuButton as-child :is-active="item.href === page.url">
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
