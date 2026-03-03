<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ChevronDown,
    LayoutGrid,
    LogOut,
    Settings,
    ShoppingBag,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { getInitials } from '@/composables/useInitials';
import { dashboard, home, login, logout, register } from '@/routes';
import cart from '@/routes/cart';
import cartItems from '@/routes/cart/items';
import collections from '@/routes/collections';
import newsletterSubscriptions from '@/routes/newsletter/subscriptions';
import { edit as editProfile } from '@/routes/profile';
import shop from '@/routes/shop';
import type { Auth } from '@/types';
import type {
    WelcomeCategory,
    WelcomeOccasion,
    WelcomeProduct,
    WelcomeReview,
} from '@/types/welcome';

withDefaults(
    defineProps<{
        canRegister: boolean;
        canAccessAdministration: boolean;
        cartCount: number;
        totalProducts: number;
        categories: WelcomeCategory[];
        products: WelcomeProduct[];
        occasions: WelcomeOccasion[];
        reviews: WelcomeReview[];
    }>(),
    {
        canRegister: true,
        canAccessAdministration: false,
        cartCount: 0,
        totalProducts: 0,
        categories: () => [],
        products: () => [],
        occasions: () => [],
        reviews: () => [],
    },
);

const addToCart = (productSlug: string): void => {
    const target = cartItems.store();

    router.post(
        target.url,
        { product_slug: productSlug },
        {
            preserveScroll: true,
        },
    );
};

const page = usePage<{ flash?: { status?: string }; auth?: Auth }>();
const flashStatus = computed(() => page.props.flash?.status ?? '');
const auth = computed(() => page.props.auth);
const user = computed(() => auth.value?.user);
const isAuthenticated = computed(() => Boolean(user.value));
</script>

