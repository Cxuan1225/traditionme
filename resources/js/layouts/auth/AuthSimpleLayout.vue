<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-svh bg-zinc-50 text-zinc-900">
        <div class="bg-zinc-900 px-4 py-2 text-center text-xs font-semibold tracking-wide text-zinc-100 sm:text-sm">
            Free shipping in Malaysia for orders above RM 200
        </div>

        <header class="border-b border-zinc-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-10">
                <Link :href="home()" class="flex items-center gap-3">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-zinc-900 text-sm font-extrabold tracking-wide text-zinc-100">
                        TM
                    </span>
                    <div>
                        <p class="text-xs font-bold tracking-[0.2em] text-amber-700 uppercase">Malaysian Multi-Cultural Fashion</p>
                        <p class="brand-title text-2xl font-extrabold text-zinc-900">Tradition Me</p>
                    </div>
                </Link>

                <p class="hidden text-xs font-semibold tracking-wide text-zinc-700 md:block">
                    Secure account access for your orders and preferences
                </p>
            </div>
        </header>

        <div class="relative mx-auto flex w-full max-w-7xl items-center justify-center px-4 py-8 sm:px-6 lg:px-10">
            <div class="pointer-events-none absolute inset-x-8 top-4 h-40 rounded-full bg-gradient-to-r from-orange-200/55 via-amber-200/45 to-rose-200/45 blur-3xl" />

            <section
                class="relative w-full max-w-5xl overflow-hidden rounded-3xl border border-zinc-200 bg-white/95 shadow-2xl backdrop-blur lg:grid lg:grid-cols-[1.05fr_0.95fr]"
                data-aos="fade-up"
            >
                <aside class="relative hidden bg-zinc-900 p-10 text-zinc-100 lg:flex lg:flex-col lg:justify-between">
                    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(245,158,11,0.35),transparent_55%),radial-gradient(circle_at_85%_70%,rgba(239,68,68,0.28),transparent_60%)]" />
                    <div class="relative space-y-6">
                        <p class="text-xs font-bold tracking-[0.24em] text-amber-300 uppercase">Tradition Me Members</p>
                        <h2 class="text-3xl leading-tight font-black">
                            Style rooted in heritage, delivered with modern ease.
                        </h2>
                        <p class="max-w-sm text-sm text-zinc-300">
                            Access your account to manage orders, update details, and enjoy a faster checkout experience.
                        </p>
                    </div>
                    <div class="relative grid gap-3 text-sm">
                        <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3">Secure checkout and account protection</div>
                        <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3">Fast delivery across Malaysia</div>
                    </div>
                </aside>

                <section class="p-6 sm:p-8 lg:p-10">
                    <div class="mx-auto w-full max-w-md">
                        <div class="mb-8 flex flex-col items-center gap-4 text-center lg:items-start lg:text-left">
                            <div class="space-y-2">
                                <h1 class="text-2xl font-black text-zinc-900">{{ title }}</h1>
                                <p class="text-sm font-medium text-zinc-800">
                                    {{ description }}
                                </p>
                            </div>
                        </div>
                        <slot />
                    </div>
                </section>
            </section>
        </div>
    </div>
</template>

<style scoped>
.brand-title {
    font-family: 'Playfair Display', serif;
}
</style>
