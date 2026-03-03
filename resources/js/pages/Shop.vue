<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Search, SlidersHorizontal, Sparkles, Tag } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import { home } from '@/routes';
import cart from '@/routes/cart';

type CatalogProduct = {
    id: number;
    slug: string;
    name: string;
    category: string;
    occasion: string;
    badge: string;
    rating: number;
    priceInSen: number;
    originalPriceInSen: number | null;
    tone: string;
    stock: 'ready' | 'low' | 'preorder';
};

const catalog: CatalogProduct[] = [
    {
        id: 1,
        slug: 'seri-heritage-kurung',
        name: 'Seri Heritage Kurung',
        category: 'Baju Kurung',
        occasion: 'Hari Raya',
        badge: 'Best Seller',
        rating: 4.9,
        priceInSen: 28900,
        originalPriceInSen: 34900,
        tone: 'from-rose-100 via-amber-50 to-orange-100',
        stock: 'ready',
    },
    {
        id: 2,
        slug: 'peranakan-kebaya-classic',
        name: 'Peranakan Kebaya Classic',
        category: 'Kebaya',
        occasion: 'Wedding',
        badge: 'Limited',
        rating: 4.8,
        priceInSen: 31900,
        originalPriceInSen: null,
        tone: 'from-red-100 via-pink-50 to-amber-100',
        stock: 'low',
    },
    {
        id: 3,
        slug: 'modern-cheongsam-luna',
        name: 'Modern Cheongsam Luna',
        category: 'Cheongsam',
        occasion: 'Dinner',
        badge: 'New',
        rating: 4.7,
        priceInSen: 25900,
        originalPriceInSen: 29900,
        tone: 'from-fuchsia-100 via-rose-50 to-orange-100',
        stock: 'ready',
    },
    {
        id: 4,
        slug: 'saree-noor-royal',
        name: 'Saree Noor Royal',
        category: 'Saree',
        occasion: 'Deepavali',
        badge: 'Premium',
        rating: 4.9,
        priceInSen: 39900,
        originalPriceInSen: 45900,
        tone: 'from-violet-100 via-indigo-50 to-purple-100',
        stock: 'preorder',
    },
    {
        id: 5,
        slug: 'men-baju-melayu-arya',
        name: 'Men Baju Melayu Arya',
        category: 'Menswear',
        occasion: 'Hari Raya',
        badge: 'Top Rated',
        rating: 4.8,
        priceInSen: 22900,
        originalPriceInSen: null,
        tone: 'from-emerald-100 via-teal-50 to-cyan-100',
        stock: 'ready',
    },
    {
        id: 6,
        slug: 'songket-couple-signature',
        name: 'Songket Couple Signature',
        category: 'Couple Set',
        occasion: 'Engagement',
        badge: 'Bundle',
        rating: 5,
        priceInSen: 55900,
        originalPriceInSen: 63900,
        tone: 'from-amber-100 via-yellow-50 to-orange-100',
        stock: 'low',
    },
];

const toRinggit = (valueInSen: number): string =>
    `RM ${(valueInSen / 100).toFixed(2)}`;

const stockLabelMap: Record<CatalogProduct['stock'], string> = {
    ready: 'Ready to ship',
    low: 'Low stock',
    preorder: 'Pre-order',
};

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

const query = ref<string>('');
const selectedCategory = ref<string>('all');
const selectedOccasion = ref<string>('all');
const selectedSort = ref<string>('featured');
const readyToShipOnly = ref<boolean>(false);
const loadingPreview = ref<boolean>(false);
let loadingTimer: ReturnType<typeof setTimeout> | null = null;

const categoryOptions = computed<string[]>(() => [
    'all',
    ...new Set(catalog.map((item) => item.category)),
]);

const occasionOptions = computed<string[]>(() => [
    'all',
    ...new Set(catalog.map((item) => item.occasion)),
]);

