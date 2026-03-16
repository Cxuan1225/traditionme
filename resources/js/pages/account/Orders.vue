<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import accountOrdersRoutes, {
    index as ordersIndex,
} from '@/routes/account/orders';
import shop from '@/routes/shop';
import type { Order } from '@/types/order';

type StatusOption = {
    value: string;
    label: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

const props = defineProps<{
    orders: {
        data: Order[];
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
    };
    statusOptions: StatusOption[];
}>();

const status = ref<string>(props.filters.status ?? 'all');

watch(
    () => props.filters.status,
    (value) => {
        status.value = value ?? 'all';
    },
);

const statusPills = computed<StatusOption[]>(() => [
    { value: 'all', label: 'All' },
    ...props.statusOptions,
]);

const visibleStatusCounts = computed<Record<string, number>>(() =>
    props.orders.data.reduce<Record<string, number>>((counts, order) => {
        counts[order.status] = (counts[order.status] ?? 0) + 1;

        return counts;
    }, {}),
);

const applyStatusFilter = (nextStatus: string): void => {
    status.value = nextStatus;

    router.get(
        ordersIndex.url({
            query: {
                status: nextStatus === 'all' ? undefined : nextStatus,
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

const firstItemImageClass = (order: Order): string =>
    order.items[0]?.gradient ?? 'from-amber-100 via-orange-100 to-rose-100';
</script>

<template>
    <Head title="My Orders" />

    <StorefrontLayout>
        <section class="space-y-6">
            <article
                class="tm-shell overflow-hidden bg-[linear-gradient(145deg,hsl(38_85%_95%)_0%,hsl(22_76%_92%)_45%,hsl(6_74%_90%)_100%)] p-6 lg:p-8"
            >
                <div
                    class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="max-w-3xl">
                        <p class="tm-kicker text-primary">Order history</p>
                        <h1
                            class="mt-3 tm-display text-4xl font-black text-foreground sm:text-5xl"
                        >
                            Keep every purchase within reach.
                        </h1>
                        <p
                            class="mt-4 max-w-2xl text-base text-muted-foreground"
                        >
                            Review order status, reopen delivery details, and
                            revisit the pieces you have already brought home.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="tm-stat min-w-32">
                            <p class="tm-kicker text-muted-foreground">
                                Visible orders
                            </p>
                            <p class="tm-title mt-2">{{ orders.meta.total }}</p>
                        </div>
                        <div class="tm-stat min-w-32">
                            <p class="tm-kicker text-muted-foreground">
                                In progress
                            </p>
                            <p class="tm-title mt-2">
                                {{
                                    (visibleStatusCounts.pending ?? 0) +
                                    (visibleStatusCounts.paid ?? 0) +
                                    (visibleStatusCounts.shipped ?? 0)
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </article>

            <article class="tm-panel p-5">
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div>
                        <p class="tm-subtitle">Filter by status</p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Switch between recent payment and delivery stages.
                        </p>
                    </div>
                    <Link :href="shop.index()">
                        <Button variant="outline">Start shopping</Button>
                    </Link>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button
                        v-for="option in statusPills"
                        :key="option.value"
                        type="button"
                        class="rounded-full border px-4 py-2 text-sm font-semibold transition"
                        :class="
                            status === option.value
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'border-border bg-background text-muted-foreground hover:border-primary/40 hover:text-foreground'
                        "
                        @click="applyStatusFilter(option.value)"
                    >
                        {{ option.label }}
                    </button>
                </div>
            </article>

            <div
                v-if="orders.data.length === 0"
                class="tm-shell p-8 text-center lg:p-12"
            >
                <p class="tm-display text-3xl font-black text-foreground">
                    No orders yet.
                </p>
                <p class="mt-3 text-base text-muted-foreground">
                    Start shopping and your first order will appear here.
                </p>
                <Link :href="shop.index()" class="mt-6 inline-flex">
                    <Button>No orders yet. Start shopping!</Button>
                </Link>
            </div>

            <div v-else class="grid gap-4">
                <Link
                    v-for="order in orders.data"
                    :key="order.id"
                    :href="accountOrdersRoutes.show({ order: order.id })"
                    class="tm-panel block overflow-hidden p-0 transition hover:-translate-y-0.5 hover:border-primary/35"
                >
                    <article
                        class="grid gap-0 md:grid-cols-[140px_1fr] lg:grid-cols-[168px_1fr]"
                    >
                        <div
                            class="relative min-h-40 overflow-hidden border-b border-border/60 md:border-r md:border-b-0"
                            :class="[
                                'bg-gradient-to-br',
                                firstItemImageClass(order),
                            ]"
                        >
                            <img
                                v-if="order.items[0]?.imageUrl"
                                :src="order.items[0].imageUrl"
                                :alt="order.items[0].productName"
                                class="h-full w-full object-cover"
                            />
                            <div
                                v-else
                                class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.75),transparent_55%)]"
                            />
                            <div class="absolute bottom-3 left-3">
                                <span
                                    class="rounded-full border border-white/60 bg-white/80 px-3 py-1 text-xs font-bold tracking-wide text-zinc-900 uppercase backdrop-blur"
                                >
                                    {{ order.items[0]?.category ?? 'Order' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-5 lg:p-6">
                            <div
                                class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"
                            >
                                <div>
                                    <div
                                        class="flex flex-wrap items-center gap-3"
                                    >
                                        <p class="tm-subtitle">
                                            {{ order.number }}
                                        </p>
                                        <Badge
                                            :class="
                                                statusBadgeClass(order.status)
                                            "
                                        >
                                            {{ order.statusLabel }}
                                        </Badge>
                                    </div>
                                    <p
                                        class="mt-2 text-sm text-muted-foreground"
                                    >
                                        Placed
                                        {{ formatPlacedAt(order.placedAt) }}
                                    </p>
                                </div>

                                <div class="text-left lg:text-right">
                                    <p class="tm-kicker text-muted-foreground">
                                        Order total
                                    </p>
                                    <p
                                        class="mt-2 text-xl font-black text-foreground"
                                    >
                                        {{ order.summary.total }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="mt-5 grid gap-3 border-t border-border/60 pt-5 sm:grid-cols-3"
                            >
                                <div class="tm-list-item">
                                    <p class="tm-kicker text-muted-foreground">
                                        Items
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-semibold text-foreground"
                                    >
                                        {{ order.summary.itemCount }} item<span
                                            v-if="order.summary.itemCount !== 1"
                                            >s</span
                                        >
                                    </p>
                                </div>
                                <div class="tm-list-item">
                                    <p class="tm-kicker text-muted-foreground">
                                        First piece
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-semibold text-foreground"
                                    >
                                        {{
                                            order.items[0]?.productName ??
                                            'Unavailable'
                                        }}
                                    </p>
                                </div>
                                <div class="tm-list-item">
                                    <p class="tm-kicker text-muted-foreground">
                                        Delivering to
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-semibold text-foreground"
                                    >
                                        {{ order.shipping.city }},
                                        {{ order.shipping.state }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </article>
                </Link>
            </div>

            <div
                v-if="orders.data.length > 0"
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
        </section>
    </StorefrontLayout>
</template>
