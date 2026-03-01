<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { home } from '@/routes';
</script>

<template>
    <Head title="Cart" />

    <main class="min-h-screen bg-zinc-50 px-4 py-10 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-4xl rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm">
            <p class="text-xs font-bold tracking-[0.2em] text-amber-700 uppercase">Tradition Me Cart</p>
            <h1 class="mt-2 text-3xl font-black text-zinc-900">Your Cart</h1>
            <p class="mt-3 text-zinc-600">
                Cart route is now live and can be expanded with line items, pricing, and checkout flows.
            </p>
            <Link
                :href="home()"
                class="mt-8 inline-flex rounded-full bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
            >
                Continue Shopping
            </Link>
        </div>
    </main>
</template>