const filteredProducts = computed<CatalogProduct[]>(() =>
    catalog.filter((item) => {
        const matchesSearch =
            query.value.trim() === '' ||
            item.name.toLowerCase().includes(query.value.toLowerCase()) ||
            item.category.toLowerCase().includes(query.value.toLowerCase()) ||
            item.occasion.toLowerCase().includes(query.value.toLowerCase());
        const matchesCategory =
            selectedCategory.value === 'all' ||
            item.category === selectedCategory.value;
        const matchesOccasion =
            selectedOccasion.value === 'all' ||
            item.occasion === selectedOccasion.value;
        const matchesReadyFilter =
            !readyToShipOnly.value || item.stock === 'ready';

        return (
            matchesSearch &&
            matchesCategory &&
            matchesOccasion &&
            matchesReadyFilter
        );
    }),
);

const sortedProducts = computed<CatalogProduct[]>(() => {
    const products = [...filteredProducts.value];

    switch (selectedSort.value) {
        case 'price-asc':
            return products.sort((a, b) => a.priceInSen - b.priceInSen);
        case 'price-desc':
            return products.sort((a, b) => b.priceInSen - a.priceInSen);
        case 'rating':
            return products.sort((a, b) => b.rating - a.rating);
        case 'newest':
            return products.sort((a, b) => b.id - a.id);
        default:
            return products;
    }
});

const activeFilters = computed<string[]>(() => {
    const filters: string[] = [];
    if (selectedCategory.value !== 'all') {
        filters.push(selectedCategory.value);
    }
    if (selectedOccasion.value !== 'all') {
        filters.push(selectedOccasion.value);
    }
    if (readyToShipOnly.value) {
        filters.push('Ready to ship only');
    }
    if (query.value.trim() !== '') {
        filters.push(`Search: ${query.value.trim()}`);
    }

    return filters;
});

const clearFilters = (): void => {
    query.value = '';
    selectedCategory.value = 'all';
    selectedOccasion.value = 'all';
    selectedSort.value = 'featured';
    readyToShipOnly.value = false;
};

watch(
    [query, selectedCategory, selectedOccasion, selectedSort, readyToShipOnly],
    () => {
        if (loadingTimer !== null) {
            clearTimeout(loadingTimer);
        }
        loadingPreview.value = true;
        loadingTimer = setTimeout(() => {
            loadingPreview.value = false;
        }, 280);
    },
);

