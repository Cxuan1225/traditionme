<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alert';
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
import SettingsLayout from '@/layouts/settings/AdminSettingsLayout.vue';
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

const readCookie = (name: string): string | null => {
    const encodedName = `${encodeURIComponent(name)}=`;
    const cookies = document.cookie.split(';');
    const cookie = cookies.find((part) => part.trim().startsWith(encodedName));

    if (!cookie) {
        return null;
    }

    return decodeURIComponent(cookie.trim().slice(encodedName.length));
};

const requestJson = async <T>(
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
        const payload = await requestJson<{ data?: unknown }>(productsIndexRoute.url());
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
    if ((!isEditing && !abilities.value.canCreateProducts) || (isEditing && !abilities.value.canUpdateProducts)) {
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
            const response = await requestJson(productsRoutes.update.url({ product: editingId.value }), {
                method: 'PUT',
                body: payload,
            });

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

        products.value = products.value.filter((product) => product.id !== productId);
        if (editingId.value === productId) {
            resetForm();
        }
        pageSuccess.value = 'Product deleted.';
    } catch (error) {
        pageError.value =
            error instanceof Error ? error.message : 'Unable to delete product.';
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
        <SettingsLayout>
            <div class="flex flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <Alert v-if="pageError" variant="destructive">
                <AlertTitle>Request failed</AlertTitle>
                <AlertDescription>{{ pageError }}</AlertDescription>
            </Alert>

            <Alert v-if="pageSuccess">
                <AlertTitle>Saved</AlertTitle>
                <AlertDescription>{{ pageSuccess }}</AlertDescription>
            </Alert>

            <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                <Card class="h-full">
                    <CardHeader>
                        <CardTitle>Products</CardTitle>
                        <CardDescription>
                            Manage catalog products shown on storefront pages.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-if="loading" class="flex items-center gap-2 text-sm">
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
                            class="text-sm text-muted-foreground"
                        >
                            No products available yet.
                        </p>

                        <div v-else class="space-y-3">
                            <div
                                v-for="product in products"
                                :key="product.id"
                                class="rounded-lg border p-3"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="font-medium">{{ product.name }}</p>
                                        <p class="text-xs text-muted-foreground">{{ product.slug }}</p>
                                    </div>
                                    <Badge :variant="product.is_active ? 'default' : 'secondary'">
                                        {{ product.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </div>

                                <p class="mt-2 text-xs text-muted-foreground">
                                    {{ product.category }} · {{ product.price_in_sen }} sen
                                </p>

                                <div class="mt-3 flex gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        :disabled="!abilities.canUpdateProducts"
                                        @click="startEdit(product)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        :disabled="!abilities.canDeleteProducts || deleting === product.id"
                                        @click="removeProduct(product.id)"
                                    >
                                        <Spinner v-if="deleting === product.id" />
                                        Delete
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button
                            variant="outline"
                            :disabled="loading || !abilities.canViewProducts"
                            @click="loadProducts"
                        >
                            Refresh
                        </Button>
                    </CardFooter>
                </Card>

                <Card class="h-full">
                    <CardHeader>
                        <CardTitle>{{ editingId ? 'Edit product' : 'Create product' }}</CardTitle>
                        <CardDescription>
                            Define storefront product fields and status.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" placeholder="songket-luxe-kurung-set" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="category">Category</Label>
                            <Input id="category" v-model="form.category" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="grid gap-2">
                                <Label for="price">Price (sen)</Label>
                                <Input id="price" v-model="form.price_in_sen" inputmode="numeric" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="original-price">Original price (sen)</Label>
                                <Input id="original-price" v-model="form.original_price_in_sen" inputmode="numeric" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="badge">Badge</Label>
                            <Input id="badge" v-model="form.badge" placeholder="Best Seller" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="gradient">Gradient classes</Label>
                            <Input id="gradient" v-model="form.gradient" placeholder="from-rose-100 via-orange-50 to-amber-100" />
                        </div>

                        <Label class="flex items-center gap-2 text-sm">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-zinc-300"
                            />
                            Product is active
                        </Label>
                    </CardContent>
                    <CardFooter class="flex gap-2">
                        <Button
                            :disabled="submitting || (!editingId && !abilities.canCreateProducts) || (editingId !== null && !abilities.canUpdateProducts)"
                            @click="submitForm"
                        >
                            <Spinner v-if="submitting" />
                            {{ editingId ? 'Update product' : 'Create product' }}
                        </Button>
                        <Button variant="outline" :disabled="submitting" @click="resetForm">
                            Clear
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </SettingsLayout>
    </AdminLayout>
</template>
