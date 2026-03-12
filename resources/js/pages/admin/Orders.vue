<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { toRinggit } from '@/composables/useCurrency';
import AdminLayout from '@/layouts/admin/Layout.vue';
import adminOrdersRoutes, { index as ordersIndex } from '@/routes/admin/orders';
import type { BreadcrumbItem } from '@/types';
import type { AdminOrder } from '@/types/admin-order';

type StatusOption = {
    value: string;
    label: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type OrdersPageProps = {
    orders: {
        data: AdminOrder[];
        meta: {
            currentPage: number;
            lastPage: number;
            perPage: number;
            total: number;
            from: number | null;
            to: number | null;
        };
        links: PaginationLink[];
    };
    filters: {
        status: string | null;
        search: string | null;
    };
    statusOptions: StatusOption[];
    capabilities: {
        canUpdateStatus?: boolean;
    };
};

const props = defineProps<OrdersPageProps>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Orders',
        href: ordersIndex(),
    },
];

const search = ref<string>(props.filters.search ?? '');
const status = ref<string>(props.filters.status ?? 'all');

watch(
    () => props.filters,
    (filters) => {
        search.value = filters.search ?? '';
        status.value = filters.status ?? 'all';
    },
    { deep: true },
);

const statusTabs = computed<StatusOption[]>(() => [
    { value: 'all', label: 'All' },
    ...props.statusOptions,
]);

const statusCounts = computed<Record<string, number>>(() => {
    return props.orders.data.reduce<Record<string, number>>((counts, order) => {
        counts[order.status] = (counts[order.status] ?? 0) + 1;

        return counts;
    }, {});
});

