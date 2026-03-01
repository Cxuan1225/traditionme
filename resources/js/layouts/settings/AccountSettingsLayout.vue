<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LockKeyhole, ShieldCheck, UserRound } from 'lucide-vue-next';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { cn } from '@/lib/utils';
import { toUrl } from '@/lib/utils';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import type { NavItem } from '@/types';

type AccountNavItem = NavItem & {
    caption: string;
};

const sidebarNavItems: AccountNavItem[] = [
    { title: 'Profile', href: editProfile(), icon: UserRound, caption: 'Personal details' },
    { title: 'Password', href: editPassword(), icon: LockKeyhole, caption: 'Sign-in protection' },
    { title: 'Two-factor auth', href: show(), icon: ShieldCheck, caption: 'Verification security' },
];

const { isCurrentOrParentUrl } = useCurrentUrl();
</script>

<template>
    <div class="space-y-8">
        <section
            class="relative overflow-hidden rounded-[2rem] border border-zinc-200 bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 p-6 text-zinc-100 shadow-sm"
        >
            <div class="absolute top-0 right-0 h-36 w-36 rounded-full bg-amber-400/30 blur-2xl" />
            <div class="absolute bottom-0 left-0 h-28 w-28 rounded-full bg-red-400/20 blur-2xl" />
            <p class="relative text-xs font-bold tracking-[0.22em] text-amber-300 uppercase">Buyer Control Center</p>
            <h1 class="relative mt-2 text-3xl font-black tracking-tight sm:text-4xl">Account settings</h1>
            <p class="relative mt-2 max-w-2xl text-sm text-zinc-300 sm:text-base">
                Control your personal details and security preferences for your shopping account.
            </p>
        </section>

        <section class="rounded-[2rem] border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 lg:p-7">
            <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
                <aside class="w-full lg:w-72">
                    <p class="mb-3 text-xs font-bold tracking-[0.14em] text-zinc-500 uppercase dark:text-zinc-400">Setting Areas</p>
                    <nav class="grid gap-2.5" aria-label="Account settings">
                        <Link
                            v-for="item in sidebarNavItems"
                            :key="toUrl(item.href)"
                            :href="item.href"
                            :class="
                                cn(
                                    'group flex w-full items-start gap-3 rounded-2xl border px-4 py-3 transition',
                                    isCurrentOrParentUrl(item.href)
                                        ? 'border-zinc-900 bg-zinc-900 text-zinc-100 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900'
                                        : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:border-zinc-300 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-zinc-600 dark:hover:bg-zinc-800/90',
                                )
                            "
                        >
                            <component :is="item.icon" class="mt-0.5 size-4 shrink-0" />
                            <div>
                                <p class="text-sm font-bold leading-tight">{{ item.title }}</p>
                                <p
                                    :class="
                                        cn(
                                            'mt-1 text-xs',
                                            isCurrentOrParentUrl(item.href)
                                                ? 'text-zinc-300 dark:text-zinc-600'
                                                : 'text-zinc-500 dark:text-zinc-400',
                                        )
                                    "
                                >
                                    {{ item.caption }}
                                </p>
                            </div>
                        </Link>
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
