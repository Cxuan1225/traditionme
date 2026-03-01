<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Gauge,
    LockKeyhole,
    Palette,
    ShieldCheck,
    ShoppingBag,
    UserRoundCog,
    UsersRound,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import viewMode from '@/routes/admin/view-mode';
import { dashboard, home } from '@/routes';
import { edit as editAppearance } from '@/routes/appearance';
import { index as productsIndex } from '@/routes/products';
import { edit as editProfile } from '@/routes/profile';
import { index as rolesIndex } from '@/routes/security/roles';
import { show as showTwoFactor } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

type NavEntry = {
    title: string;
    href: ReturnType<typeof dashboard>;
    icon: unknown;
};

const { isCurrentOrParentUrl } = useCurrentUrl();
const primaryNav: NavEntry[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: Gauge,
    },
    {
        title: 'Products',
        href: productsIndex(),
        icon: ShoppingBag,
    },
    {
        title: 'Roles & Access',
        href: rolesIndex(),
        icon: UsersRound,
    },
];

const settingsNav: NavEntry[] = [
    {
        title: 'Profile',
        href: editProfile(),
        icon: UserRoundCog,
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: LockKeyhole,
    },
    {
        title: 'Two-factor Auth',
        href: showTwoFactor(),
        icon: ShieldCheck,
    },
    {
        title: 'Appearance',
        href: editAppearance(),
        icon: Palette,
    },
];

const switchToStorefront = (): void => {
    router.post(
        viewMode.update().url,
        { mode: 'storefront' },
        { preserveScroll: true },
    );
};
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-[308px_1fr]">
            <aside class="relative border-r border-border bg-gradient-to-b from-amber-100/60 via-background to-background px-5 py-6 dark:from-amber-950/20">
                <div class="pointer-events-none absolute -top-16 -left-16 h-48 w-48 rounded-full bg-amber-300/35 blur-3xl dark:bg-amber-500/15" />
                <div class="pointer-events-none absolute -right-16 bottom-10 h-40 w-40 rounded-full bg-rose-300/25 blur-3xl dark:bg-rose-500/10" />

                <Link :href="dashboard()" class="relative mb-6 flex items-center gap-3 rounded-2xl border border-border bg-card/90 px-4 py-3 shadow-sm backdrop-blur">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-amber-500 text-xs font-black tracking-wide text-amber-950">
                        TM
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold tracking-[0.2em] text-amber-700 uppercase dark:text-amber-300">Operations</p>
                        <p class="text-base font-black text-foreground">Tradition Me</p>
                    </div>
                </Link>

                <div class="relative space-y-7">
                    <div>
                        <p class="mb-2 px-2 text-[11px] font-bold tracking-[0.22em] text-muted-foreground uppercase">Management</p>
                        <nav class="space-y-1.5">
                            <Link
                                v-for="item in primaryNav"
                                :key="item.title"
                                :href="item.href"
                                class="group flex items-center gap-2 rounded-xl border border-transparent px-3 py-2.5 text-sm font-semibold text-foreground/80 transition hover:border-amber-200 hover:bg-amber-100/70 hover:text-foreground dark:hover:border-amber-900/70 dark:hover:bg-amber-950/30"
                                :class="{
                                    'border-amber-300 bg-amber-100 text-amber-900 shadow-sm dark:border-amber-900/70 dark:bg-amber-950/35 dark:text-amber-200': isCurrentOrParentUrl(item.href),
                                }"
                            >
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-background/80 ring-1 ring-border group-hover:ring-amber-300 dark:bg-zinc-900/40 dark:group-hover:ring-amber-800">
                                    <component :is="item.icon" class="size-4" />
                                </span>
                                {{ item.title }}
                            </Link>
                        </nav>
                    </div>

                    <div>
                        <p class="mb-2 px-2 text-[11px] font-bold tracking-[0.22em] text-muted-foreground uppercase">Account</p>
                        <nav class="space-y-1.5">
                            <Link
                                v-for="item in settingsNav"
                                :key="item.title"
                                :href="item.href"
                                class="group flex items-center gap-2 rounded-xl border border-transparent px-3 py-2.5 text-sm font-semibold text-foreground/80 transition hover:border-amber-200 hover:bg-amber-100/70 hover:text-foreground dark:hover:border-amber-900/70 dark:hover:bg-amber-950/30"
                                :class="{
                                    'border-amber-300 bg-amber-100 text-amber-900 shadow-sm dark:border-amber-900/70 dark:bg-amber-950/35 dark:text-amber-200': isCurrentOrParentUrl(item.href),
                                }"
                            >
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-background/80 ring-1 ring-border group-hover:ring-amber-300 dark:bg-zinc-900/40 dark:group-hover:ring-amber-800">
                                    <component :is="item.icon" class="size-4" />
                                </span>
                                {{ item.title }}
                            </Link>
                        </nav>
                    </div>
                </div>

                <div class="relative mt-8 space-y-2 border-t border-border pt-4">
                    <Button class="w-full justify-start bg-amber-500 text-amber-950 hover:bg-amber-400" @click="switchToStorefront">
                        Switch to Storefront
                    </Button>
                    <Link :href="home()" class="inline-flex w-full items-center justify-center rounded-lg border border-border bg-card px-3 py-2 text-xs font-semibold text-foreground/80 transition hover:bg-accent hover:text-foreground">
                        Back to Home
                    </Link>
                </div>
            </aside>

            <div class="relative bg-background">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(245,158,11,0.08),transparent_45%)] dark:bg-[radial-gradient(circle_at_top_right,rgba(245,158,11,0.12),transparent_40%)]" />
                <header class="relative border-b border-border bg-background/90 px-5 py-4 backdrop-blur lg:px-8">
                    <p class="text-xs font-bold tracking-[0.2em] text-muted-foreground uppercase">Administration</p>
                    <h1 class="text-xl font-black text-foreground">Control Center</h1>
                </header>
                <main class="relative px-4 py-5 lg:px-8">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
