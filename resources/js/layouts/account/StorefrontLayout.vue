<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown, LogOut, Settings, ShoppingBag } from 'lucide-vue-next';
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { getInitials } from '@/composables/useInitials';
import { home, login, logout, register } from '@/routes';
import cart from '@/routes/cart';
import { edit as editProfile } from '@/routes/profile';
import shop from '@/routes/shop';
import type { Auth } from '@/types';

const page = usePage<{ auth?: Auth }>();
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value?.user);
const isAuthenticated = computed(() => Boolean(user.value));
</script>

<template>
    <div class="min-h-screen bg-background text-foreground">
        <div
            class="bg-zinc-900 px-4 py-2 text-center text-xs font-semibold tracking-wide text-zinc-100 sm:text-sm"
        >
            Free shipping in Malaysia for orders above RM 200
        </div>

        <header
            class="sticky top-0 z-20 border-b border-border bg-background/95 backdrop-blur"
        >
            <div
                class="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-4 px-4 py-4 sm:px-6 lg:grid-cols-[auto_1fr_auto] lg:px-10"
            >
                <Link :href="home()" class="flex items-center gap-3">
                    <span
                        class="inline-flex size-10 items-center justify-center rounded-xl bg-primary text-sm font-extrabold tracking-wide text-primary-foreground"
                    >
                        TM
                    </span>
                    <div>
                        <p class="tm-kicker text-primary">
                            Malaysian Multi-Cultural Fashion
                        </p>
                        <p
                            class="tm-display text-2xl font-extrabold text-foreground"
                        >
                            Tradition Me
                        </p>
                    </div>
                </Link>

                <nav
                    class="flex items-center gap-2 overflow-x-auto text-sm font-semibold"
                >
                    <Link
                        :href="home()"
                        class="rounded-full px-4 py-2 text-muted-foreground transition hover:bg-secondary hover:text-foreground"
                    >
                        Home
                    </Link>
                    <Link
                        :href="shop.index()"
                        class="rounded-full px-4 py-2 text-muted-foreground transition hover:bg-secondary hover:text-foreground"
                    >
                        Shop
                    </Link>
                </nav>

                <nav class="flex items-center gap-2 text-sm font-semibold">
                    <Link
                        :href="cart.show()"
                        class="rounded-full border border-border bg-card/80 px-4 py-2 text-sm font-semibold text-foreground transition hover:border-primary/50"
                    >
                        <span class="inline-flex items-center gap-2">
                            <ShoppingBag class="size-4" />
                            Cart
                        </span>
                    </Link>

                    <template v-if="isAuthenticated">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-2 py-1.5 text-foreground transition hover:border-primary/50"
                                >
                                    <Avatar
                                        class="size-8 rounded-full border border-border"
                                    >
                                        <AvatarImage
                                            v-if="user?.avatar"
                                            :src="user.avatar"
                                            :alt="user.name"
                                        />
                                        <AvatarFallback
                                            class="bg-primary text-xs font-bold text-primary-foreground"
                                        >
                                            {{ getInitials(user?.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <ChevronDown class="size-4" />
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-52">
                                <div class="px-2 py-1">
                                    <p
                                        class="truncate text-sm font-semibold text-zinc-900"
                                    >
                                        {{ user?.name }}
                                    </p>
                                    <p class="truncate text-xs text-zinc-500">
                                        {{ user?.email }}
                                    </p>
                                </div>
                                <DropdownMenuItem as-child>
                                    <Link
                                        :href="editProfile()"
                                        class="flex w-full items-center"
                                    >
                                        <Settings class="mr-2 size-4" />
                                        Settings
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <Link
                                        :href="logout()"
                                        method="post"
                                        as="button"
                                        class="flex w-full items-center text-red-700"
                                    >
                                        <LogOut class="mr-2 size-4" />
                                        Log out
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>

                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-full border border-border px-4 py-2 text-foreground transition hover:border-primary/50"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="rounded-full bg-primary px-4 py-2 font-bold text-primary-foreground transition hover:bg-primary/90"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>

            <div class="border-t border-border bg-card/80">
                <div
                    class="mx-auto flex w-full max-w-7xl gap-2 overflow-x-auto px-4 py-3 sm:px-6 lg:px-10"
                >
                    <span
                        class="rounded-full border border-border bg-background px-4 py-1 text-xs font-semibold tracking-wide whitespace-nowrap text-muted-foreground uppercase"
                    >
                        Baju Kurung
                    </span>
                    <span
                        class="rounded-full border border-border bg-background px-4 py-1 text-xs font-semibold tracking-wide whitespace-nowrap text-muted-foreground uppercase"
                    >
                        Kebaya
                    </span>
                    <span
                        class="rounded-full border border-border bg-background px-4 py-1 text-xs font-semibold tracking-wide whitespace-nowrap text-muted-foreground uppercase"
                    >
                        Cheongsam
                    </span>
                    <span
                        class="rounded-full border border-border bg-background px-4 py-1 text-xs font-semibold tracking-wide whitespace-nowrap text-muted-foreground uppercase"
                    >
                        Saree
                    </span>
                    <span
                        class="rounded-full border border-border bg-background px-4 py-1 text-xs font-semibold tracking-wide whitespace-nowrap text-muted-foreground uppercase"
                    >
                        New Arrivals
                    </span>
                </div>
            </div>
        </header>

        <main
            class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-10 lg:py-8"
        >
            <slot />
        </main>
    </div>
</template>
