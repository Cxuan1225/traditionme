<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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

    <main class="min-h-screen bg-zinc-50 px-4 py-10 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-4xl rounded-3xl border border-zinc-200 bg-white p-8 shadow-sm">
            <p class="text-xs font-bold tracking-[0.2em] text-amber-700 uppercase">Tradition Me Shop</p>
            <h1 class="mt-2 text-3xl font-black text-zinc-900">Shop Collections</h1>
            <p class="mt-3 text-zinc-600">
                Product listing route is now active and ready for full catalog integration.
            </p>
            <div class="mt-6 grid gap-2 text-sm text-zinc-700">
                <p><span class="font-semibold">Category:</span> {{ category || 'All' }}</p>
                <p><span class="font-semibold">Occasion:</span> {{ occasion || 'All' }}</p>
            </div>
            <Link
                :href="home()"
                class="mt-8 inline-flex rounded-full bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800"
            >
                Back to Home
            </Link>
        </div>
    </main>
</template>
