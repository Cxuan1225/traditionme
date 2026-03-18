<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { toRinggit } from '@/composables/useCurrency';
import {
    getProductCategoryLabel,
    productCategoryOptions,
} from '@/constants/productCategories';
import AdminLayout from '@/layouts/admin/Layout.vue';
import { index as productsIndexRoute } from '@/routes/products';
import productsRoutes from '@/routes/products';
import type { BreadcrumbItem } from '@/types';

type ProductResource = {
    id: number;
    name: string;
    slug: string;
    category: string;
    description: string | null;
    price_in_sen: number;
    original_price_in_sen: number | null;
    badge: string | null;
    gradient: string | null;
    image_url: string | null;
    is_active: boolean;
    stock_quantity: number;
    track_stock: boolean;
};

type Capabilities = {
    canViewProducts?: boolean;
    canCreateProducts?: boolean;
    canUpdateProducts?: boolean;
    canDeleteProducts?: boolean;
};

type StatusFilter = 'all' | 'active' | 'inactive' | 'missing-photo';
type DensityMode = 'comfortable' | 'compact';

const props = withDefaults(
    defineProps<{
        initialProducts?: ProductResource[];
        capabilities?: Capabilities;
    }>(),
    {
        initialProducts: () => [],
        capabilities: () => ({}),
    },
);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Product management',
        href: productsIndexRoute(),
    },
];

const loading = ref<boolean>(false);
const submitting = ref<boolean>(false);
const deleting = ref<number | null>(null);
const quickUpdating = ref<number | null>(null);
const searchQuery = ref<string>('');
const statusFilter = ref<StatusFilter>('all');
const categoryFilter = ref<string>('all');
const density = ref<DensityMode>('comfortable');
const selectedProductIds = ref<number[]>([]);
const pageError = ref<string | null>(null);
const pageSuccess = ref<string | null>(null);
const products = ref<ProductResource[]>(props.initialProducts);
const editingId = ref<number | null>(null);
const isEditorOpen = ref<boolean>(false);
const previewImageError = ref<boolean>(false);
const selectedImageFile = ref<File | null>(null);
const selectedImagePreviewUrl = ref<string | null>(null);
const imageInputKey = ref<number>(0);
const isImageDragActive = ref<boolean>(false);

const form = ref<{
    name: string;
    slug: string;
    category: string;
    description: string;
    price_in_sen: string;
    original_price_in_sen: string;
    badge: string;
    gradient: string;
    image_url: string;
    is_active: boolean;
    stock_quantity: string;
    track_stock: boolean;
}>({
    name: '',
    slug: '',
    category: '',
    description: '',
    price_in_sen: '',
    original_price_in_sen: '',
    badge: '',
    gradient: '',
    image_url: '',
    is_active: true,
    stock_quantity: '0',
    track_stock: false,
});

const abilities = computed<Required<Capabilities>>(() => ({
    canViewProducts: props.capabilities.canViewProducts ?? true,
    canCreateProducts: props.capabilities.canCreateProducts ?? true,
    canUpdateProducts: props.capabilities.canUpdateProducts ?? true,
    canDeleteProducts: props.capabilities.canDeleteProducts ?? true,
}));

const visibleProducts = computed<ProductResource[]>(() => {
    const query = searchQuery.value.trim().toLowerCase();

    return products.value.filter((product) => {
        const searchMatch =
            query === '' ||
            [
                product.name,
                product.slug,
                product.category,
                getProductCategoryLabel(product.category),
            ]
                .join(' ')
                .toLowerCase()
                .includes(query);

        const statusMatch =
            statusFilter.value === 'all' ||
            (statusFilter.value === 'active' && product.is_active) ||
            (statusFilter.value === 'inactive' && !product.is_active) ||
            (statusFilter.value === 'missing-photo' &&
                product.image_url === null);

        const categoryMatch =
            categoryFilter.value === 'all' ||
            product.category === categoryFilter.value;

        return searchMatch && statusMatch && categoryMatch;
    });
});

const activeCount = computed<number>(
    () => products.value.filter((item) => item.is_active).length,
);

const inactiveCount = computed<number>(
    () => products.value.filter((item) => !item.is_active).length,
);

const missingPhotoCount = computed<number>(
    () => products.value.filter((item) => item.image_url === null).length,
);

const allVisibleSelected = computed<boolean>(
    () =>
        visibleProducts.value.length > 0 &&
        visibleProducts.value.every((product) =>
            selectedProductIds.value.includes(product.id),
        ),
);

const someVisibleSelected = computed<boolean>(
    () =>
        !allVisibleSelected.value &&
        visibleProducts.value.some((product) =>
            selectedProductIds.value.includes(product.id),
        ),
);

const selectedProducts = computed<ProductResource[]>(() =>
    products.value.filter((product) =>
        selectedProductIds.value.includes(product.id),
    ),
);

const draftImageUrl = computed<string>(() => form.value.image_url.trim());
const draftRemotePreviewUrl = computed<string | null>(() => {
    if (draftImageUrl.value === '') {
        return null;
    }

    return /^https?:\/\/\S+/i.test(draftImageUrl.value) ||
        draftImageUrl.value.startsWith('/')
        ? draftImageUrl.value
        : null;
});
const draftPreviewUrl = computed<string | null>(
    () => selectedImagePreviewUrl.value ?? draftRemotePreviewUrl.value,
);

