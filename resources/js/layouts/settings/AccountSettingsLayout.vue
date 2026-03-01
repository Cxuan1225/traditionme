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
    { title: 'Profile', href: editProfile() },
    { title: 'Password', href: editPassword() },
    { title: 'Two-factor auth', href: show() },
    { title: 'Appearance', href: editAppearance() },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="rounded-3xl border border-zinc-300 bg-zinc-100/85 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-900/70">
        <Heading title="Account settings" description="Manage your personal profile, password, and sign-in preferences" />

        <div class="mt-6 flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-56">
                <nav class="flex flex-col space-y-1 space-x-0" aria-label="Account settings">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start',
                            { 'bg-muted': isCurrentOrParentUrl(item.href) },
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
