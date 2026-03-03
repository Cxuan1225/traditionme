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
            <article
                class="tm-shell overflow-hidden bg-[linear-gradient(140deg,hsl(36_64%_94%)_0%,hsl(24_70%_93%)_45%,hsl(9_62%_90%)_100%)] p-6 lg:p-10"
            >
                <p class="tm-kicker text-primary">Welcome back</p>
                <h1
                    class="tm-display mt-3 text-4xl leading-tight font-black text-foreground sm:text-5xl"
                >
                    Hi {{ userName }}, continue your
                    <span class="tm-display block text-primary"
                        >Tradition Me</span
                    >
                    journey.
                </h1>
                <p class="mt-4 max-w-xl text-base text-muted-foreground">
                    Shop festive collections, update your profile, and manage
                    your account preferences from one place.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <Link
                        :href="shop.index()"
                        class="rounded-full bg-primary px-6 py-3 text-sm font-bold tracking-wide text-primary-foreground uppercase transition hover:bg-primary/90"
                    >
                        Continue shopping
                    </Link>
                    <Link
                        :href="editProfile()"
                        class="rounded-full border border-primary/45 bg-card/70 px-6 py-3 text-sm font-bold tracking-wide text-foreground uppercase transition hover:border-primary"
                    >
                        Account settings
                    </Link>
                </div>
            </article>

            <article class="tm-shell p-6 lg:p-8">
                <h2 class="tm-display text-xl font-black text-foreground">
                    Quick access
                </h2>
                <div class="mt-4 space-y-3">
                    <Link
                        :href="cart.show()"
                        class="tm-subtle-card block p-4 text-sm font-semibold text-foreground transition hover:border-primary/35"
                    >
                        View cart and checkout
                    </Link>
                    <Link
                        :href="editProfile()"
                        class="tm-subtle-card block p-4 text-sm font-semibold text-foreground transition hover:border-primary/35"
                    >
                        Update profile and password
                    </Link>
                    <Link
                        :href="shop.index()"
                        class="tm-subtle-card block p-4 text-sm font-semibold text-foreground transition hover:border-primary/35"
                    >
                        Browse new arrivals
                    </Link>
                </div>
            </article>
        </section>
    </StorefrontLayout>
</template>
