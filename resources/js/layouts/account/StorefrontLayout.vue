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
        <header class="sticky top-0 z-20 border-b border-zinc-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-10">
                <Link :href="home()" class="flex items-center gap-3">
                    <span class="inline-flex size-9 items-center justify-center rounded-xl bg-zinc-900 text-xs font-extrabold tracking-wide text-zinc-100">
                        TM
                    </span>
                    <p class="brand-title text-xl font-extrabold text-zinc-900">Tradition Me</p>
                </Link>

                <nav class="flex items-center gap-2 text-sm font-semibold">
                    <Link :href="home()" class="rounded-full px-3 py-2 text-zinc-700 transition hover:bg-zinc-100">
                        Home
                    </Link>
                    <Link :href="shop.index()" class="rounded-full px-3 py-2 text-zinc-700 transition hover:bg-zinc-100">
                        Shop
                    </Link>
                    <Link :href="cart.show()" class="rounded-full px-3 py-2 text-zinc-700 transition hover:bg-zinc-100">
                        Cart
                    </Link>
                    <Link
                        v-if="auth?.user"
                        :href="editProfile()"
                        class="rounded-full border border-zinc-300 px-3 py-2 text-zinc-900 transition hover:border-zinc-500"
                    >
                        Settings
                    </Link>
                    <button
                        v-if="isAdmin"
                        type="button"
                        class="rounded-full border border-zinc-300 px-3 py-2 text-zinc-900 transition hover:border-zinc-500"
                        @click="switchMode(isAdminMode ? 'storefront' : 'admin')"
                    >
                        {{ isAdminMode ? 'Switch to Storefront' : 'Switch to Admin' }}
                    </button>
                    <Link
                        v-if="auth?.user"
                        :href="dashboard()"
                        class="rounded-full bg-zinc-900 px-3 py-2 text-white transition hover:bg-zinc-700"
                    >
                        Dashboard
                    </Link>
                </nav>
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
