<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import { home } from '@/routes';
</script>

<template>
    <Head title="Cart" />

    <StorefrontLayout>
        <section class="space-y-5">
            <article class="tm-shell p-6 lg:p-8">
                <p class="tm-kicker text-primary">Tradition Me Cart</p>
                <h1
                    class="tm-display mt-2 text-3xl font-black text-foreground lg:text-4xl"
                >
                    Your Cart
                </h1>
                <p class="mt-3 max-w-2xl text-muted-foreground">
                    Cart route is live and ready for line items, quantity
                    updates, and checkout progression.
                </p>
                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="tm-subtle-card p-4">
                        <p class="tm-kicker text-muted-foreground">Items</p>
                        <p class="mt-2 text-2xl font-black text-foreground">
                            0
                        </p>
                    </div>
                    <div class="tm-subtle-card p-4">
                        <p class="tm-kicker text-muted-foreground">Subtotal</p>
                        <p class="mt-2 text-2xl font-black text-foreground">
                            RM 0.00
                        </p>
                    </div>
                    <div class="tm-subtle-card p-4">
                        <p class="tm-kicker text-muted-foreground">Status</p>
                        <p class="mt-2 text-2xl font-black text-foreground">
                            Draft
                        </p>
                    </div>
                </div>
            </article>

            <article class="tm-shell p-6">
                <p class="text-sm text-muted-foreground">
                    Checkout stages and payment summaries will render here using
                    existing cart endpoints.
                </p>
                <Link
                    :href="home()"
                    class="mt-5 inline-flex rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                >
                    Continue Shopping
                </Link>
            </article>
        </section>
    </StorefrontLayout>
</template>
