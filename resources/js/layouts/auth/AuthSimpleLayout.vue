<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative flex min-h-svh items-center justify-center overflow-hidden bg-zinc-50 p-6 md:p-10">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-orange-100 via-amber-50 to-rose-100" />
        <div class="pointer-events-none absolute -top-24 -left-16 h-72 w-72 rounded-full bg-red-200/50 blur-3xl" />
        <div class="pointer-events-none absolute -right-20 -bottom-24 h-72 w-72 rounded-full bg-amber-200/60 blur-3xl" />

        <div class="relative w-full max-w-md rounded-3xl border border-zinc-200 bg-white/95 p-7 shadow-xl backdrop-blur">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4 text-center">
                    <Link :href="home()" class="flex items-center gap-3 font-medium">
                        <span
                            class="inline-flex size-10 items-center justify-center rounded-xl bg-zinc-900 text-sm font-extrabold tracking-wide text-zinc-100"
                        >
                            TM
                        </span>
                        <span class="brand-title text-2xl font-black text-zinc-900">Tradition Me</span>
                    </Link>

                    <div class="space-y-2">
                        <h1 class="text-xl font-semibold text-zinc-900">{{ title }}</h1>
                        <p class="text-sm text-zinc-600">
                            {{ description }}
                        </p>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.brand-title {
    font-family: 'Playfair Display', serif;
}
</style>
