import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

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
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    avatar_url?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    settings?: {
        theme: 'light' | 'dark' | 'system';
        language: string;
        timezone: string;
        date_format: string;
        time_format: string;
        email_notifications: boolean;
        browser_notifications: boolean;
        task_reminders: boolean;
        project_updates: boolean;
    };
}

export type BreadcrumbItemType = BreadcrumbItem;