const draftMediaState = computed<'missing' | 'invalid' | 'valid'>(() => {
    if (selectedImagePreviewUrl.value !== null) {
        return 'valid';
    }

    if (draftImageUrl.value === '') {
        return 'missing';
    }

    return draftRemotePreviewUrl.value === null ? 'invalid' : 'valid';
});

const tableDensityClass = computed<string>(() =>
    density.value === 'compact' ? 'tm-table-compact' : 'tm-table-comfortable',
);

const summarizeDescription = (value: string | null): string => {
    if (value === null || value.trim() === '') {
        return 'No description yet.';
    }

    const normalized = value.replace(/\s+/g, ' ').trim();

    return normalized.length > 120
        ? `${normalized.slice(0, 117)}...`
        : normalized;
};

const formatRinggitInput = (valueInSen: number | null): string =>
    valueInSen === null ? '' : (valueInSen / 100).toFixed(2);

const parseRinggitInput = (
    value: string,
    fieldLabel: string,
    allowEmpty: boolean,
): number | null => {
    const normalized = value.trim().replaceAll(',', '');

    if (normalized === '') {
        if (allowEmpty) {
            return null;
        }

        throw new Error(`${fieldLabel} is required.`);
    }

    if (!/^\d+(?:\.\d{1,2})?$/.test(normalized)) {
        throw new Error(`${fieldLabel} must use RM format like 1.00.`);
    }

    const amountInSen = Math.round(Number.parseFloat(normalized) * 100);

    if (!Number.isInteger(amountInSen) || amountInSen < 1) {
        throw new Error(`${fieldLabel} must be at least RM 0.01.`);
    }

    return amountInSen;
};

const revokeSelectedImagePreview = (): void => {
    if (selectedImagePreviewUrl.value !== null) {
        URL.revokeObjectURL(selectedImagePreviewUrl.value);
        selectedImagePreviewUrl.value = null;
    }
};

const clearSelectedImageFile = (): void => {
    selectedImageFile.value = null;
    isImageDragActive.value = false;
    imageInputKey.value += 1;
    revokeSelectedImagePreview();
};

const setSelectedImageFile = (file: File | null): void => {
    previewImageError.value = false;
    isImageDragActive.value = false;
    revokeSelectedImagePreview();
    selectedImageFile.value = file;

    if (file === null) {
        return;
    }

    selectedImagePreviewUrl.value = URL.createObjectURL(file);
};

const handleImageSelection = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const [file] = input.files ?? [];

    if (!file || !file.type.startsWith('image/')) {
        clearSelectedImageFile();
        return;
    }

    setSelectedImageFile(file);
};

const handleImageDrop = (event: DragEvent): void => {
    const [file] = Array.from(event.dataTransfer?.files ?? []);

    if (!file || !file.type.startsWith('image/')) {
        clearSelectedImageFile();
        return;
    }

    setSelectedImageFile(file);
};

const readCookie = (name: string): string | null => {
    const encodedName = `${encodeURIComponent(name)}=`;
    const cookies = document.cookie.split(';');
    const cookie = cookies.find((part) => part.trim().startsWith(encodedName));

    if (!cookie) {
        return null;
    }

    return decodeURIComponent(cookie.trim().slice(encodedName.length));
};

const requestJson = async <T,>(
    endpoint: string,
    options: {
        method?: 'GET' | 'POST' | 'PUT' | 'DELETE';
        body?: FormData | Record<string, unknown>;
    } = {},
): Promise<T> => {
    const token = readCookie('XSRF-TOKEN');
    let requestBody: BodyInit | undefined;

    if (options.body instanceof FormData) {
        requestBody = options.body;
    } else if (options.body !== undefined) {
        requestBody = JSON.stringify(options.body);
    }

    const response = await fetch(endpoint, {
        method: options.method ?? 'GET',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(token ? { 'X-XSRF-TOKEN': token } : {}),
            ...(options.body instanceof FormData
                ? {}
                : { 'Content-Type': 'application/json' }),
        },
        body: requestBody,
    });

    const payload = (await response.json().catch(() => ({}))) as {
        message?: string;
        errors?: Record<string, string[]>;
        data?: unknown;
    };

    if (!response.ok) {
        const firstError = payload.errors
            ? Object.values(payload.errors)[0]?.[0]
            : null;

        throw new Error(firstError ?? payload.message ?? 'Request failed');
    }

    return payload as T;
};

const parseProduct = (value: unknown): ProductResource | null => {
    if (!value || typeof value !== 'object') {
        return null;
    }

    const source = 'data' in value ? (value as { data?: unknown }).data : value;
    if (!source || typeof source !== 'object') {
        return null;
    }

    const candidate = source as ProductResource;
    if (
        typeof candidate.id !== 'number' ||
        typeof candidate.name !== 'string' ||
        typeof candidate.slug !== 'string' ||
        typeof candidate.category !== 'string' ||
        typeof candidate.price_in_sen !== 'number'
    ) {
        return null;
    }

    return candidate;
};

const parseCollection = (value: unknown): ProductResource[] => {
    const data = Array.isArray(value)
        ? value
        : (value as { data?: unknown } | null)?.data;

    if (!Array.isArray(data)) {
        return [];
    }

    return data
        .map((entry) => parseProduct(entry))
        .filter((entry): entry is ProductResource => entry !== null);
};

