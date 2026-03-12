<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal, Sparkles, Tag } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { toRinggit } from '@/composables/useCurrency';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import { home } from '@/routes';
import cart from '@/routes/cart';
import cartItems from '@/routes/cart/items';
import shop from '@/routes/shop';
import type { PaginatedResponse, ShopFilters, ShopProduct } from '@/types/shop';

const props = withDefaults(
    defineProps<{
        products: PaginatedResponse<ShopProduct>;
        filters: ShopFilters;
        categories: string[];
    }>(),
    {
        categories: () => [],
    },
);

const query = ref<string>(props.filters.search);
const selectedCategory = ref<string>(props.filters.category || 'all');
const selectedSort = ref<string>(props.filters.sort);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = (): void => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    debounceTimer = setTimeout(() => {
        router.get(
            shop.index(),
            {
                category:
                    selectedCategory.value !== 'all'
                        ? selectedCategory.value
                        : undefined,
                search: query.value || undefined,
                sort:
                    selectedSort.value !== 'featured'
                        ? selectedSort.value
                        : undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    }, 300);
};

const clearFilters = (): void => {
    query.value = '';
    selectedCategory.value = 'all';
    selectedSort.value = 'featured';
    router.get(shop.index(), {}, { preserveState: true, preserveScroll: true });
};

const addToCart = (productSlug: string): void => {
    router.post(
        cartItems.store(),
        { product_slug: productSlug },
        { preserveScroll: true },
    );
};

const activeFilters = (): string[] => {
    const filters: string[] = [];
    if (selectedCategory.value !== 'all') {
        filters.push(selectedCategory.value);
    }
    if (query.value.trim() !== '') {
        filters.push(`Search: ${query.value.trim()}`);
    }
    return filters;
};

watch([query, selectedCategory, selectedSort], applyFilters);
</script>

<template>
    <Head title="Shop" />

    <StorefrontLayout>
        <section class="space-y-6">
            <article class="tm-section">
                <p class="tm-kicker text-primary">Discover Collections</p>
                <h1 class="tm-title-xl mt-2">
                    Find your signature heritage look.
                </h1>
                <p class="tm-body mt-3 max-w-3xl">
                    Browse curated traditional and modern pieces by category and
                    style mood.
                </p>
                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="tm-stat">
                        <p class="tm-kicker text-muted-foreground">
                            Categories
                        </p>
                        <p class="tm-title mt-2">
                            {{ categories.length }}
                        </p>
                    </div>
                    <div class="tm-stat">
                        <p class="tm-kicker text-muted-foreground">Products</p>
                        <p class="tm-title mt-2">{{ products.total }}</p>
                    </div>
                    <div class="tm-stat">
                        <p class="tm-kicker text-muted-foreground">Page</p>
                        <p class="tm-title mt-2">
                            {{ products.current_page }} /
                            {{ products.last_page }}
                        </p>
                    </div>
                </div>
            </article>

            <div class="grid gap-5 xl:grid-cols-[280px_1fr]">
                <aside class="tm-panel h-fit p-5 xl:sticky xl:top-24">
                    <div class="mb-4 flex items-center gap-2">
                        <SlidersHorizontal class="size-4 text-primary" />
                        <p class="tm-subtitle">Filter & Sort</p>
                    </div>

                    <div class="space-y-4">
                        <div class="tm-form-field">
                            <label class="tm-label" for="shop-search"
                                >Search</label
                            >
                            <div class="relative">
                                <Search
                                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="shop-search"
                                    v-model="query"
                                    placeholder="Search collection or style"
                                    class="tm-input-surface pl-10"
                                />
                            </div>
                        </div>

                        <div class="tm-form-field">
                            <p class="tm-label">Category</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="tm-filter-pill"
                                    :class="{
                                        'tm-filter-pill-active':
                                            selectedCategory === 'all',
                                    }"
                                    :aria-pressed="selectedCategory === 'all'"
                                    @click="selectedCategory = 'all'"
                                >
                                    All Categories
                                </button>
                                <button
                                    v-for="cat in categories"
                                    :key="cat"
                                    type="button"
                                    class="tm-filter-pill"
                                    :class="{
                                        'tm-filter-pill-active':
                                            selectedCategory === cat,
                                    }"
                                    :aria-pressed="selectedCategory === cat"
                                    @click="selectedCategory = cat"
                                >
                                    {{ cat }}
                                </button>
                            </div>
                        </div>

                        <div class="tm-form-field">
                            <label class="tm-label" for="shop-sort"
                                >Sort by</label
                            >
                            <select
                                id="shop-sort"
                                v-model="selectedSort"
                                class="tm-input-surface px-3 text-sm"
                            >
                                <option value="featured">Featured</option>
                                <option value="newest">Newest</option>
                                <option value="price-asc">
                                    Price: Low to high
                                </option>
                                <option value="price-desc">
                                    Price: High to low
                                </option>
                            </select>
                        </div>
                    </div>

                    <Button
                        variant="outline"
                        size="sm"
                        class="mt-5 w-full"
                        aria-label="Reset all filters"
                        @click="clearFilters"
                    >
                        Reset filters
                    </Button>
                </aside>

                <section class="space-y-4">
                    <article class="tm-panel p-4">
                        <div
                            class="flex flex-wrap items-center justify-between gap-3"
                        >
                            <div aria-live="polite">
                                <p class="tm-subtitle">Product discovery</p>
                                <p class="tm-body-sm mt-1">
                                    {{ products.total }} item(s) matched your
                                    current selection.
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge variant="secondary">
                                    <Tag class="size-3.5" />
                                    {{ selectedSort.replace('-', ' ') }}
                                </Badge>
                                <Link :href="cart.show()">
                                    <Button size="sm">View cart</Button>
                                </Link>
                            </div>
                        </div>
                        <div
                            v-if="activeFilters().length > 0"
                            class="mt-3 flex flex-wrap gap-2"
                        >
                            <span
                                v-for="filter in activeFilters()"
                                :key="filter"
                                class="tm-chip"
                            >
                                {{ filter }}
                            </span>
                            <button
                                type="button"
                                class="tm-chip-strong"
                                @click="clearFilters"
                            >
                                Clear all
                            </button>
                        </div>
                    </article>

                    <article
                        v-if="products.data.length === 0"
                        class="tm-section text-center"
                        aria-live="polite"
                    >
                        <Sparkles class="mx-auto size-9 text-primary" />
                        <h2 class="tm-title mt-3">No products found</h2>
                        <p class="tm-body mt-2">
                            Try adjusting category or search keywords to
                            discover available styles.
                        </p>
                        <div class="mt-5 flex flex-wrap justify-center gap-2">
                            <Button variant="outline" @click="clearFilters"
                                >Reset discovery</Button
                            >
                            <Link :href="home()">
                                <Button>Back to home</Button>
                            </Link>
                        </div>
                    </article>

                    <div
                        v-else
                        class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
                    >
                        <article
                            v-for="product in products.data"
                            :key="product.id"
                            class="tm-product-card"
                        >
                            <div
                                v-if="product.badge"
                                class="mb-3 flex items-center justify-between gap-2"
                            >
                                <span class="tm-chip-strong">{{
                                    product.badge
                                }}</span>
                            </div>
                            <div
                                class="tm-product-media bg-linear-to-br"
                                :class="
                                    product.gradient ??
                                    'from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700'
                                "
                            >
                                <img
                                    v-if="product.imageUrl"
                                    :src="product.imageUrl"
                                    :alt="product.name"
                                    class="h-full w-full object-cover"
                                    loading="lazy"
                                />
                            </div>
                            <h3 class="tm-title mt-4 text-xl">
                                {{ product.name }}
                            </h3>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="tm-chip">{{
                                    product.category
                                }}</span>
                            </div>
                            <div class="mt-4 flex items-end gap-2">
                                <p class="tm-title text-primary">
                                    {{ toRinggit(product.priceInSen) }}
                                </p>
                                <p
                                    v-if="
                                        product.originalPriceInSen !== null &&
                                        product.originalPriceInSen !==
                                            product.priceInSen
                                    "
                                    class="tm-body-sm line-through"
                                >
                                    {{ toRinggit(product.originalPriceInSen) }}
                                </p>
                            </div>
                            <Button
                                class="mt-4 w-full"
                                :aria-label="`Add ${product.name} to cart`"
                                @click="addToCart(product.slug)"
                            >
                                Add to cart
                            </Button>
                        </article>
                    </div>

                    <nav
                        v-if="products.last_page > 1"
                        class="flex flex-wrap items-center justify-center gap-1"
                        aria-label="Pagination"
                    >
                        <template
                            v-for="link in products.links"
                            :key="link.label"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="tm-filter-pill"
                                :class="{
                                    'tm-filter-pill-active': link.active,
                                }"
                                preserve-state
                                preserve-scroll
                            >
                                <span v-html="link.label" />
                            </Link>
                            <span v-else class="tm-filter-pill opacity-40">
                                <span v-html="link.label" />
                            </span>
                        </template>
                    </nav>
                </section>
            </div>
        </section>
    </StorefrontLayout>
</template>