const applyFilters = (): void => {
    const trimmedSearch = search.value.trim();

    router.get(
        ordersIndex.url({
            query: {
                status: status.value === 'all' ? undefined : status.value,
                search: trimmedSearch === '' ? undefined : trimmedSearch,
            },
        }),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const resetFilters = (): void => {
    search.value = '';
    status.value = 'all';
    applyFilters();
};

const visitOrder = (order: AdminOrder): void => {
    router.visit(adminOrdersRoutes.show.url({ order: order.id }));
};

const visitLink = (url: string | null): void => {
    if (url === null) {
        return;
    }

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
};

const formatPlacedAt = (value: string | null): string => {
    if (value === null) {
        return 'Just now';
    }

    return new Intl.DateTimeFormat('en-MY', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const statusBadgeClass = (value: string): string => {
    switch (value) {
        case 'paid':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200';
        case 'shipped':
            return 'bg-sky-100 text-sky-800 dark:bg-sky-950/40 dark:text-sky-200';
        case 'delivered':
            return 'bg-violet-100 text-violet-800 dark:bg-violet-950/40 dark:text-violet-200';
        case 'cancelled':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-200';
        default:
            return 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-200';
    }
};
</script>

<template>
    <Head title="Orders" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <section class="tm-shell p-6">
                <p class="tm-kicker text-primary">Order operations</p>
                <div
                    class="mt-3 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between"
                >
                    <div class="max-w-3xl">
                        <h1 class="tm-page-title text-foreground">
                            Fulfillment queue
                        </h1>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Track purchase flow, confirm status changes, and
                            jump into order detail for exceptions.
                        </p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="tm-stat min-w-32">
                            <p class="tm-kicker text-muted-foreground">
                                Visible orders
                            </p>
                            <p class="tm-title mt-2">{{ orders.meta.total }}</p>
                        </div>
                        <div class="tm-stat min-w-32">
                            <p class="tm-kicker text-muted-foreground">
                                Awaiting action
                            </p>
                            <p class="tm-title mt-2">
                                {{
                                    (statusCounts.pending ?? 0) +
                                    (statusCounts.paid ?? 0)
                                }}
                            </p>
                        </div>
                        <div class="tm-stat min-w-32">
                            <p class="tm-kicker text-muted-foreground">
                                Ready to ship
                            </p>
                            <p class="tm-title mt-2">
                                {{ statusCounts.paid ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <Card class="tm-card">
                <CardHeader class="gap-4 border-b border-border/70">
                    <div
                        class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between"
                    >
                        <div>
                            <CardTitle>Filters</CardTitle>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Narrow by order state or search by order number
                                and customer name.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Button variant="outline" @click="resetFilters">
                                Clear
                            </Button>
                            <Button @click="applyFilters">Apply filters</Button>
                        </div>
                    </div>

                    <div class="grid gap-3 lg:grid-cols-[1.1fr_0.9fr]">
                        <div class="space-y-2">
                            <label
                                for="search-orders"
                                class="text-sm font-medium text-foreground"
                            >
                                Search
                            </label>
                            <Input
                                id="search-orders"
                                v-model="search"
                                class="tm-input-surface"
                                placeholder="TM-000123 or Nur Aina"
                                @keydown.enter.prevent="applyFilters"
                            />
                        </div>
                        <div class="space-y-2">
                            <label
                                for="status-filter"
                                class="text-sm font-medium text-foreground"
                            >
                                Status
                            </label>
                            <select
                                id="status-filter"
                                v-model="status"
                                class="tm-input-surface h-10"
                            >
                                <option
                                    v-for="option in statusTabs"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="option in statusTabs"
                            :key="option.value"
                            type="button"
                            class="rounded-full border px-3 py-1.5 text-xs font-semibold transition"
                            :class="
                                status === option.value
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : 'border-border bg-background text-muted-foreground hover:border-primary/40 hover:text-foreground'
                            "
                            @click="
                                status = option.value;
                                applyFilters();
                            "
                        >
                            {{ option.label }}
                            <span class="ml-1 opacity-70">
                                {{
                                    option.value === 'all'
                                        ? orders.meta.total
                                        : (statusCounts[option.value] ?? 0)
                                }}
                            </span>
                        </button>
                    </div>
                </CardHeader>

                <CardContent class="p-0">
                    <div
                        v-if="orders.data.length === 0"
                        class="p-8 text-center"
                    >
                        <p class="tm-subtitle">No orders matched this view.</p>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Try clearing the current filters or search terms.
                        </p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="border-b border-border/70">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">
                                        Order
                                    </th>
                                    <th class="px-6 py-4 font-semibold">
                                        Customer
                                    </th>
                                    <th class="px-6 py-4 font-semibold">
                                        Status
                                    </th>
                                    <th class="px-6 py-4 font-semibold">
                                        Items
                                    </th>
                                    <th class="px-6 py-4 font-semibold">
                                        Total
                                    </th>
                                    <th class="px-6 py-4 font-semibold">
                                        Placed
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="order in orders.data"
                                    :key="order.id"
                                    class="cursor-pointer border-b border-border/60 transition hover:bg-accent/30"
                                    @click="visitOrder(order)"
                                >
                                    <td class="px-6 py-4 align-top">
                                        <p
                                            class="font-semibold text-foreground"
                                        >
                                            {{ order.number }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            #{{ order.id }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <p class="font-medium text-foreground">
                                            {{
                                                order.user?.name ??
                                                'Unknown customer'
                                            }}
                                        </p>
                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            {{
                                                order.user?.email ?? 'No email'
                                            }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <Badge
                                            :class="
                                                statusBadgeClass(order.status)
                                            "
                                        >
                                            {{ order.statusLabel }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        {{ order.summary.itemCount }}
                                    </td>
                                    <td
                                        class="px-6 py-4 align-top font-semibold"
                                    >
                                        {{
                                            toRinggit(order.summary.totalInSen)
                                        }}
                                    </td>
                                    <td
                                        class="px-6 py-4 align-top text-muted-foreground"
                                    >
                                        {{ formatPlacedAt(order.placedAt) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            <div
                class="flex flex-col gap-3 rounded-3xl border border-border/70 bg-card/80 px-5 py-4 md:flex-row md:items-center md:justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing
                    <span class="font-semibold text-foreground">
                        {{ orders.meta.from ?? 0 }}-{{ orders.meta.to ?? 0 }}
                    </span>
                    of
                    <span class="font-semibold text-foreground">
                        {{ orders.meta.total }}
                    </span>
                    orders.
                </p>

                <div class="flex flex-wrap gap-2">
                    <Button
                        v-for="link in orders.links"
                        :key="`${link.label}-${link.url}`"
                        variant="outline"
                        size="sm"
                        :disabled="link.url === null"
                        :class="
                            link.active
                                ? 'border-primary bg-primary/10 text-primary'
                                : ''
                        "
                        @click="visitLink(link.url)"
                    >
                        <span v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