const loadProducts = async (): Promise<void> => {
    if (!abilities.value.canViewProducts) {
        return;
    }

    loading.value = true;
    pageError.value = null;

    try {
        const payload = await requestJson<{ data?: unknown }>(
            productsIndexRoute.url(),
        );
        products.value = parseCollection(payload.data ?? []);
        selectedProductIds.value = selectedProductIds.value.filter((id) =>
            products.value.some((product) => product.id === id),
        );
    } catch (error) {
        pageError.value =
            error instanceof Error ? error.message : 'Unable to load products.';
    } finally {
        loading.value = false;
    }
};

const resetForm = (): void => {
    editingId.value = null;
    previewImageError.value = false;
    clearSelectedImageFile();
    form.value = {
        name: '',
        slug: '',
        category: '',
        description: '',
        price_in_sen: '',
        original_price_in_sen: '',
        badge: '',
        gradient: '',
        image_url: '',
        is_active: true,
        stock_quantity: '0',
        track_stock: false,
    };
};

const openCreateEditor = (): void => {
    resetForm();
    isEditorOpen.value = true;
};

const startEdit = (product: ProductResource): void => {
    editingId.value = product.id;
    previewImageError.value = false;
    clearSelectedImageFile();
    isEditorOpen.value = true;
    form.value = {
        name: product.name,
        slug: product.slug,
        category: product.category,
        description: product.description ?? '',
        price_in_sen: formatRinggitInput(product.price_in_sen),
        original_price_in_sen: product.original_price_in_sen
            ? formatRinggitInput(product.original_price_in_sen)
            : '',
        badge: product.badge ?? '',
        gradient: product.gradient ?? '',
        image_url: product.image_url ?? '',
        is_active: product.is_active,
        stock_quantity: product.stock_quantity.toString(),
        track_stock: product.track_stock,
    };
};

const duplicateIntoDraft = (product: ProductResource): void => {
    editingId.value = null;
    previewImageError.value = false;
    clearSelectedImageFile();
    isEditorOpen.value = true;
    form.value = {
        name: `${product.name} copy`,
        slug: `${product.slug}-copy`,
        category: product.category,
        description: product.description ?? '',
        price_in_sen: formatRinggitInput(product.price_in_sen),
        original_price_in_sen: product.original_price_in_sen
            ? formatRinggitInput(product.original_price_in_sen)
            : '',
        badge: product.badge ?? '',
        gradient: product.gradient ?? '',
        image_url: product.image_url ?? '',
        is_active: false,
        stock_quantity: product.stock_quantity.toString(),
        track_stock: product.track_stock,
    };
    pageSuccess.value = 'Draft copied to editor.';
};

const submitForm = async (): Promise<void> => {
    const isEditing = editingId.value !== null;
    if (
        (!isEditing && !abilities.value.canCreateProducts) ||
        (isEditing && !abilities.value.canUpdateProducts)
    ) {
        return;
    }

    submitting.value = true;
    pageError.value = null;
    pageSuccess.value = null;

    try {
        const priceInSen = parseRinggitInput(
            form.value.price_in_sen,
            'Price',
            false,
        );
        const originalPriceInSen = parseRinggitInput(
            form.value.original_price_in_sen,
            'Original price',
            true,
        );

        if (priceInSen === null) {
            throw new Error('Price is required.');
        }

        const payload = {
            ...form.value,
            description:
                form.value.description.trim() === ''
                    ? null
                    : form.value.description.trim(),
            category: form.value.category.trim().toLowerCase(),
            price_in_sen: priceInSen,
            original_price_in_sen: originalPriceInSen,
            image_url:
                form.value.image_url.trim() === ''
                    ? null
                    : form.value.image_url.trim(),
            stock_quantity: parseInt(form.value.stock_quantity, 10) || 0,
            track_stock: form.value.track_stock,
        };

        const multipartPayload =
            selectedImageFile.value === null
                ? null
                : (() => {
                      const data = new FormData();
                      data.set('name', form.value.name);
                      data.set('slug', form.value.slug);
                      data.set('category', payload.category);
                      data.set('description', form.value.description.trim());
                      data.set('price_in_sen', payload.price_in_sen.toString());
                      data.set(
                          'original_price_in_sen',
                          payload.original_price_in_sen?.toString() ?? '',
                      );
                      data.set('badge', form.value.badge);
                      data.set('gradient', form.value.gradient);
                      data.set('image_url', form.value.image_url.trim());
                      data.set('image', selectedImageFile.value);
                      data.set('is_active', payload.is_active ? '1' : '0');
                      data.set(
                          'stock_quantity',
                          payload.stock_quantity.toString(),
                      );
                      data.set('track_stock', payload.track_stock ? '1' : '0');

                      return data;
                  })();

        if (isEditing && editingId.value !== null) {
            const response = await requestJson(
                productsRoutes.update.url({ product: editingId.value }),
                {
                    method: multipartPayload ? 'POST' : 'PUT',
                    body:
                        multipartPayload === null
                            ? payload
                            : (() => {
                                  multipartPayload.set('_method', 'PUT');

                                  return multipartPayload;
                              })(),
                },
            );

            const updated = parseProduct(response);
            if (updated) {
                products.value = products.value.map((product) =>
                    product.id === updated.id ? updated : product,
                );
            }

            pageSuccess.value = 'Product updated successfully.';
        } else {
            const response = await requestJson(productsRoutes.store.url(), {
                method: 'POST',
                body: multipartPayload ?? payload,
            });

            const created = parseProduct(response);
            if (created) {
                products.value = [created, ...products.value];
            }

            pageSuccess.value = 'Product created successfully.';
        }

        isEditorOpen.value = false;
        resetForm();
    } catch (error) {
        pageError.value =
            error instanceof Error ? error.message : 'Unable to save product.';
    } finally {
        submitting.value = false;
    }
};

