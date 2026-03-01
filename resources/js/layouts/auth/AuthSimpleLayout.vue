<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative flex min-h-svh items-center justify-center overflow-hidden bg-zinc-50 p-4 sm:p-6 md:p-10">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-orange-100 via-amber-50 to-rose-100" />
        <div class="pointer-events-none absolute -top-24 -left-16 h-72 w-72 rounded-full bg-red-200/50 blur-3xl" />
        <div class="pointer-events-none absolute -right-20 -bottom-24 h-72 w-72 rounded-full bg-amber-200/60 blur-3xl" />

        <div class="relative grid w-full max-w-5xl overflow-hidden rounded-3xl border border-zinc-200 bg-white/95 shadow-2xl backdrop-blur lg:grid-cols-[1.05fr_0.95fr]">
            <aside class="relative hidden bg-zinc-900 p-10 text-zinc-100 lg:flex lg:flex-col lg:justify-between" data-aos="fade-right">
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(245,158,11,0.35),transparent_55%),radial-gradient(circle_at_85%_70%,rgba(239,68,68,0.28),transparent_60%)]" />
                <div class="relative space-y-8">
                    <Link :href="home()" class="inline-flex items-center gap-3">
                        <span class="inline-flex size-10 items-center justify-center rounded-xl bg-zinc-100 text-sm font-extrabold tracking-wide text-zinc-900">
                            TM
                        </span>
                        <span class="brand-title text-3xl font-black text-zinc-100">Tradition Me</span>
                    </Link>
                    <div class="space-y-4">
                        <p class="text-xs font-bold tracking-[0.24em] text-amber-300 uppercase">Malaysian Heritage Fashion</p>
                        <h2 class="text-3xl leading-tight font-black">
                            Where tradition becomes your everyday signature.
                        </h2>
                        <p class="max-w-sm text-sm text-zinc-300">
                            Discover curated looks inspired by Malay, Chinese, Indian, Orang Asli, Sabah and Sarawak communities.
                        </p>
                    </div>
                </div>
                <div class="relative grid gap-3 text-sm">
                    <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3">Secure checkout and account protection</div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3">Fast delivery across Malaysia</div>
                </div>
            </aside>

            <section class="p-6 sm:p-8 lg:p-10" data-aos="fade-left" data-aos-delay="120">
                <div class="mx-auto w-full max-w-md">
                    <div class="mb-8 flex flex-col items-center gap-4 text-center lg:items-start lg:text-left">
                        <Link :href="home()" class="inline-flex items-center gap-3 font-medium lg:hidden">
                            <span
                                class="inline-flex size-10 items-center justify-center rounded-xl bg-zinc-900 text-sm font-extrabold tracking-wide text-zinc-100"
                            >
                                TM
                            </span>
                            <span class="brand-title text-2xl font-black text-zinc-900">Tradition Me</span>
                        </Link>
                        <div class="space-y-2">
                            <h1 class="text-2xl font-black text-zinc-900">{{ title }}</h1>
                            <p class="text-sm text-zinc-600">
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <slot />
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.brand-title {
    font-family: 'Playfair Display', serif;
}
</style>
