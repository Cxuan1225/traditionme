<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { dashboard, home } from '@/routes';
import viewMode from '@/routes/admin/view-mode';
import cart from '@/routes/cart';
import { edit as editProfile } from '@/routes/profile';
import shop from '@/routes/shop';
import type { Auth } from '@/types';

const page = usePage<{ auth?: Auth }>();
const auth = computed(() => page.props.auth);
const isAdmin = computed(() => auth.value?.isAdmin === true);
const isAdminMode = computed(() => auth.value?.adminViewMode === 'admin');

const switchMode = (mode: 'admin' | 'storefront'): void => {
    router.post(
        viewMode.update().url,
        { mode },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <div class="min-h-screen bg-zinc-50 text-zinc-900">
        <div class="bg-zinc-900 px-4 py-2 text-center text-xs font-semibold tracking-wide text-zinc-100 sm:text-sm">
            Free shipping in Malaysia for orders above RM 200
        </div>

        <header class="sticky top-0 z-20 border-b border-zinc-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-10">
                <div class="flex items-center gap-3">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-zinc-900 text-sm font-extrabold tracking-wide text-zinc-100">
                        TM
                    </span>
                    <div>
                        <p class="text-xs font-bold tracking-[0.2em] text-amber-700 uppercase">Malaysian Multi-Cultural Fashion</p>
                        <p class="brand-title text-2xl font-extrabold text-zinc-900">Tradition Me</p>
                    </div>
                </div>

                <div class="hidden flex-1 px-6 md:block">
                    <div class="rounded-full border border-zinc-300 bg-zinc-100 px-4 py-2 text-sm text-zinc-500">
                        Search kurung, kebaya, cheongsam, saree...
                    </div>
                </div>

                <nav class="flex items-center gap-2 text-sm font-semibold">
                    <Link
                        :href="cart.show()"
                        class="hidden rounded-full border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:border-zinc-500 sm:inline-flex"
                    >
                        Cart
                    </Link>
                    <Link
                        v-if="auth?.user"
                        :href="editProfile()"
                        class="rounded-full border border-zinc-300 px-4 py-2 text-zinc-900 transition hover:border-zinc-500"
                    >
                        Settings
                    </Link>
                    <button
                        v-if="isAdmin"
                        type="button"
                        class="rounded-full border border-zinc-300 px-4 py-2 text-zinc-900 transition hover:border-zinc-500"
                        @click="switchMode(isAdminMode ? 'storefront' : 'admin')"
                    >
                        {{ isAdminMode ? 'Switch to Storefront' : 'Switch to Admin' }}
                    </button>
                    <Link
                        v-if="auth?.user"
                        :href="dashboard()"
                        class="rounded-full bg-emerald-600 px-4 py-2 text-white transition hover:bg-emerald-500"
                    >
                        Dashboard
                    </Link>
                    <Link
                        :href="shop.index()"
                        class="rounded-full border border-zinc-300 px-4 py-2 text-zinc-900 transition hover:border-zinc-500"
                    >
                        Shop
                    </Link>
                </nav>
            </div>

            <div class="border-t border-zinc-200 bg-white">
                <div class="mx-auto flex w-full max-w-7xl gap-2 overflow-x-auto px-4 py-3 sm:px-6 lg:px-10">
                    <span class="whitespace-nowrap rounded-full border border-zinc-300 bg-zinc-100 px-4 py-1 text-xs font-semibold tracking-wide text-zinc-700 uppercase">
                        Baju Kurung
                    </span>
                    <span class="whitespace-nowrap rounded-full border border-zinc-300 bg-zinc-100 px-4 py-1 text-xs font-semibold tracking-wide text-zinc-700 uppercase">
                        Kebaya
                    </span>
                    <span class="whitespace-nowrap rounded-full border border-zinc-300 bg-zinc-100 px-4 py-1 text-xs font-semibold tracking-wide text-zinc-700 uppercase">
                        Cheongsam
                    </span>
                    <span class="whitespace-nowrap rounded-full border border-zinc-300 bg-zinc-100 px-4 py-1 text-xs font-semibold tracking-wide text-zinc-700 uppercase">
                        Saree
                    </span>
                    <span class="whitespace-nowrap rounded-full border border-zinc-300 bg-zinc-100 px-4 py-1 text-xs font-semibold tracking-wide text-zinc-700 uppercase">
                        New Arrivals
                    </span>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-10 lg:py-8">
            <slot />
        </main>
    </div>
</template>

<style scoped>
.brand-title {
    font-family: 'Playfair Display', serif;
}
</style>
