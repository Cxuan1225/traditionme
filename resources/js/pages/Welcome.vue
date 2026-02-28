<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

type Product = {
    name: string;
    category: string;
    price: string;
    originalPrice: string;
    badge: string;
    gradient: string;
};

const categories = ['Women', 'Men', 'Kids', 'Accessories', 'Festive Sets', 'New Arrivals'];

const products: Product[] = [
    {
        name: 'Songket Luxe Kurung Set',
        category: 'Women',
        price: 'RM 289',
        originalPrice: 'RM 359',
        badge: 'Best Seller',
        gradient: 'from-rose-100 via-orange-50 to-amber-100',
    },
    {
        name: 'Cekak Musang Premium',
        category: 'Men',
        price: 'RM 229',
        originalPrice: 'RM 299',
        badge: 'Limited',
        gradient: 'from-emerald-100 via-teal-50 to-cyan-100',
    },
    {
        name: 'Nyonya Kebaya Bloom',
        category: 'Women',
        price: 'RM 259',
        originalPrice: 'RM 319',
        badge: 'New',
        gradient: 'from-fuchsia-100 via-pink-50 to-rose-100',
    },
    {
        name: 'Kids Raya Twin Set',
        category: 'Kids',
        price: 'RM 149',
        originalPrice: 'RM 189',
        badge: 'Hot',
        gradient: 'from-sky-100 via-blue-50 to-indigo-100',
    },
];

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Warisan Threads">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="min-h-screen bg-zinc-50 text-zinc-900">
        <div class="bg-zinc-900 px-4 py-2 text-center text-xs font-semibold tracking-wide text-zinc-100 sm:text-sm">
            Free shipping in Malaysia for orders above RM 200
        </div>

        <header class="sticky top-0 z-20 border-b border-zinc-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-10">
                <div>
                    <p class="text-xs font-bold tracking-[0.2em] text-amber-700 uppercase">Malaysian Multi-Cultural Fashion</p>
                    <p class="brand-title text-2xl font-extrabold text-zinc-900">Warisan Threads</p>
                </div>

                <div class="hidden flex-1 px-6 md:block">
                    <div class="rounded-full border border-zinc-300 bg-zinc-100 px-4 py-2 text-sm text-zinc-500">
                        Search kurung, kebaya, cheongsam, saree...
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="hidden rounded-full border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 sm:inline-flex">
                        Cart (2)
                    </span>
                    <nav class="flex items-center gap-2 text-sm font-semibold">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="dashboard()"
                            class="rounded-full bg-emerald-600 px-4 py-2 text-white transition hover:bg-emerald-500"
                        >
                            Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="login()"
                                class="rounded-full border border-zinc-300 px-4 py-2 text-zinc-900 transition hover:border-zinc-500"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="register()"
                                class="rounded-full bg-amber-500 px-4 py-2 text-zinc-900 transition hover:bg-amber-400"
                            >
                                Register
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>

            <div class="border-t border-zinc-200 bg-white">
                <div class="mx-auto flex w-full max-w-7xl gap-2 overflow-x-auto px-4 py-3 sm:px-6 lg:px-10">
                    <span
                        v-for="item in categories"
                        :key="item"
                        class="whitespace-nowrap rounded-full border border-zinc-300 bg-zinc-100 px-4 py-1 text-xs font-semibold tracking-wide text-zinc-700 uppercase"
                    >
                        {{ item }}
                    </span>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-10 lg:py-8">
            <section class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
                <article class="overflow-hidden rounded-3xl border border-zinc-200 bg-gradient-to-br from-orange-50 via-amber-50 to-rose-100 p-6 shadow-sm lg:p-10">
                    <p class="text-xs font-bold tracking-[0.25em] text-red-700 uppercase">Mega Raya Sale 2026</p>
                    <h1 class="mt-3 max-w-2xl text-4xl leading-tight font-black text-zinc-900 sm:text-5xl">
                        E-commerce for Malaysian
                        <span class="brand-title block text-red-700">heritage fashion</span>
                    </h1>
                    <p class="mt-4 max-w-2xl text-base text-zinc-700 sm:text-lg">
                        Shop curated traditional and modern pieces inspired by Malay, Chinese, Indian, Orang Asli, Sabah, and Sarawak communities.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="rounded-full bg-zinc-900 px-6 py-3 text-sm font-bold tracking-wide text-white uppercase transition hover:bg-zinc-700"
                        >
                            Shop Now
                        </button>
                        <button
                            type="button"
                            class="rounded-full border border-zinc-400 px-6 py-3 text-sm font-bold tracking-wide text-zinc-900 uppercase transition hover:border-zinc-700"
                        >
                            View New Arrivals
                        </button>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-4 text-sm font-semibold text-zinc-700">
                        <span>Secure checkout</span>
                        <span>7-day returns</span>
                        <span>Nationwide delivery</span>
                    </div>
                </article>

                <article class="rounded-3xl border border-zinc-200 bg-zinc-900 p-6 text-zinc-100 shadow-sm lg:p-8">
                    <p class="text-xs font-bold tracking-[0.2em] text-amber-300 uppercase">Flash Deals</p>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl border border-white/20 bg-white/5 p-4">
                            <p class="text-xs font-semibold tracking-[0.16em] text-amber-200 uppercase">Today Only</p>
                            <p class="mt-1 text-lg font-bold">Buy 2, Get 20% Off</p>
                            <p class="mt-1 text-sm text-zinc-300">Applicable to selected kurung, kebaya, and menswear sets.</p>
                        </div>
                        <div class="rounded-2xl border border-white/20 bg-white/5 p-4">
                            <p class="text-xs font-semibold tracking-[0.16em] text-emerald-200 uppercase">Express Tailoring</p>
                            <p class="mt-1 text-lg font-bold">Ready in 48 Hours</p>
                            <p class="mt-1 text-sm text-zinc-300">Available in Klang Valley for festive urgent orders.</p>
                        </div>
                    </div>
                </article>
            </section>

            <section class="mt-8">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-2xl font-black text-zinc-900 sm:text-3xl">Featured Products</h2>
                    <span class="text-sm font-semibold text-zinc-600">Showing 4 of 120 items</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="product in products"
                        :key="product.name"
                        class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span class="rounded-full bg-zinc-900 px-3 py-1 text-[11px] font-bold tracking-wide text-white uppercase">
                                {{ product.badge }}
                            </span>
                            <span class="text-xs font-semibold text-zinc-500">{{ product.category }}</span>
                        </div>
                        <div class="h-36 rounded-2xl bg-gradient-to-br" :class="product.gradient" />
                        <h3 class="mt-4 text-lg font-extrabold text-zinc-900">{{ product.name }}</h3>
                        <div class="mt-3 flex items-center gap-2">
                            <p class="text-xl font-black text-red-700">{{ product.price }}</p>
                            <p class="text-sm font-semibold text-zinc-400 line-through">{{ product.originalPrice }}</p>
                        </div>
                        <button
                            type="button"
                            class="mt-4 w-full rounded-full border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-bold tracking-wide text-white uppercase transition hover:bg-zinc-700"
                        >
                            Add to Cart
                        </button>
                    </article>
                </div>
            </section>

            <section class="mt-8 grid gap-4 md:grid-cols-3">
                <article class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <p class="text-sm font-bold text-zinc-900">Trusted by 10,000+ shoppers</p>
                    <p class="mt-1 text-sm text-zinc-600">Verified reviews from families across Malaysia.</p>
                </article>
                <article class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <p class="text-sm font-bold text-zinc-900">Secure payment options</p>
                    <p class="mt-1 text-sm text-zinc-600">FPX, cards, and e-wallets supported.</p>
                </article>
                <article class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <p class="text-sm font-bold text-zinc-900">Easy size exchange</p>
                    <p class="mt-1 text-sm text-zinc-600">One-time exchange within 7 days.</p>
                </article>
            </section>
        </main>
    </div>
</template>

<style scoped>
.brand-title {
    font-family: 'Playfair Display', serif;
}
</style>
