<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { toRinggit } from '@/composables/useCurrency';
import AdminLayout from '@/layouts/admin/Layout.vue';
import adminOrdersRoutes, {
    index as ordersIndex,
    show as showOrder,
} from '@/routes/admin/orders';
import type { BreadcrumbItem } from '@/types';
import type { AdminOrder } from '@/types/admin-order';

type StatusOption = {
    value: string;
    label: string;
};

const props = defineProps<{
    order: AdminOrder;
    statusOptions: StatusOption[];
    capabilities: {
        canUpdateStatus?: boolean;
    };
}>();

const page = usePage<{ flash?: { status?: string } }>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Orders',
        href: ordersIndex(),
    },
    {
        title: props.order.number,
        href: showOrder({ order: props.order.id }),
    },
]);

const status = ref<string>(props.order.status);
const notes = ref<string>(props.order.notes ?? '');
const isUpdating = ref<boolean>(false);

watch(
    () => props.order,
    (order) => {
        status.value = order.status;
        notes.value = order.notes ?? '';
    },
    { deep: true },
);

const placedAtLabel = computed<string>(() =>
    formatTimestamp(props.order.placedAt),
);

const timeline = computed<Array<{ label: string; value: string | null }>>(
    () => [
        { label: 'Placed', value: props.order.placedAt },
        { label: 'Paid', value: props.order.paidAt },
        { label: 'Shipped', value: props.order.shippedAt },
        { label: 'Delivered', value: props.order.deliveredAt },
    ],
);

const statusBadgeClass = computed<string>(() => {
    switch (props.order.status) {
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
});

const updateStatus = (): void => {
    if (!props.capabilities.canUpdateStatus || isUpdating.value) {
        return;
    }

    if (!window.confirm(`Update ${props.order.number} to ${status.value}?`)) {
        return;
    }

    isUpdating.value = true;

    router.patch(
        adminOrdersRoutes.updateStatus.url({ order: props.order.id }),
        {
            status: status.value,
            notes: notes.value,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                isUpdating.value = false;
            },
        },
    );
};

