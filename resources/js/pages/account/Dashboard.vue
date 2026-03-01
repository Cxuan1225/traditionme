<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import cart from '@/routes/cart';
import { edit as editProfile } from '@/routes/profile';
import shop from '@/routes/shop';
import type { Auth } from '@/types';

const page = usePage<{ auth?: Auth }>();
const userName = computed(() => page.props.auth?.user?.name ?? 'there');
</script>

<template>
    <Head title="Dashboard" />

    <StorefrontLayout>
        <section class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <article class="overflow-hidden rounded-3xl border border-zinc-200 bg-gradient-to-br from-orange-50 via-amber-50 to-rose-100 p-6 shadow-sm lg:p-10">
                <p class="text-xs font-bold tracking-[0.25em] text-red-700 uppercase">Welcome back</p>
                <h1 class="mt-3 text-4xl leading-tight font-black text-zinc-900 sm:text-5xl">
                    Hi {{ userName }}, continue your
                    <span class="brand-title block text-red-700">Tradition Me</span>
                    journey.
                </h1>
                <p class="mt-4 max-w-xl text-base text-zinc-700">
                    Shop festive collections, update your profile, and manage your account preferences from one place.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <Link :href="shop.index()" class="rounded-full bg-zinc-900 px-6 py-3 text-sm font-bold tracking-wide text-white uppercase transition hover:bg-zinc-700">
                        Continue shopping
                    </Link>
                    <Link :href="editProfile()" class="rounded-full border border-zinc-400 px-6 py-3 text-sm font-bold tracking-wide text-zinc-900 uppercase transition hover:border-zinc-700">
                        Account settings
                    </Link>
                </div>
            </article>

            <article class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm lg:p-8">
                <h2 class="text-xl font-black text-zinc-900">Quick access</h2>
                <div class="mt-4 space-y-3">
                    <Link :href="cart.show()" class="block rounded-2xl border border-zinc-200 p-4 text-sm font-semibold text-zinc-700 transition hover:border-zinc-400">
                        View cart and checkout
                    </Link>
                    <Link :href="editProfile()" class="block rounded-2xl border border-zinc-200 p-4 text-sm font-semibold text-zinc-700 transition hover:border-zinc-400">
                        Update profile and password
                    </Link>
                    <Link :href="shop.index()" class="block rounded-2xl border border-zinc-200 p-4 text-sm font-semibold text-zinc-700 transition hover:border-zinc-400">
                        Browse new arrivals
                    </Link>
                </div>
            </article>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.brand-title {
    font-family: 'Playfair Display', serif;
}
</style>
