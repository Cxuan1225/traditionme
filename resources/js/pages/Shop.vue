<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import { home } from '@/routes';

withDefaults(
    defineProps<{
        category?: string;
        occasion?: string;
    }>(),
    {
        category: '',
        occasion: '',
    },
);
</script>

<template>
    <Head title="Shop" />

    <StorefrontLayout>
        <section class="space-y-5">
            <article class="tm-shell p-6 lg:p-8">
                <p class="tm-kicker text-primary">Tradition Me Shop</p>
                <h1
                    class="tm-display mt-2 text-3xl font-black text-foreground lg:text-4xl"
                >
                    Shop Collections
                </h1>
                <p class="mt-3 max-w-2xl text-muted-foreground">
                    Product listing route is active and prepared for full
                    catalog integration with filter and discovery flows.
                </p>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="tm-subtle-card p-4">
                        <p class="tm-kicker text-muted-foreground">Category</p>
                        <p class="mt-2 text-lg font-bold text-foreground">
                            {{ category || 'All' }}
                        </p>
                    </div>
                    <div class="tm-subtle-card p-4">
                        <p class="tm-kicker text-muted-foreground">Occasion</p>
                        <p class="mt-2 text-lg font-bold text-foreground">
                            {{ occasion || 'All' }}
                        </p>
                    </div>
                </div>
            </article>

            <article class="tm-shell p-6">
                <p class="text-sm text-muted-foreground">
                    Catalog cards, sorting, and pagination will plug into this
                    shell without route or behavior changes.
                </p>
                <Link
                    :href="home()"
                    class="mt-5 inline-flex rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                >
                    Back to Home
                </Link>
            </article>
        </section>
    </StorefrontLayout>
</template>
