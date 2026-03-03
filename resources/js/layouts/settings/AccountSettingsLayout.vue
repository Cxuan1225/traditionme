<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    LockKeyhole,
    ShieldCheck,
    ShoppingBag,
    UserRound,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { cn } from '@/lib/utils';
import { toUrl } from '@/lib/utils';
import cart from '@/routes/cart';
import { edit as editProfile } from '@/routes/profile';
import shop from '@/routes/shop';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import type { Auth, NavItem } from '@/types';

type AccountNavItem = NavItem & {
    caption: string;
    status: string;
};

const sidebarNavItems: AccountNavItem[] = [
    {
        title: 'Profile',
        href: editProfile(),
        icon: UserRound,
        caption: 'Personal details',
        status: 'Primary',
    },
    {
        title: 'Password',
        href: editPassword(),
        icon: LockKeyhole,
        caption: 'Sign-in protection',
        status: 'Security',
    },
    {
        title: 'Two-factor auth',
        href: show(),
        icon: ShieldCheck,
        caption: 'Verification security',
        status: 'Recommended',
    },
];

const { isCurrentOrParentUrl } = useCurrentUrl();

const page = usePage<{ auth?: Auth }>();
const user = computed(() => page.props.auth?.user);
const isEmailVerified = computed(() => Boolean(user.value?.email_verified_at));
</script>

<template>
    <div class="space-y-8">
        <section
            class="tm-shell relative overflow-hidden bg-zinc-900 p-6 text-zinc-100 lg:p-8"
        >
            <div class="tm-dot-grid absolute inset-0 opacity-35" />
            <div
                class="absolute top-0 right-0 h-40 w-40 rounded-full bg-amber-400/30 blur-2xl"
            />
            <div
                class="absolute -bottom-6 left-0 h-32 w-32 rounded-full bg-red-400/25 blur-2xl"
            />
            <p class="tm-kicker relative text-amber-300">
                Buyer Control Center
            </p>
            <h1
                class="relative mt-2 text-3xl font-black tracking-tight sm:text-4xl"
            >
                Account settings
            </h1>
            <p
                class="relative mt-2 max-w-2xl text-sm text-zinc-300 sm:text-base"
            >
                Control your personal details and security preferences for your
                shopping account.
            </p>
            <div class="relative mt-5 grid gap-2 sm:grid-cols-3">
                <div
                    class="rounded-2xl border border-white/20 bg-white/10 px-3 py-2"
                >
                    <p
                        class="text-[11px] font-bold tracking-[0.14em] text-zinc-300 uppercase"
                    >
                        Account
                    </p>
                    <p
                        class="mt-1 truncate text-sm font-semibold text-zinc-100"
                    >
                        {{ user?.name ?? 'Guest' }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-white/20 bg-white/10 px-3 py-2"
                >
                    <p
                        class="text-[11px] font-bold tracking-[0.14em] text-zinc-300 uppercase"
                    >
                        Email status
                    </p>
                    <p class="mt-1 text-sm font-semibold text-zinc-100">
                        {{
                            isEmailVerified ? 'Verified' : 'Verification needed'
                        }}
                    </p>
                </div>
                <div
                    class="rounded-2xl border border-white/20 bg-white/10 px-3 py-2"
                >
                    <p
                        class="text-[11px] font-bold tracking-[0.14em] text-zinc-300 uppercase"
                    >
                        Security
                    </p>
                    <p class="mt-1 text-sm font-semibold text-zinc-100">
                        Manage password & 2FA
                    </p>
                </div>
            </div>
        </section>

        <section class="tm-shell p-5 lg:p-7">
            <div class="mb-6 grid gap-3 sm:grid-cols-3">
                <Link
                    :href="editProfile()"
                    class="tm-subtle-card p-4 transition hover:border-primary/40 hover:bg-background dark:hover:bg-zinc-900"
                >
                    <p
                        class="text-xs font-bold tracking-[0.14em] text-zinc-500 uppercase dark:text-zinc-400"
                    >
                        Quick action
                    </p>
                    <p
                        class="mt-2 text-sm font-bold text-zinc-900 dark:text-zinc-100"
                    >
                        Edit profile
                    </p>
                </Link>
                <Link
                    :href="shop.index()"
                    class="tm-subtle-card p-4 transition hover:border-primary/40 hover:bg-background dark:hover:bg-zinc-900"
                >
                    <p
                        class="text-xs font-bold tracking-[0.14em] text-zinc-500 uppercase dark:text-zinc-400"
                    >
                        Quick action
                    </p>
                    <p
                        class="mt-2 inline-flex items-center gap-2 text-sm font-bold text-zinc-900 dark:text-zinc-100"
                    >
                        <ShoppingBag class="size-4" />
                        Continue shopping
                    </p>
                </Link>
                <Link
                    :href="cart.show()"
                    class="tm-subtle-card p-4 transition hover:border-primary/40 hover:bg-background dark:hover:bg-zinc-900"
                >
                    <p
                        class="text-xs font-bold tracking-[0.14em] text-zinc-500 uppercase dark:text-zinc-400"
                    >
                        Quick action
                    </p>
                    <p
                        class="mt-2 text-sm font-bold text-zinc-900 dark:text-zinc-100"
                    >
                        Review cart
                    </p>
                </Link>
            </div>

            <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
                <aside class="w-full lg:w-72">
                    <p
                        class="mb-3 text-xs font-bold tracking-[0.14em] text-zinc-500 uppercase dark:text-zinc-400"
                    >
                        Setting Areas
                    </p>
                    <nav class="grid gap-2.5" aria-label="Account settings">
                        <Link
                            v-for="item in sidebarNavItems"
                            :key="toUrl(item.href)"
                            :href="item.href"
                            :class="
                                cn(
                                    'group flex w-full items-start gap-3 rounded-2xl border px-4 py-3 transition',
                                    isCurrentOrParentUrl(item.href)
                                        ? 'border-primary bg-primary text-primary-foreground dark:border-primary dark:bg-primary dark:text-primary-foreground'
                                        : 'border-zinc-200 bg-zinc-50 text-zinc-700 hover:border-primary/35 hover:bg-orange-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-primary/60 dark:hover:bg-zinc-800/90',
                                )
                            "
                        >
                            <component
                                :is="item.icon"
                                class="mt-0.5 size-4 shrink-0"
                            />
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm leading-tight font-bold">
                                        {{ item.title }}
                                    </p>
                                    <span
                                        :class="
                                            cn(
                                                'rounded-full px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase',
                                                isCurrentOrParentUrl(item.href)
                                                    ? 'bg-white/15 text-zinc-100 dark:bg-black/15 dark:text-zinc-100'
                                                    : 'bg-zinc-200 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
                                            )
                                        "
                                    >
                                        {{ item.status }}
                                    </span>
                                </div>
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
