import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
    disabled?: boolean;
    children?: { title: string; href: string; disabled?: boolean }[];
}

export interface NavGroupItem extends Omit<NavItem, 'href'> {
    href?: string;
}

export interface SharedData {
    evolutionAccess?: { releaseDate: string; pages: Record<string, boolean> } | null;
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    user_type?: {
        id: number;
        name: string;
    } | null;
}

export type BreadcrumbItemType = BreadcrumbItem;
