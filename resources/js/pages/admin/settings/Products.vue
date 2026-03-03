<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AdminLayout from '@/layouts/admin/Layout.vue';
import { index as productsIndexRoute } from '@/routes/products';
import productsRoutes from '@/routes/products';
import type { BreadcrumbItem } from '@/types';

type ProductResource = {
    id: number;
    name: string;
    slug: string;
    category: string;
    price_in_sen: number;
    original_price_in_sen: number | null;
    badge: string | null;
    gradient: string | null;
    is_active: boolean;
};

type Capabilities = {
    canViewProducts?: boolean;
    canCreateProducts?: boolean;
    canUpdateProducts?: boolean;
    canDeleteProducts?: boolean;
};

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
const searchQuery = ref<string>('');
const pageError = ref<string | null>(null);
const pageSuccess = ref<string | null>(null);
const products = ref<ProductResource[]>(props.initialProducts);
const editingId = ref<number | null>(null);

const form = ref<{
    name: string;
    slug: string;
    category: string;
    price_in_sen: string;
    original_price_in_sen: string;
    badge: string;
    gradient: string;
    is_active: boolean;
}>({
    name: '',
    slug: '',
    category: '',
    price_in_sen: '',
    original_price_in_sen: '',
    badge: '',
    gradient: '',
    is_active: true,
});

const abilities = computed<Required<Capabilities>>(() => ({
    canViewProducts: props.capabilities.canViewProducts ?? true,
    canCreateProducts: props.capabilities.canCreateProducts ?? true,
    canUpdateProducts: props.capabilities.canUpdateProducts ?? true,
    canDeleteProducts: props.capabilities.canDeleteProducts ?? true,
}));

const visibleProducts = computed<ProductResource[]>(() => {
    const query = searchQuery.value.trim().toLowerCase();
    if (query === '') {
        return products.value;
    }

    return products.value.filter((product) =>
        [product.name, product.slug, product.category]
            .join(' ')
            .toLowerCase()
            .includes(query),
    );
});

const activeCount = computed<number>(
    () => products.value.filter((item) => item.is_active).length,
);

const inactiveCount = computed<number>(
    () => products.value.filter((item) => !item.is_active).length,
);

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
        body?: Record<string, unknown>;
    } = {},
): Promise<T> => {
    const token = readCookie('XSRF-TOKEN');
    const response = await fetch(endpoint, {
        method: options.method ?? 'GET',
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(token ? { 'X-XSRF-TOKEN': token } : {}),
        },
        body: options.body ? JSON.stringify(options.body) : undefined,
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
    } catch (error) {
        pageError.value =
            error instanceof Error ? error.message : 'Unable to load products.';
    } finally {
        loading.value = false;
    }
};

const resetForm = (): void => {
    editingId.value = null;
    form.value = {
        name: '',
        slug: '',
        category: '',
        price_in_sen: '',
        original_price_in_sen: '',
        badge: '',
        gradient: '',
        is_active: true,
    };
};