const toggleProductActive = async (product: ProductResource): Promise<void> => {
    if (!abilities.value.canUpdateProducts) {
        return;
    }

    quickUpdating.value = product.id;
    pageError.value = null;
    pageSuccess.value = null;

    try {
        const response = await requestJson(
            productsRoutes.update.url({ product: product.id }),
            {
                method: 'PUT',
                body: {
                    name: product.name,
                    slug: product.slug,
                    category: product.category,
                    description: product.description,
                    price_in_sen: product.price_in_sen,
                    original_price_in_sen: product.original_price_in_sen,
                    badge: product.badge ?? '',
                    gradient: product.gradient ?? '',
                    image_url: product.image_url,
                    is_active: !product.is_active,
                    stock_quantity: product.stock_quantity,
                    track_stock: product.track_stock,
                },
            },
        );

        const updated = parseProduct(response);
        if (updated) {
            products.value = products.value.map((entry) =>
                entry.id === updated.id ? updated : entry,
            );
        }

        pageSuccess.value = `Product marked ${
            product.is_active ? 'inactive' : 'active'
        }.`;
    } catch (error) {
        pageError.value =
            error instanceof Error
                ? error.message
                : 'Unable to update product status.';
    } finally {
        quickUpdating.value = null;
    }
};

const removeProduct = async (productId: number): Promise<void> => {
    if (!abilities.value.canDeleteProducts) {
        return;
    }

    deleting.value = productId;
    pageError.value = null;
    pageSuccess.value = null;

    try {
        await requestJson(productsRoutes.destroy.url({ product: productId }), {
            method: 'DELETE',
        });

        products.value = products.value.filter(
            (product) => product.id !== productId,
        );
        selectedProductIds.value = selectedProductIds.value.filter(
            (id) => id !== productId,
        );
        if (editingId.value === productId) {
            resetForm();
        }
        pageSuccess.value = 'Product deleted.';
    } catch (error) {
        pageError.value =
            error instanceof Error
                ? error.message
                : 'Unable to delete product.';
    } finally {
        deleting.value = null;
    }
};

const setDensity = (mode: DensityMode): void => {
    density.value = mode;
};

const setStatusFilter = (status: StatusFilter): void => {
    statusFilter.value = status;
};

const toggleRowSelection = (productId: number, checked: boolean): void => {
    if (checked) {
        if (!selectedProductIds.value.includes(productId)) {
            selectedProductIds.value = [...selectedProductIds.value, productId];
        }

        return;
    }

    selectedProductIds.value = selectedProductIds.value.filter(
        (id) => id !== productId,
    );
};

const toggleAllVisibleSelection = (checked: boolean): void => {
    const visibleIds = visibleProducts.value.map((product) => product.id);
    if (checked) {
        selectedProductIds.value = Array.from(
            new Set([...selectedProductIds.value, ...visibleIds]),
        );

        return;
    }

    selectedProductIds.value = selectedProductIds.value.filter(
        (id) => !visibleIds.includes(id),
    );
};

watch(
    () => form.value.image_url,
    () => {
        previewImageError.value = false;
    },
);

onBeforeUnmount(() => {
    revokeSelectedImagePreview();
});

onMounted(async () => {
    if (products.value.length === 0) {
        await loadProducts();
    }
});
</script>

