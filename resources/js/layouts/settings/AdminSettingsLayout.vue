<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    LockKeyhole,
    Palette,
    ShieldCheck,
    UserRoundCog,
} from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import type { NavItem } from '@/types';

type AdminSettingsNavItem = NavItem & {
    caption: string;
    icon: unknown;
};

const sidebarNavItems: AdminSettingsNavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
        caption: 'Identity and email',
        icon: UserRoundCog,
    },
    {
        title: 'Password',
        href: editPassword(),
        caption: 'Credential controls',
        icon: LockKeyhole,
    },
    {
        title: 'Two-factor auth',
        href: show(),
        caption: 'MFA verification',
        icon: ShieldCheck,
    },
    {
        title: 'Appearance',
        href: editAppearance(),
        caption: 'Theme preferences',
        icon: Palette,
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="space-y-6 px-1 pb-2">
        <section class="tm-shell p-5 lg:p-7">
            <p class="tm-kicker text-amber-700 dark:text-amber-300">
                Admin preference studio
            </p>
            <Heading
                title="Administration settings"
                description="Manage your admin profile, security controls, and interface preferences from one panel."
            />
        </section>

        <section class="tm-shell p-4 lg:p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
                <aside class="w-full lg:w-72">
                    <p
                        class="mb-3 text-[11px] font-bold tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        Control categories
                    </p>
                    <nav
                        class="flex flex-col gap-2"
                        aria-label="Admin settings"
                    >
                        <Button
                            v-for="item in sidebarNavItems"
                            :key="toUrl(item.href)"
                            variant="ghost"
                            :class="[
                                'h-auto w-full justify-start rounded-2xl border border-transparent px-3 py-3 text-left text-foreground/85 hover:border-amber-300/70 hover:bg-amber-100/65 hover:text-foreground dark:hover:bg-amber-950/25',
                                {
                                    'border-amber-300 bg-amber-100 text-amber-900 dark:border-amber-900/70 dark:bg-amber-950/35 dark:text-amber-200':
                                        isCurrentOrParentUrl(item.href),
                                },
                            ]"
                            as-child
                        >
                            <Link
                                :href="item.href"
                                class="flex w-full items-start gap-3"
                            >
                                <span
                                    class="mt-0.5 inline-flex size-8 shrink-0 items-center justify-center rounded-lg bg-background ring-1 ring-border"
                                >
                                    <component :is="item.icon" class="size-4" />
                                </span>
                                <span>
                                    <span class="block text-sm font-bold">{{
                                        item.title
                                    }}</span>
                                    <span
                                        class="block text-xs text-muted-foreground"
                                        >{{ item.caption }}</span
                                    >
                                </span>
                            </Link>
                        </Button>
                    </nav>
                </aside>

                <Separator class="lg:hidden" />
                <Separator orientation="vertical" class="hidden lg:block" />

                <div class="min-w-0 flex-1">
                    <section class="space-y-6">
                        <slot />
                    </section>
                </div>
            </div>
        </section>
    </div>
</template>