const startEdit = (product: ProductResource): void => {
    editingId.value = product.id;
    form.value = {
        name: product.name,
        slug: product.slug,
        category: product.category,
        price_in_sen: product.price_in_sen.toString(),
        original_price_in_sen: product.original_price_in_sen
            ? product.original_price_in_sen.toString()
            : '',
        badge: product.badge ?? '',
        gradient: product.gradient ?? '',
        is_active: product.is_active,
    };
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
        const payload = {
            ...form.value,
            price_in_sen: Number.parseInt(form.value.price_in_sen, 10),
            original_price_in_sen:
                form.value.original_price_in_sen.trim() === ''
                    ? null
                    : Number.parseInt(form.value.original_price_in_sen, 10),
        };

        if (isEditing && editingId.value !== null) {
            const response = await requestJson(
                productsRoutes.update.url({ product: editingId.value }),
                {
                    method: 'PUT',
                    body: payload,
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
                body: payload,
            });

            const created = parseProduct(response);
            if (created) {
                products.value = [created, ...products.value];
            }

            pageSuccess.value = 'Product created successfully.';
        }

        resetForm();
    } catch (error) {
        pageError.value =
            error instanceof Error ? error.message : 'Unable to save product.';
    } finally {
        submitting.value = false;
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
            <section
                class="border-border via-card dark:via-card rounded-3xl border bg-gradient-to-r from-amber-100/70 to-rose-100/50 p-6 dark:from-amber-950/35 dark:to-rose-950/20"
            >
                <p
                    class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300"
                >
                    Admin Catalog
                </p>
                <h2 class="text-foreground mt-2 text-3xl font-black">
                    Product Control Center
                </h2>
                <p class="text-muted-foreground mt-2 max-w-2xl text-sm">
                    Maintain pricing, visibility, and merchandising fields for
                    storefront publishing.
                </p>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="tm-stat">
                        <p class="text-muted-foreground text-xs">
                            Total products
                        </p>
                        <p class="text-2xl font-black">{{ products.length }}</p>
                    </div>
                    <div class="tm-stat">
                        <p class="text-muted-foreground text-xs">Active</p>
                        <p class="text-2xl font-black">{{ activeCount }}</p>
                    </div>
                    <div class="tm-stat">
                        <p class="text-muted-foreground text-xs">Inactive</p>
                        <p class="text-2xl font-black">{{ inactiveCount }}</p>
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

            <section class="tm-admin-toolbar">
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <p class="text-foreground text-sm font-semibold">
                            Catalog actions
                        </p>
                        <p class="text-muted-foreground text-xs">
                            Use search and quick actions to maintain products
                            faster.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Input
                            v-model="searchQuery"
                            class="tm-input-surface w-full min-w-52 lg:w-64"
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
                        <Button variant="outline" @click="resetForm">
                            New product
                        </Button>
                    </div>
                </div>
            </section>

            <div class="grid gap-4 lg:grid-cols-[1.25fr_0.75fr]">
                <Card class="tm-panel h-full">
                    <CardHeader>
                        <CardTitle>Catalog List</CardTitle>
                        <CardDescription>
                            Manage products shown on storefront pages.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-if="loading"
                            class="text-muted-foreground flex items-center gap-2 text-sm"
                        >
                            <Spinner />
                            Loading products...
                        </div>

                        <p
                            v-else-if="!abilities.canViewProducts"
                            class="text-muted-foreground text-sm"
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
                            No products match your search.
                        </p>

                        <div v-else class="tm-table-wrap">
                            <table class="tm-table">
                                <thead>
                                    <tr>
                                        <th class="tm-th">Product</th>
                                        <th class="tm-th">Category</th>
                                        <th class="tm-th">Price (sen)</th>
                                        <th class="tm-th">Status</th>
                                        <th class="tm-th text-right">
                                            Actions
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
                                            <p
                                                class="text-foreground font-semibold"
                                            >
                                                {{ product.name }}
                                            </p>
                                            <p
                                                class="text-muted-foreground text-xs"
                                            >
                                                {{ product.slug }}
                                            </p>
                                        </td>
                                        <td class="tm-td">
                                            {{ product.category }}
                                        </td>
                                        <td class="tm-td">
                                            {{ product.price_in_sen }}
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
                                        <td class="tm-td text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    :disabled="
                                                        !abilities.canUpdateProducts
                                                    "
                                                    @click="startEdit(product)"
                                                >
                                                    Edit
                                                </Button>
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
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

                <Card class="tm-panel h-full">
                    <CardHeader>
                        <CardTitle>{{
                            editingId ? 'Edit Product' : 'New Product'
                        }}</CardTitle>
                        <CardDescription>
                            Configure catalog fields and publish state.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
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
                                    <Input
                                        id="category"
                                        v-model="form.category"
                                        class="tm-input-surface"
                                    />
                                </div>
                            </div>
                        </section>

                        <section class="tm-card p-4">
                            <p class="tm-subtitle">Commercial details</p>
                            <p class="tm-form-hint">
                                Keep price values in sen and optional
                                merchandising badge info.
                            </p>
                            <div class="mt-3 space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="tm-form-field">
                                        <Label for="price" class="tm-label"
                                            >Price (sen)</Label
                                        >
                                        <Input
                                            id="price"
                                            v-model="form.price_in_sen"
                                            class="tm-input-surface"
                                            inputmode="numeric"
                                        />
                                    </div>
                                    <div class="tm-form-field">
                                        <Label
                                            for="original-price"
                                            class="tm-label"
                                            >Original price (sen)</Label
                                        >
                                        <Input
                                            id="original-price"
                                            v-model="form.original_price_in_sen"
                                            class="tm-input-surface"
                                            inputmode="numeric"
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
                                        placeholder="Best Seller"
                                    />
                                </div>
                            </div>
                        </section>

                        <section class="tm-card p-4">
                            <p class="tm-subtitle">Publish settings</p>
                            <p class="tm-form-hint">
                                Use gradient classes for storefront card media
                                styling.
                            </p>
                            <div class="mt-3 space-y-3">
                                <div class="tm-form-field">
                                    <Label for="gradient" class="tm-label"
                                        >Gradient classes</Label
                                    >
                                    <Input
                                        id="gradient"
                                        v-model="form.gradient"
                                        class="tm-input-surface"
                                        placeholder="from-rose-100 via-orange-50 to-amber-100"
                                    />
                                </div>

                                <Label class="flex items-center gap-2 text-sm">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-zinc-300"
                                    />
                                    Product is active
                                </Label>
                            </div>
                        </section>
                    </CardContent>
                    <CardFooter class="tm-sticky-actions">
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
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AdminLayout>
</template>