<template>
    <Head title="Product management" />

    <AdminLayout :breadcrumbs="breadcrumbItems">
        <div class="space-y-4">
            <section class="tm-shell p-6">
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                >
                    <div>
                        <p class="tm-kicker text-primary">Admin Catalog</p>
                        <h2 class="mt-2 tm-display text-3xl font-black">
                            Media-first product catalog
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
                            Filter faster, review product imagery quality, and
                            update catalog metadata without leaving this view.
                        </p>
                    </div>
                    <div class="grid gap-2 sm:grid-cols-3">
                        <div class="tm-stat">
                            <p class="text-xs text-muted-foreground">
                                Total products
                            </p>
                            <p class="text-2xl font-black">
                                {{ products.length }}
                            </p>
                        </div>
                        <div class="tm-stat">
                            <p class="text-xs text-muted-foreground">Active</p>
                            <p class="text-2xl font-black">{{ activeCount }}</p>
                        </div>
                        <div class="tm-stat tm-stat-warning">
                            <p class="text-xs text-muted-foreground">
                                Missing photo
                            </p>
                            <p class="text-2xl font-black">
                                {{ missingPhotoCount }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <Alert v-if="pageError" variant="destructive">
                <AlertTitle>Request failed</AlertTitle>
                <AlertDescription>{{ pageError }}</AlertDescription>
            </Alert>

            <Alert v-if="pageSuccess">
                <AlertTitle>Saved</AlertTitle>
                <AlertDescription>{{ pageSuccess }}</AlertDescription>
            </Alert>

            <section class="tm-admin-toolbar space-y-3">
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between"
                    role="region"
                    aria-label="Catalog actions toolbar"
                >
                    <div>
                        <p class="text-sm font-semibold text-foreground">
                            Catalog workflow tools
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Search by name, control density, and focus on
                            missing media or inactive inventory.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Input
                            v-model="searchQuery"
                            class="tm-input-surface w-full min-w-52 lg:w-72"
                            placeholder="Search name, slug, category..."
                        />
                        <Button
                            variant="outline"
                            :disabled="loading || !abilities.canViewProducts"
                            @click="loadProducts"
                        >
                            <Spinner v-if="loading" />
                            Refresh
                        </Button>
                        <Button variant="outline" @click="openCreateEditor">
                            New product
                        </Button>
                    </div>
                </div>

                <div class="tm-filter-row">
                    <div
                        class="tm-chip-row"
                        role="tablist"
                        aria-label="Status filter"
                    >
                        <button
                            type="button"
                            class="tm-filter-chip"
                            :class="{
                                'tm-filter-chip-active': statusFilter === 'all',
                            }"
                            :aria-pressed="statusFilter === 'all'"
                            @click="setStatusFilter('all')"
                        >
                            All <span>{{ products.length }}</span>
                        </button>
                        <button
                            type="button"
                            class="tm-filter-chip"
                            :class="{
                                'tm-filter-chip-active':
                                    statusFilter === 'active',
                            }"
                            :aria-pressed="statusFilter === 'active'"
                            @click="setStatusFilter('active')"
                        >
                            Active <span>{{ activeCount }}</span>
                        </button>
                        <button
                            type="button"
                            class="tm-filter-chip"
                            :class="{
                                'tm-filter-chip-active':
                                    statusFilter === 'inactive',
                            }"
                            :aria-pressed="statusFilter === 'inactive'"
                            @click="setStatusFilter('inactive')"
                        >
                            Inactive <span>{{ inactiveCount }}</span>
                        </button>
                        <button
                            type="button"
                            class="tm-filter-chip tm-filter-chip-warning"
                            :class="{
                                'tm-filter-chip-active':
                                    statusFilter === 'missing-photo',
                            }"
                            :aria-pressed="statusFilter === 'missing-photo'"
                            @click="setStatusFilter('missing-photo')"
                        >
                            Missing photo <span>{{ missingPhotoCount }}</span>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <label class="tm-label text-xs" for="catalog-category">
                            Category
                        </label>
                        <select
                            id="catalog-category"
                            v-model="categoryFilter"
                            class="tm-select-surface"
                        >
                            <option value="all">All categories</option>
                            <option
                                v-for="category in productCategoryOptions"
                                :key="category.value"
                                :value="category.value"
                            >
                                {{ category.label }}
                            </option>
                        </select>
                        <div
                            class="tm-density-switch"
                            role="group"
                            aria-label="Table density"
                        >
                            <button
                                type="button"
                                class="tm-density-button"
                                :class="{
                                    'tm-density-button-active':
                                        density === 'comfortable',
                                }"
                                :aria-pressed="density === 'comfortable'"
                                @click="setDensity('comfortable')"
                            >
                                Comfortable
                            </button>
                            <button
                                type="button"
                                class="tm-density-button"
                                :class="{
                                    'tm-density-button-active':
                                        density === 'compact',
                                }"
                                :aria-pressed="density === 'compact'"
                                @click="setDensity('compact')"
                            >
                                Compact
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid gap-4">
                <Card class="tm-panel h-full">
                    <CardHeader>
                        <CardTitle>Catalog list</CardTitle>
                        <CardDescription>
                            Image-first product rows with quick operational
                            actions.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-if="selectedProducts.length > 0"
                            class="tm-state-note tm-state-note-warning flex flex-wrap items-center justify-between gap-2"
                        >
                            <span class="text-sm">
                                {{ selectedProducts.length }} selected for bulk
                                review staging.
                            </span>
                            <Button
                                size="sm"
                                variant="outline"
                                @click="selectedProductIds = []"
                            >
                                Clear selection
                            </Button>
                        </div>

                        <div
                            v-if="loading"
                            class="tm-state-note tm-state-note-warning flex items-center gap-2"
                            role="status"
                            aria-live="polite"
                        >
                            <Spinner />
                            Loading products...
                        </div>

                        <p
                            v-else-if="!abilities.canViewProducts"
                            class="text-sm text-muted-foreground"
                        >
                            You do not have access to view products.
                        </p>

                        <p
                            v-else-if="products.length === 0"
                            class="tm-empty-state"
                        >
                            No products available yet.
                        </p>

                        <p
                            v-else-if="visibleProducts.length === 0"
                            class="tm-empty-state"
                        >
                            No products match your filter combination.
                        </p>

                        <div v-else class="tm-table-wrap tm-table-roomy">
                            <table
                                :class="[
                                    'tm-table tm-table-product',
                                    tableDensityClass,
                                ]"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col" class="tm-th w-12">
                                            <input
                                                type="checkbox"
                                                class="tm-table-checkbox"
                                                :checked="allVisibleSelected"
                                                :aria-checked="
                                                    someVisibleSelected
                                                        ? 'mixed'
                                                        : allVisibleSelected
                                                          ? 'true'
                                                          : 'false'
                                                "
                                                :aria-label="
                                                    allVisibleSelected
                                                        ? 'Unselect visible products'
                                                        : 'Select visible products'
                                                "
                                                @change="
                                                    toggleAllVisibleSelection(
                                                        (
                                                            $event.target as HTMLInputElement
                                                        ).checked,
                                                    )
                                                "
                                            />
                                        </th>
                                        <th scope="col" class="tm-th">
                                            Product
                                        </th>
                                        <th scope="col" class="tm-th">
                                            Status
                                        </th>
                                        <th scope="col" class="tm-th">
                                            Category
                                        </th>
                                        <th scope="col" class="tm-th">
                                            Description
                                        </th>
                                        <th scope="col" class="tm-th">Stock</th>
                                        <th scope="col" class="tm-th">
                                            Price (sen)
                                        </th>
                                        <th
                                            scope="col"
                                            class="tm-th text-right"
                                        >
                                            Row actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="product in visibleProducts"
                                        :key="product.id"
                                        class="tm-tr"
                                    >
                                        <td class="tm-td">
                                            <input
                                                type="checkbox"
                                                class="tm-table-checkbox"
                                                :checked="
                                                    selectedProductIds.includes(
                                                        product.id,
                                                    )
                                                "
                                                :aria-label="`Select ${product.name}`"
                                                @change="
                                                    toggleRowSelection(
                                                        product.id,
                                                        (
                                                            $event.target as HTMLInputElement
                                                        ).checked,
                                                    )
                                                "
                                            />
                                        </td>
                                        <td class="tm-td">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <div
                                                    class="tm-media-thumb"
                                                    :class="
                                                        product.image_url
                                                            ? ''
                                                            : (product.gradient ??
                                                              'from-zinc-200 to-zinc-100')
                                                    "
                                                >
                                                    <img
                                                        v-if="product.image_url"
                                                        :src="product.image_url"
                                                        :alt="product.name"
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <span
                                                        v-else
                                                        class="tm-media-fallback"
                                                    >
                                                        No photo
                                                    </span>
                                                </div>
                                                <div class="space-y-1">
                                                    <p class="font-semibold">
                                                        {{ product.name }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-muted-foreground"
                                                    >
                                                        {{ product.slug }}
                                                    </p>
                                                    <div
                                                        class="flex flex-wrap items-center gap-1"
                                                    >
                                                        <Badge
                                                            v-if="product.badge"
                                                            variant="secondary"
                                                        >
                                                            {{ product.badge }}
                                                        </Badge>
                                                        <span
                                                            v-if="
                                                                !product.image_url
                                                            "
                                                            class="tm-warning-chip"
                                                        >
                                                            Missing photo
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="tm-td">
                                            <Badge
                                                :variant="
                                                    product.is_active
                                                        ? 'default'
                                                        : 'secondary'
                                                "
                                            >
                                                {{
                                                    product.is_active
                                                        ? 'Active'
                                                        : 'Inactive'
                                                }}
                                            </Badge>
                                        </td>
                                        <td class="tm-td">
                                            {{
                                                getProductCategoryLabel(
                                                    product.category,
                                                )
                                            }}
                                        </td>
                                        <td class="tm-td max-w-xs">
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                {{
                                                    summarizeDescription(
                                                        product.description,
                                                    )
                                                }}
                                            </p>
                                        </td>
                                        <td class="tm-td">
                                            <span
                                                v-if="!product.track_stock"
                                                class="text-muted-foreground"
                                                title="Stock not tracked"
                                            >
                                                &mdash;
                                            </span>
                                            <span
                                                v-else-if="
                                                    product.stock_quantity > 0
                                                "
                                                class="text-green-600"
                                            >
                                                {{ product.stock_quantity }}
                                            </span>
                                            <span v-else class="text-red-600">
                                                0
                                            </span>
                                        </td>
                                        <td class="tm-td">
                                            {{
                                                toRinggit(product.price_in_sen)
                                            }}
                                        </td>
                                        <td class="tm-td text-right">
                                            <div
                                                class="flex flex-wrap justify-end gap-2"
                                            >
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    :aria-label="`Edit ${product.name}`"
                                                    :disabled="
                                                        !abilities.canUpdateProducts
                                                    "
                                                    @click="startEdit(product)"
                                                >
                                                    Edit
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    :aria-label="`Duplicate ${product.name}`"
                                                    :disabled="
                                                        !abilities.canCreateProducts
                                                    "
                                                    @click="
                                                        duplicateIntoDraft(
                                                            product,
                                                        )
                                                    "
                                                >
                                                    Duplicate
                                                </Button>
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    :disabled="
                                                        !abilities.canUpdateProducts ||
                                                        quickUpdating ===
                                                            product.id
                                                    "
                                                    @click="
                                                        toggleProductActive(
                                                            product,
                                                        )
                                                    "
                                                >
                                                    <Spinner
                                                        v-if="
                                                            quickUpdating ===
                                                            product.id
                                                        "
                                                    />
                                                    {{
                                                        product.is_active
                                                            ? 'Deactivate'
                                                            : 'Activate'
                                                    }}
                                                </Button>
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    :aria-label="`Delete ${product.name}`"
                                                    :disabled="
                                                        !abilities.canDeleteProducts ||
                                                        deleting === product.id
                                                    "
                                                    @click="
                                                        removeProduct(
                                                            product.id,
                                                        )
                                                    "
                                                >
                                                    <Spinner
                                                        v-if="
                                                            deleting ===
                                                            product.id
                                                        "
                                                    />
                                                    Delete
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                    <CardFooter class="tm-sticky-actions">
                        <Button
                            variant="outline"
                            :disabled="loading || !abilities.canViewProducts"
                            @click="loadProducts"
                        >
                            Refresh
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <Dialog :open="isEditorOpen" @update:open="isEditorOpen = $event">
                <DialogContent
                    class="max-h-[92vh] overflow-y-auto sm:max-w-4xl"
                >
                    <DialogHeader>
                        <DialogTitle>{{
                            editingId ? 'Edit product' : 'Create product'
                        }}</DialogTitle>
                        <DialogDescription>
                            Keep product identity, pricing, and media fields
                            aligned before publish.
                        </DialogDescription>
                    </DialogHeader>
                    <div class="space-y-3">
                        <section class="tm-card p-4">
                            <p class="tm-subtitle">Identity</p>
                            <p class="tm-form-hint">
                                Product naming and grouping details used in
                                listing and search.
                            </p>
                            <div class="mt-3 space-y-3">
                                <div class="tm-form-field">
                                    <Label for="name" class="tm-label"
                                        >Name</Label
                                    >
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        class="tm-input-surface"
                                    />
                                </div>
                                <div class="tm-form-field">
                                    <Label for="slug" class="tm-label"
                                        >Slug</Label
                                    >
                                    <Input
                                        id="slug"
                                        v-model="form.slug"
                                        class="tm-input-surface"
                                        placeholder="songket-luxe-kurung-set"
                                    />
                                </div>
                                <div class="tm-form-field">
                                    <Label for="category" class="tm-label"
                                        >Category</Label
                                    >
                                    <select
                                        id="category"
                                        v-model="form.category"
                                        class="tm-select-surface"
                                    >
                                        <option value="">
                                            Select product category
                                        </option>
                                        <option
                                            v-for="category in productCategoryOptions"
                                            :key="category.value"
                                            :value="category.value"
                                        >
                                            {{ category.label }}
                                        </option>
                                    </select>
                                </div>
                                <div class="tm-form-field">
                                    <Label for="description" class="tm-label"
                                        >Description</Label
                                    >
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        class="tm-input-surface min-h-32 resize-y"
                                        placeholder="Add product story, fabric details, sizing notes, or merchandising copy."
                                    />
                                </div>
                            </div>
                        </section>
                        <section class="tm-card p-4">
                            <p class="tm-subtitle">Commercial details</p>
                            <p class="tm-form-hint">
                                Enter ringgit values in RM format like
                                <code>1.00</code> and add optional badge labels
                                for merchandising highlights.
                            </p>
                            <div class="mt-3 space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="tm-form-field">
                                        <Label for="price" class="tm-label"
                                            >Price (RM)</Label
                                        >
                                        <Input
                                            id="price"
                                            v-model="form.price_in_sen"
                                            class="tm-input-surface"
                                            inputmode="decimal"
                                            placeholder="1.00"
                                        />
                                    </div>
                                    <div class="tm-form-field">
                                        <Label
                                            for="original-price"
                                            class="tm-label"
                                            >Original price (RM)</Label
                                        >
                                        <Input
                                            id="original-price"
                                            v-model="form.original_price_in_sen"
                                            class="tm-input-surface"
                                            inputmode="decimal"
                                            placeholder="1.00"
                                        />
                                    </div>
                                </div>
                                <div class="tm-form-field">
                                    <Label for="badge" class="tm-label"
                                        >Badge</Label
                                    >
                                    <Input
                                        id="badge"
                                        v-model="form.badge"
                                        class="tm-input-surface"
                                        placeholder="Best seller"
                                    />
                                </div>
                            </div>
                        </section>
                        <section class="tm-card p-4">
                            <p class="tm-subtitle">Media and publish</p>
                            <p class="tm-form-hint">
                                Upload a catalog image for local storage, or
                                keep using <code>image_url</code> for remote
                                media. Gradient classes remain the fallback for
                                missing images.
                            </p>
                            <div
                                class="mt-3 grid gap-3 xl:grid-cols-[1.1fr_0.9fr]"
                            >
                                <div class="space-y-3">
                                    <div class="tm-form-field">
                                        <Label class="tm-label"
                                            >Product image upload</Label
                                        >
                                        <label
                                            class="flex cursor-pointer flex-col gap-3 rounded-[1.75rem] border border-dashed border-[color:rgba(140,74,44,0.24)] bg-[linear-gradient(145deg,rgba(255,250,245,0.96),rgba(255,244,234,0.82))] p-4 transition hover:border-[color:rgba(140,74,44,0.42)]"
                                            :class="{
                                                'border-[color:rgba(140,74,44,0.5)] bg-[linear-gradient(145deg,rgba(255,244,234,0.96),rgba(255,236,220,0.88))]':
                                                    isImageDragActive,
                                            }"
                                            @dragenter.prevent="
                                                isImageDragActive = true
                                            "
                                            @dragover.prevent="
                                                isImageDragActive = true
                                            "
                                            @dragleave.prevent="
                                                isImageDragActive = false
                                            "
                                            @drop.prevent="handleImageDrop"
                                        >
                                            <input
                                                :key="imageInputKey"
                                                type="file"
                                                accept="image/*"
                                                class="sr-only"
                                                @change="handleImageSelection"
                                            />
                                            <div
                                                class="flex items-start justify-between gap-3"
                                            >
                                                <div class="space-y-1">
                                                    <p
                                                        class="text-sm font-semibold text-zinc-900"
                                                    >
                                                        Drop an image here or
                                                        browse from device
                                                    </p>
                                                    <p
                                                        class="text-xs leading-5 text-zinc-600"
                                                    >
                                                        JPG, PNG, or WebP up to
                                                        4 MB. New uploads take
                                                        priority over the remote
                                                        URL field.
                                                    </p>
                                                </div>
                                                <span
                                                    class="rounded-full border border-[color:rgba(140,74,44,0.2)] bg-white/90 px-3 py-1 text-xs font-semibold text-zinc-700"
                                                >
                                                    Browse
                                                </span>
                                            </div>
                                            <div
                                                v-if="selectedImageFile"
                                                class="flex items-center justify-between gap-3 rounded-2xl bg-white/80 px-3 py-2"
                                            >
                                                <div class="min-w-0">
                                                    <p
                                                        class="truncate text-sm font-medium text-zinc-900"
                                                    >
                                                        {{
                                                            selectedImageFile.name
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-zinc-500"
                                                    >
                                                        {{
                                                            (
                                                                selectedImageFile.size /
                                                                1024 /
                                                                1024
                                                            ).toFixed(2)
                                                        }}
                                                        MB selected
                                                    </p>
                                                </div>
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="outline"
                                                    @click.prevent="
                                                        clearSelectedImageFile()
                                                    "
                                                >
                                                    Remove
                                                </Button>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="tm-form-field">
                                        <Label for="image-url" class="tm-label"
                                            >Remote photo URL</Label
                                        >
                                        <Input
                                            id="image-url"
                                            v-model="form.image_url"
                                            class="tm-input-surface"
                                            placeholder="https://images.example.com/products/songket.jpg"
                                        />
                                    </div>
                                    <div class="tm-form-field">
                                        <Label for="gradient" class="tm-label"
                                            >Fallback gradient classes</Label
                                        >
                                        <Input
                                            id="gradient"
                                            v-model="form.gradient"
                                            class="tm-input-surface"
                                            placeholder="from-rose-100 via-orange-50 to-amber-100"
                                        />
                                    </div>
                                    <Label
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <input
                                            v-model="form.track_stock"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-zinc-300"
                                        />
                                        Track stock
                                    </Label>
                                    <div
                                        v-if="form.track_stock"
                                        class="tm-form-field"
                                    >
                                        <Label
                                            for="stock_quantity"
                                            class="tm-label"
                                            >Stock quantity</Label
                                        >
                                        <Input
                                            id="stock_quantity"
                                            v-model="form.stock_quantity"
                                            type="number"
                                            min="0"
                                            class="tm-input-surface"
                                            placeholder="0"
                                        />
                                    </div>
                                    <Label
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <input
                                            v-model="form.is_active"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-zinc-300"
                                        />
                                        Product is active
                                    </Label>
                                </div>
                                <div class="tm-media-preview-panel">
                                    <div
                                        class="tm-media-preview"
                                        :class="
                                            draftPreviewUrl
                                                ? ''
                                                : form.gradient.trim() ||
                                                  'from-zinc-200 to-zinc-100'
                                        "
                                    >
                                        <img
                                            v-if="
                                                draftPreviewUrl &&
                                                !previewImageError
                                            "
                                            :src="draftPreviewUrl"
                                            alt="Draft product media preview"
                                            class="h-full w-full object-cover"
                                            @error="previewImageError = true"
                                        />
                                        <div
                                            v-else
                                            class="tm-media-fallback px-3 text-center"
                                        >
                                            {{
                                                draftMediaState === 'missing'
                                                    ? 'No product photo yet'
                                                    : 'URL could not be previewed'
                                            }}
                                        </div>
                                    </div>
                                    <p
                                        v-if="draftMediaState === 'missing'"
                                        class="tm-state-note tm-state-note-warning"
                                    >
                                        Missing photo warning: this product will
                                        render with gradient fallback in
                                        listings until an upload or URL is
                                        supplied.
                                    </p>
                                    <p
                                        v-else-if="
                                            draftMediaState === 'invalid'
                                        "
                                        class="tm-state-note tm-state-note-danger"
                                    >
                                        Photo URL should start with
                                        <code>http://</code> or
                                        <code>https://</code>.
                                    </p>
                                    <p
                                        v-else
                                        class="tm-state-note tm-state-note-success"
                                    >
                                        {{
                                            selectedImageFile
                                                ? 'New upload preview ready. Saving will replace the current catalog image.'
                                                : 'Photo preview loaded. Product cards will prioritize this media.'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </section>
                    </div>
                    <DialogFooter class="tm-sticky-actions">
                        <Button
                            :disabled="
                                submitting ||
                                (!editingId && !abilities.canCreateProducts) ||
                                (editingId !== null &&
                                    !abilities.canUpdateProducts)
                            "
                            @click="submitForm"
                        >
                            <Spinner v-if="submitting" />
                            {{
                                editingId ? 'Update product' : 'Create product'
                            }}
                        </Button>
                        <Button
                            variant="outline"
                            :disabled="submitting"
                            @click="resetForm"
                        >
                            Clear
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AdminLayout>
</template>