<template>
    <Head title="Tradition Me"> </Head>

    <div class="min-h-screen bg-background text-foreground">
        <div
            class="bg-zinc-900 px-4 py-2 text-center text-xs font-semibold tracking-wide text-zinc-100 sm:text-sm"
        >
            Free shipping in Malaysia for orders above RM 200
        </div>

        <header
            class="sticky top-0 z-20 border-b border-border bg-background/95 backdrop-blur"
        >
            <div
                class="mx-auto grid w-full max-w-7xl grid-cols-1 items-center gap-4 px-4 py-4 sm:px-6 lg:grid-cols-[auto_1fr_auto] lg:px-10"
            >
                <Link :href="home()" class="flex items-center gap-3">
                    <span
                        class="inline-flex size-10 items-center justify-center rounded-xl bg-primary text-sm font-extrabold tracking-wide text-primary-foreground"
                    >
                        TM
                    </span>
                    <div>
                        <p class="tm-kicker text-primary">
                            Malaysian Multi-Cultural Fashion
                        </p>
                        <p
                            class="tm-display text-2xl font-extrabold text-foreground"
                        >
                            Tradition Me
                        </p>
                    </div>
                </Link>

                <nav
                    class="flex items-center gap-2 overflow-x-auto text-sm font-semibold"
                >
                    <Link
                        :href="home()"
                        class="rounded-full px-4 py-2 text-muted-foreground transition hover:bg-secondary hover:text-foreground"
                    >
                        Home
                    </Link>
                    <Link
                        :href="shop.index()"
                        class="rounded-full px-4 py-2 text-muted-foreground transition hover:bg-secondary hover:text-foreground"
                    >
                        Shop
                    </Link>
                </nav>

                <nav class="flex items-center gap-2 text-sm font-semibold">
                    <Link
                        :href="cart.show()"
                        class="rounded-full border border-border bg-card/80 px-4 py-2 text-sm font-semibold text-foreground transition hover:border-primary/45"
                    >
                        <span class="inline-flex items-center gap-2">
                            <ShoppingBag class="size-4" />
                            Cart ({{ cartCount }})
                        </span>
                    </Link>

                    <template v-if="isAuthenticated">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-2 py-1.5 text-foreground transition hover:border-primary/45"
                                >
                                    <Avatar
                                        class="size-8 rounded-full border border-border"
                                    >
                                        <AvatarImage
                                            v-if="user?.avatar"
                                            :src="user.avatar"
                                            :alt="user.name"
                                        />
                                        <AvatarFallback
                                            class="bg-primary text-xs font-bold text-primary-foreground"
                                        >
                                            {{ getInitials(user?.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <ChevronDown class="size-4" />
                                </button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-52">
                                <div class="px-2 py-1">
                                    <p
                                        class="truncate text-sm font-semibold text-foreground"
                                    >
                                        {{ user?.name }}
                                    </p>
                                    <p
                                        class="truncate text-xs text-muted-foreground"
                                    >
                                        {{ user?.email }}
                                    </p>
                                </div>
                                <DropdownMenuItem as-child>
                                    <Link
                                        :href="editProfile()"
                                        class="flex w-full items-center"
                                    >
                                        <Settings class="mr-2 size-4" />
                                        Settings
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="canAccessAdministration"
                                    as-child
                                >
                                    <Link
                                        :href="
                                            dashboard({ query: { admin: 1 } })
                                        "
                                        class="flex w-full items-center"
                                    >
                                        <LayoutGrid class="mr-2 size-4" />
                                        Administration
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <Link
                                        :href="logout()"
                                        method="post"
                                        as="button"
                                        class="flex w-full items-center text-red-700"
                                    >
                                        <LogOut class="mr-2 size-4" />
                                        Log out
                                    </Link>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="rounded-full border border-border px-4 py-2 text-foreground transition hover:border-primary/45"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="rounded-full bg-primary px-4 py-2 text-primary-foreground transition hover:bg-primary/90"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>

            <div class="border-t border-border bg-card/80">
                <div
                    class="mx-auto flex w-full max-w-7xl gap-2 overflow-x-auto px-4 py-3 sm:px-6 lg:px-10"
                >
                    <span
                        v-for="item in categories"
                        :key="item.slug"
                        class="rounded-full border border-border bg-background px-4 py-1 text-xs font-semibold tracking-wide whitespace-nowrap text-muted-foreground uppercase"
                    >
                        {{ item.name }}
                    </span>
                </div>
            </div>
        </header>

        <main
            class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-10 lg:py-8"
        >
            <section class="tm-stagger grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
                <article
                    class="tm-shell overflow-hidden bg-[linear-gradient(140deg,hsl(37_67%_94%)_0%,hsl(30_66%_92%)_45%,hsl(5_63%_91%)_100%)] p-6 lg:p-10 dark:bg-[linear-gradient(140deg,hsl(26_22%_15%)_0%,hsl(22_20%_13%)_45%,hsl(12_28%_16%)_100%)]"
                    data-aos="fade-up"
                >
                    <p class="tm-kicker text-primary">Mega Raya Sale 2026</p>
                    <h1
                        class="tm-display mt-3 max-w-2xl text-4xl leading-tight font-black text-foreground sm:text-5xl"
                    >
                        E-commerce for Malaysian
                        <span class="tm-display block text-primary"
                            >heritage fashion</span
                        >
                    </h1>
                    <p
                        class="mt-4 max-w-2xl text-base text-muted-foreground sm:text-lg"
                    >
                        Shop curated traditional and modern pieces inspired by
                        Malay, Chinese, Indian, Orang Asli, Sabah, and Sarawak
                        communities.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <Link
                            :href="shop.index()"
                            class="rounded-full bg-primary px-6 py-3 text-sm font-bold tracking-wide text-primary-foreground uppercase transition hover:bg-primary/90"
                        >
                            Shop Now
                        </Link>
                        <Link
                            :href="
                                shop.index({
                                    query: { category: 'new-arrivals' },
                                })
                            "
                            class="rounded-full border border-primary/45 bg-card/70 px-6 py-3 text-sm font-bold tracking-wide text-foreground uppercase transition hover:border-primary"
                        >
                            View New Arrivals
                        </Link>
                    </div>
                    <div
                        class="mt-6 flex flex-wrap gap-4 text-sm font-semibold text-muted-foreground"
                    >
                        <span>Secure checkout</span>
                        <span>7-day returns</span>
                        <span>Nationwide delivery</span>
                    </div>
                </article>

                <article
                    class="tm-shell rounded-3xl border-zinc-800 bg-zinc-900 p-6 text-zinc-100 shadow-sm transition hover:-translate-y-1 lg:p-8"
                    data-aos="fade-up"
                    data-aos-delay="120"
                >
                    <p
                        class="text-xs font-bold tracking-[0.2em] text-amber-300 uppercase"
                    >
                        Flash Deals
                    </p>
                    <div class="mt-4 space-y-3">
                        <div
                            class="rounded-2xl border border-white/20 bg-white/5 p-4"
                        >
                            <p
                                class="text-xs font-semibold tracking-[0.16em] text-amber-200 uppercase"
                            >
                                Today Only
                            </p>
                            <p class="mt-1 text-lg font-bold">
                                Buy 2, Get 20% Off
                            </p>
                            <p class="mt-1 text-sm text-zinc-300">
                                Applicable to selected kurung, kebaya, and
                                menswear sets.
                            </p>
                        </div>
                        <div
                            class="rounded-2xl border border-white/20 bg-white/5 p-4"
                        >
                            <p
                                class="text-xs font-semibold tracking-[0.16em] text-emerald-200 uppercase"
                            >
                                Express Tailoring
                            </p>
                            <p class="mt-1 text-lg font-bold">
                                Ready in 48 Hours
                            </p>
                            <p class="mt-1 text-sm text-zinc-300">
                                Available in Klang Valley for festive urgent
                                orders.
                            </p>
                        </div>
                    </div>
                </article>
            </section>

            <section class="mt-8" data-aos="fade-up" data-aos-delay="80">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-2xl font-black text-zinc-900 sm:text-3xl">
                        Featured Products
                    </h2>
                    <span class="text-sm font-semibold text-zinc-600"
                        >Showing {{ products.length }} of
                        {{ totalProducts }} items</span
                    >
                </div>
                <div
                    class="tm-stagger grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        v-for="product in products"
                        :key="product.name"
                        class="rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-1.5 hover:shadow-xl"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="rounded-full bg-zinc-900 px-3 py-1 text-[11px] font-bold tracking-wide text-white uppercase"
                            >
                                {{ product.badge }}
                            </span>
                            <span class="text-xs font-semibold text-zinc-500">{{
                                product.category
                            }}</span>
                        </div>
                        <div
                            class="h-36 rounded-2xl bg-gradient-to-br"
                            :class="product.gradient"
                        />
                        <h3 class="mt-4 text-lg font-extrabold text-zinc-900">
                            {{ product.name }}
                        </h3>
                        <div class="mt-3 flex items-center gap-2">
                            <p class="text-xl font-black text-red-700">
                                {{ product.price }}
                            </p>
                            <p
                                class="text-sm font-semibold text-zinc-400 line-through"
                            >
                                {{ product.originalPrice }}
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="addToCart(product.slug)"
                            class="mt-4 w-full rounded-full border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-bold tracking-wide text-white uppercase transition hover:-translate-y-0.5 hover:bg-zinc-700"
                        >
                            Add to Cart
                        </button>
                    </article>
                </div>
            </section>

            <section
                class="mt-8 grid gap-4 md:grid-cols-3"
                data-aos="fade-up"
                data-aos-delay="120"
            >
                <article class="tm-night-panel p-5">
                    <p class="text-sm font-bold text-zinc-900">
                        Trusted by 10,000+ shoppers
                    </p>
                    <p class="mt-1 text-sm text-zinc-600">
                        Verified reviews from families across Malaysia.
                    </p>
                </article>
                <article class="tm-night-panel p-5">
                    <p class="text-sm font-bold text-zinc-900">
                        Secure payment options
                    </p>
                    <p class="mt-1 text-sm text-zinc-600">
                        FPX, cards, and e-wallets supported.
                    </p>
                </article>
                <article class="tm-night-panel p-5">
                    <p class="text-sm font-bold text-zinc-900">
                        Easy size exchange
                    </p>
                    <p class="mt-1 text-sm text-zinc-600">
                        One-time exchange within 7 days.
                    </p>
                </article>
            </section>

            <section class="mt-8" data-aos="fade-up" data-aos-delay="160">
                <div class="mb-4 flex items-end justify-between">
                    <h2 class="text-2xl font-black text-zinc-900 sm:text-3xl">
                        Shop by Occasion
                    </h2>
                    <span class="text-sm font-semibold text-zinc-600"
                        >Tailored collections for real events</span
                    >
                </div>
                <div class="tm-stagger grid gap-4 md:grid-cols-3">
                    <article
                        v-for="occasion in occasions"
                        :key="occasion.name"
                        class="tm-night-panel p-5 transition duration-300 hover:-translate-y-1 hover:shadow-lg"
                    >
                        <span
                            class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold tracking-wide text-amber-800 uppercase"
                        >
                            {{ occasion.badge }}
                        </span>
                        <h3 class="mt-4 text-xl font-extrabold text-zinc-900">
                            {{ occasion.name }}
                        </h3>
                        <p class="mt-2 text-sm text-zinc-600">
                            {{ occasion.description }}
                        </p>
                        <Link
                            :href="collections.show({ slug: occasion.slug })"
                            class="mt-4 rounded-full border border-zinc-900 px-4 py-2 text-xs font-bold tracking-wide text-zinc-900 uppercase transition hover:bg-zinc-900 hover:text-white"
                        >
                            View Collection
                        </Link>
                    </article>
                </div>
            </section>

            <section
                class="mt-8 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]"
                data-aos="fade-up"
                data-aos-delay="200"
            >
                <article
                    class="tm-night-panel p-6 transition duration-300 hover:-translate-y-1 hover:shadow-lg lg:p-8"
                >
                    <h2 class="text-2xl font-black text-zinc-900">
                        Customer Reviews
                    </h2>
                    <div class="mt-4 space-y-3">
                        <div
                            v-for="review in reviews"
                            :key="review.name"
                            class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4"
                        >
                            <p class="text-sm text-zinc-700">
                                "{{ review.comment }}"
                            </p>
                            <p
                                class="mt-2 text-xs font-bold tracking-wide text-zinc-500 uppercase"
                            >
                                {{ review.name }} · {{ review.location }}
                            </p>
                        </div>
                    </div>
                </article>

                <article
                    class="rounded-3xl border border-zinc-200 bg-zinc-900 p-6 text-zinc-100 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl lg:p-8"
                >
                    <h2 class="text-2xl font-black">How It Works</h2>
                    <ol class="mt-4 space-y-3">
                        <li
                            class="rounded-2xl border border-white/20 bg-white/5 p-4"
                        >
                            <p
                                class="text-xs font-bold tracking-[0.16em] text-amber-200 uppercase"
                            >
                                Step 1
                            </p>
                            <p class="mt-1 text-sm">
                                Choose your style and preferred fit from curated
                                collections.
                            </p>
                        </li>
                        <li
                            class="rounded-2xl border border-white/20 bg-white/5 p-4"
                        >
                            <p
                                class="text-xs font-bold tracking-[0.16em] text-amber-200 uppercase"
                            >
                                Step 2
                            </p>
                            <p class="mt-1 text-sm">
                                Select measurement option: standard size or
                                tailoring request.
                            </p>
                        </li>
                        <li
                            class="rounded-2xl border border-white/20 bg-white/5 p-4"
                        >
                            <p
                                class="text-xs font-bold tracking-[0.16em] text-amber-200 uppercase"
                            >
                                Step 3
                            </p>
                            <p class="mt-1 text-sm">
                                Track delivery and receive updates until your
                                order arrives.
                            </p>
                        </li>
                    </ol>
                </article>
            </section>

            <section
                class="mt-8 grid gap-4 lg:grid-cols-[1fr_1fr]"
                data-aos="fade-up"
                data-aos-delay="240"
            >
                <article
                    class="tm-night-panel p-6 transition duration-300 hover:-translate-y-1 hover:shadow-lg"
                >
                    <h2 class="text-2xl font-black text-zinc-900">
                        Frequently Asked Questions
                    </h2>
                    <div class="mt-4 space-y-3">
                        <div class="rounded-2xl border border-zinc-200 p-4">
                            <p class="text-sm font-bold text-zinc-900">
                                Do you ship across all Malaysia regions?
                            </p>
                            <p class="mt-1 text-sm text-zinc-600">
                                Yes, we ship nationwide including Sabah and
                                Sarawak.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-zinc-200 p-4">
                            <p class="text-sm font-bold text-zinc-900">
                                Can I request custom measurements?
                            </p>
                            <p class="mt-1 text-sm text-zinc-600">
                                Yes, selected products support custom tailoring
                                during checkout.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-zinc-200 p-4">
                            <p class="text-sm font-bold text-zinc-900">
                                What if my size is not suitable?
                            </p>
                            <p class="mt-1 text-sm text-zinc-600">
                                You can request one size exchange within 7 days
                                after delivery.
                            </p>
                        </div>
                    </div>
                </article>

                <article
                    class="rounded-3xl border border-zinc-200 bg-gradient-to-br from-amber-50 to-orange-100 p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-border dark:from-zinc-900 dark:to-zinc-800"
                >
                    <p
                        class="text-xs font-bold tracking-[0.2em] text-red-700 uppercase"
                    >
                        Member Benefits
                    </p>
                    <h2
                        class="mt-3 text-2xl font-black text-zinc-900 sm:text-3xl"
                    >
                        Get Early Access to New Drops
                    </h2>
                    <p class="mt-2 text-sm text-zinc-700">
                        Join our member list for launch alerts, special
                        discounts, and styling updates for every festive season.
                    </p>
                    <Form
                        :action="newsletterSubscriptions.store().url"
                        method="post"
                        v-slot="{ errors, processing }"
                        class="mt-4"
                    >
                        <input
                            type="email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="Enter your email address"
                            class="w-full rounded-full border border-zinc-300 bg-white px-4 py-3 text-sm text-zinc-700 transition outline-none focus:border-zinc-500"
                        />
                        <input
                            type="hidden"
                            name="source"
                            value="welcome-page"
                        />
                        <p
                            v-if="errors.email"
                            class="mt-2 text-sm font-medium text-red-600"
                        >
                            {{ errors.email }}
                        </p>
                        <button
                            type="submit"
                            class="mt-4 rounded-full bg-zinc-900 px-6 py-3 text-sm font-bold tracking-wide text-white uppercase transition hover:bg-zinc-700 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="processing"
                        >
                            {{
                                processing ? 'Subscribing...' : 'Subscribe Now'
                            }}
                        </button>
                    </Form>
                    <p
                        v-if="flashStatus"
                        class="mt-3 text-sm font-semibold text-emerald-700"
                    >
                        {{ flashStatus }}
                    </p>
                </article>
            </section>
        </main>
    </div>
</template>
