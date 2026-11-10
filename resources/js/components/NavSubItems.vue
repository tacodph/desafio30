<script setup lang="ts">
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import type { NavGroupItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, LockKeyhole } from 'lucide-vue-next';

defineProps<{ items: NavGroupItem[] }>();
const page = usePage();
const active = (item: NavGroupItem): boolean => Boolean(item.href && (page.url === item.href || page.url.startsWith(`${item.href}/`) || page.url.startsWith(`${item.href}?`))) || Boolean(item.children?.some(active));
</script>

<template>
    <SidebarMenuSub>
        <template v-for="item in items" :key="item.href ?? item.title">
            <Collapsible v-if="item.children?.length" as-child :default-open="active(item)" class="group/submenu">
                <SidebarMenuSubItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuSubButton as="button" type="button" :is-active="active(item)" :disabled="item.disabled">
                            <span>{{ item.title }}</span>
                            <ChevronRight class="ml-auto size-4 shrink-0 transition-transform group-data-[state=open]/submenu:rotate-90" aria-hidden="true" />
                        </SidebarMenuSubButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent><NavSubItems :items="item.children" /></CollapsibleContent>
                </SidebarMenuSubItem>
            </Collapsible>
            <Collapsible v-else-if="item.disabled && item.lockedPreview" as-child>
                <SidebarMenuSubItem>
                    <CollapsibleTrigger as-child>
                        <SidebarMenuSubButton as="button" type="button" title="Ver prévia do conteúdo bloqueado">
                            <span>{{ item.title }}</span><LockKeyhole class="ml-auto size-3.5 shrink-0" aria-hidden="true" />
                        </SidebarMenuSubButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <div class="my-2 space-y-2 rounded-xl bg-brand-card2 p-3 text-sm text-brand-cream">
                            <p class="font-black uppercase text-brand-gold">{{ item.lockedPreview.title }}</p>
                            <p class="font-bold">{{ item.lockedPreview.subtitle }}</p>
                            <p class="leading-relaxed">{{ item.lockedPreview.description }}</p>
                            <p class="text-xs font-bold uppercase text-brand-gold">{{ item.lockedPreview.footer }}</p>
                        </div>
                    </CollapsibleContent>
                </SidebarMenuSubItem>
            </Collapsible>
            <SidebarMenuSubItem v-else>
                <SidebarMenuSubButton v-if="item.disabled" as="button" type="button" disabled aria-disabled="true" title="Acesso ainda não liberado" class="opacity-50">
                    <span>{{ item.title }}</span><LockKeyhole class="ml-auto size-3.5" aria-hidden="true" />
                </SidebarMenuSubButton>
                <SidebarMenuSubButton v-else as-child :is-active="active(item)">
                    <Link :href="item.href ?? '#'" :title="item.title"><span>{{ item.title }}</span></Link>
                </SidebarMenuSubButton>
            </SidebarMenuSubItem>
        </template>
    </SidebarMenuSub>
</template>