function formatTimestamp(value: string | null): string {
    if (value === null) {
        return 'Not recorded yet';
    }

    return new Intl.DateTimeFormat('en-MY', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}
</script>

<template>
    <Head :title="`Order ${order.number}`" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <section class="tm-shell p-6">
                <div
                    class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between"
                >
                    <div class="max-w-3xl">
                        <p class="tm-kicker text-primary">Order detail</p>
                        <h1 class="tm-page-title mt-2 text-foreground">
                            {{ order.number }}
                        </h1>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Placed {{ placedAtLabel }} by
                            {{ order.user?.name ?? 'Unknown customer' }}.
                        </p>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <Badge :class="statusBadgeClass">
                                {{ order.statusLabel }}
                            </Badge>
                            <span class="tm-chip">
                                {{ order.summary.itemCount }} items
                            </span>
                            <span class="tm-chip">
                                {{ toRinggit(order.summary.totalInSen) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="tm-stat min-w-36">
                            <p class="tm-kicker text-muted-foreground">
                                Customer
                            </p>
                            <p class="tm-title mt-2 text-lg">
                                {{ order.user?.name ?? 'Unknown' }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ order.user?.email ?? 'No email on record' }}
                            </p>
                        </div>
                        <div class="tm-stat min-w-36">
                            <p class="tm-kicker text-muted-foreground">
                                Shipping state
                            </p>
                            <p class="tm-title mt-2 text-lg">
                                {{ order.shipping.state }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ order.shipping.city }},
                                {{ order.shipping.postcode }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div
                v-if="page.props.flash?.status"
                class="rounded-3xl border border-emerald-300/70 bg-emerald-50/80 px-5 py-4 text-sm text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-950/30 dark:text-emerald-100"
            >
                {{ page.props.flash.status }}
            </div>

            <div class="grid gap-5 xl:grid-cols-[1.2fr_0.8fr]">
                <section class="space-y-5">
                    <Card class="tm-card">
                        <CardHeader class="border-b border-border/70">
                            <CardTitle>Status management</CardTitle>
                            <p class="text-sm text-muted-foreground">
                                Update the operational state and keep internal
                                notes attached to the order record.
                            </p>
                        </CardHeader>
                        <CardContent class="grid gap-4 p-6 lg:grid-cols-2">
                            <div class="space-y-2">
                                <label
                                    for="order-status"
                                    class="text-sm font-medium text-foreground"
                                >
                                    Status
                                </label>
                                <select
                                    id="order-status"
                                    v-model="status"
                                    class="tm-input-surface h-10"
                                    :disabled="!capabilities.canUpdateStatus"
                                >
                                    <option
                                        v-for="option in statusOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label
                                    for="order-notes"
                                    class="text-sm font-medium text-foreground"
                                >
                                    Notes
                                </label>
                                <textarea
                                    id="order-notes"
                                    v-model="notes"
                                    class="tm-input-surface min-h-28 resize-y"
                                    :disabled="!capabilities.canUpdateStatus"
                                    placeholder="Internal delivery or customer notes"
                                />
                            </div>

                            <div
                                class="flex flex-wrap items-center justify-between gap-3 rounded-3xl border border-border/70 bg-background/70 px-4 py-3 lg:col-span-2"
                            >
                                <p class="text-sm text-muted-foreground">
                                    Current status:
                                    <span class="font-semibold text-foreground">
                                        {{ order.statusLabel }}
                                    </span>
                                </p>
                                <Button
                                    :disabled="
                                        !capabilities.canUpdateStatus ||
                                        isUpdating
                                    "
                                    @click="updateStatus"
                                >
                                    {{
                                        isUpdating
                                            ? 'Updating...'
                                            : 'Update order'
                                    }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="tm-card">
                        <CardHeader class="border-b border-border/70">
                            <CardTitle>Line items</CardTitle>
                        </CardHeader>
                        <CardContent class="p-0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm">
                                    <thead class="border-b border-border/70">
                                        <tr>
                                            <th class="px-6 py-4 font-semibold">
                                                Item
                                            </th>
                                            <th class="px-6 py-4 font-semibold">
                                                Qty
                                            </th>
                                            <th class="px-6 py-4 font-semibold">
                                                Unit
                                            </th>
                                            <th class="px-6 py-4 font-semibold">
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="item in order.items"
                                            :key="item.id"
                                            class="border-b border-border/60 last:border-b-0"
                                        >
                                            <td class="px-6 py-4 align-top">
                                                <p
                                                    class="font-semibold text-foreground"
                                                >
                                                    {{ item.productName }}
                                                </p>
                                                <p
                                                    v-if="item.category"
                                                    class="mt-1 text-xs text-muted-foreground"
                                                >
                                                    {{ item.category }}
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                {{ item.quantity }}
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                {{
                                                    toRinggit(
                                                        item.unitPriceInSen,
                                                    )
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 align-top font-semibold"
                                            >
                                                {{
                                                    toRinggit(
                                                        item.subtotalInSen,
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                <aside class="space-y-5">
                    <Card class="tm-card">
                        <CardHeader class="border-b border-border/70">
                            <CardTitle>Payment summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 p-6">
                            <div class="tm-summary-row">
                                <span>Subtotal</span>
                                <span>{{ order.summary.subtotal }}</span>
                            </div>
                            <div class="tm-summary-row">
                                <span>Discount</span>
                                <span>
                                    {{
                                        order.summary.discountInSen === 0
                                            ? 'RM 0.00'
                                            : `- ${order.summary.discount}`
                                    }}
                                </span>
                            </div>
                            <div class="tm-summary-row">
                                <span>Shipping</span>
                                <span>{{ order.summary.shipping }}</span>
                            </div>
                            <div
                                class="tm-summary-row border-t border-border pt-3 text-base font-black"
                            >
                                <span>Total</span>
                                <span class="text-primary">
                                    {{ order.summary.total }}
                                </span>
                            </div>
                            <div
                                v-if="order.couponCode"
                                class="rounded-3xl border border-border/70 bg-secondary/30 px-4 py-3"
                            >
                                <p class="tm-kicker text-muted-foreground">
                                    Coupon
                                </p>
                                <p class="mt-2 font-semibold text-foreground">
                                    {{ order.couponCode }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card class="tm-card">
                        <CardHeader class="border-b border-border/70">
                            <CardTitle>Shipping address</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 p-6">
                            <p class="font-semibold text-foreground">
                                {{ order.shipping.name }}
                            </p>
                            <p
                                class="text-sm whitespace-pre-line text-muted-foreground"
                            >
                                {{ order.shipping.address }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ order.shipping.postcode }}
                                {{ order.shipping.city }},
                                {{ order.shipping.state }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ order.shipping.phone }}
                            </p>
                        </CardContent>
                    </Card>

                    <Card class="tm-card">
                        <CardHeader class="border-b border-border/70">
                            <CardTitle>Timeline</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 p-6">
                            <div
                                v-for="entry in timeline"
                                :key="entry.label"
                                class="rounded-3xl border border-border/60 bg-background/60 px-4 py-3"
                            >
                                <p class="tm-kicker text-muted-foreground">
                                    {{ entry.label }}
                                </p>
                                <p
                                    class="mt-2 text-sm font-medium text-foreground"
                                >
                                    {{ formatTimestamp(entry.value) }}
                                </p>
                            </div>
                            <div
                                v-if="order.notes"
                                class="rounded-3xl border border-border/60 bg-background/60 px-4 py-3"
                            >
                                <p class="tm-kicker text-muted-foreground">
                                    Order notes
                                </p>
                                <p
                                    class="mt-2 text-sm whitespace-pre-line text-foreground"
                                >
                                    {{ order.notes }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>
