<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
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

const sidebarNavItems: NavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
    },
    {
        title: 'Password',
        href: editPassword(),
    },
    {
        title: 'Two-factor auth',
        href: show(),
    },
    {
        title: 'Appearance',
        href: editAppearance(),
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="space-y-6 px-1 pb-2">
        <section class="tm-shell p-5 lg:p-7">
            <p class="tm-kicker text-primary">Settings workspace</p>
            <Heading
                title="Settings"
                description="Manage your profile and account settings."
            />
        </section>

        <section class="tm-shell p-4 lg:p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
                <aside class="w-full lg:w-64">
                    <p
                        class="mb-3 text-[11px] font-bold tracking-[0.18em] text-muted-foreground uppercase"
                    >
                        Sections
                    </p>
                    <nav class="flex flex-col gap-2" aria-label="Settings">
                        <Button
                            v-for="item in sidebarNavItems"
                            :key="toUrl(item.href)"
                            variant="ghost"
                            :class="[
                                'h-auto w-full justify-start rounded-2xl border border-transparent px-3 py-3 text-left text-foreground/85 hover:border-primary/35 hover:bg-orange-50 hover:text-foreground dark:hover:bg-zinc-800',
                                {
                                    'border-primary/45 bg-primary/10 text-primary dark:border-primary/70 dark:bg-amber-950/35 dark:text-amber-200':
                                        isCurrentOrParentUrl(item.href),
                                },
                            ]"
                            as-child
                        >
                            <Link :href="item.href">
                                {{ item.title }}
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