onBeforeUnmount(() => {
    if (loadingTimer !== null) {
        clearTimeout(loadingTimer);
    }
});
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
                    Browse curated traditional and modern pieces by category,
                    occasion, and style mood. This discovery experience is wired
                    to current routes and ready for backend catalog integration.
                </p>
                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="tm-stat">
                        <p class="tm-kicker text-muted-foreground">
                            Collections
                        </p>
                        <p class="tm-title mt-2">
                            {{ categoryOptions.length - 1 }}
                        </p>
                    </div>
                    <div class="tm-stat">
                        <p class="tm-kicker text-muted-foreground">Occasions</p>
                        <p class="tm-title mt-2">
                            {{ occasionOptions.length - 1 }}
                        </p>
                    </div>
                    <div class="tm-stat">
                        <p class="tm-kicker text-muted-foreground">Products</p>
                        <p class="tm-title mt-2">{{ sortedProducts.length }}</p>
                    </div>
                </div>
            </article>

            <div class="grid gap-5 xl:grid-cols-[280px_1fr]">
                <aside class="tm-panel h-fit p-5 xl:sticky xl:top-24">
                    <div class="mb-4 flex items-center gap-2">
                        <SlidersHorizontal class="text-primary size-4" />
                        <p class="tm-subtitle">Filter & Sort</p>
                    </div>

                    <div class="space-y-4">
                        <div class="tm-form-field">
                            <label class="tm-label" for="shop-search"
                                >Search</label
                            >
                            <div class="relative">
                                <Search
                                    class="text-muted-foreground pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2"
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
                                    v-for="option in categoryOptions"
                                    :key="option"
                                    type="button"
                                    class="tm-filter-pill"
                                    :class="{
                                        'tm-filter-pill-active':
                                            selectedCategory === option,
                                    }"
                                    :aria-pressed="selectedCategory === option"
                                    @click="selectedCategory = option"
                                >
                                    {{
                                        option === 'all'
                                            ? 'All Categories'
                                            : option
                                    }}
                                </button>
                            </div>
                        </div>

                        <div class="tm-form-field">
                            <p class="tm-label">Occasion</p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="option in occasionOptions"
                                    :key="option"
                                    type="button"
                                    class="tm-filter-pill"
                                    :class="{
                                        'tm-filter-pill-active':
                                            selectedOccasion === option,
                                    }"
                                    :aria-pressed="selectedOccasion === option"
                                    @click="selectedOccasion = option"
                                >
                                    {{
                                        option === 'all'
                                            ? 'All Occasions'
                                            : option
                                    }}
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
                                <option value="rating">Top rated</option>
                                <option value="price-asc">
                                    Price: Low to high
                                </option>
                                <option value="price-desc">
                                    Price: High to low
                                </option>
                            </select>
                        </div>

                        <label
                            class="text-foreground flex items-center gap-2 text-sm font-medium"
                        >
                            <input
                                v-model="readyToShipOnly"
                                type="checkbox"
                                class="border-border size-4 rounded"
                            />
                            Ready to ship only
                        </label>
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
                                    {{ sortedProducts.length }} item(s) matched
                                    your current selection.
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
                            v-if="activeFilters.length > 0"
                            class="mt-3 flex flex-wrap gap-2"
                        >
                            <span
                                v-for="filter in activeFilters"
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

                    <div
                        v-if="loadingPreview"
                        class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
                        role="status"
                        aria-live="polite"
                    >
                        <article
                            v-for="index in 6"
                            :key="`skeleton-${index}`"
                            class="tm-product-card animate-pulse"
                        >
                            <div
                                class="tm-product-media from-zinc-200 via-zinc-100 to-zinc-200 dark:from-zinc-800 dark:via-zinc-700 dark:to-zinc-800"
                            />
                            <div class="mt-4 space-y-2">
                                <div
                                    class="h-4 w-3/5 rounded bg-zinc-200 dark:bg-zinc-700"
                                />
                                <div
                                    class="h-3 w-2/5 rounded bg-zinc-200 dark:bg-zinc-700"
                                />
                                <div
                                    class="h-8 rounded-xl bg-zinc-200 dark:bg-zinc-700"
                                />
                            </div>
                        </article>
                        <span class="sr-only">Loading catalog results</span>
                    </div>

                    <article
                        v-else-if="sortedProducts.length === 0"
                        class="tm-section text-center"
                        aria-live="polite"
                    >
                        <Sparkles class="text-primary mx-auto size-9" />
                        <h2 class="tm-title mt-3">No products found</h2>
                        <p class="tm-body mt-2">
                            Try adjusting category, occasion, or search keywords
                            to discover available styles.
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
                            v-for="product in sortedProducts"
                            :key="product.id"
                            class="tm-product-card"
                        >
                            <div
                                class="mb-3 flex items-center justify-between gap-2"
                            >
                                <span class="tm-chip-strong">{{
                                    product.badge
                                }}</span>
                                <span class="tm-body-sm"
                                    >★ {{ product.rating.toFixed(1) }}</span
                                >
                            </div>
                            <div
                                class="tm-product-media"
                                :class="product.tone"
                            />
                            <h3 class="tm-title mt-4 text-xl">
                                {{ product.name }}
                            </h3>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="tm-chip">{{
                                    product.category
                                }}</span>
                                <span class="tm-chip">{{
                                    product.occasion
                                }}</span>
                            </div>
                            <div class="mt-4 flex items-end gap-2">
                                <p class="tm-title text-primary">
                                    {{ toRinggit(product.priceInSen) }}
                                </p>
                                <p
                                    v-if="product.originalPriceInSen !== null"
                                    class="tm-body-sm line-through"
                                >
                                    {{ toRinggit(product.originalPriceInSen) }}
                                </p>
                            </div>
                            <p class="tm-body-sm mt-1">
                                {{ stockLabelMap[product.stock] }}
                            </p>
                            <Button
                                class="mt-4 w-full"
                                :aria-label="`Add ${product.name} to cart`"
                            >
                                {{
                                    product.stock === 'preorder'
                                        ? 'Pre-order now'
                                        : 'Add to cart'
                                }}
                            </Button>
                        </article>
                    </div>
                </section>
            </div>
        </section>
    </StorefrontLayout>
</template>
