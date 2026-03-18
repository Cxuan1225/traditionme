<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Clock3,
    CreditCard,
    PackageCheck,
    ReceiptText,
    ShieldCheck,
    Truck,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { toRinggit } from '@/composables/useCurrency';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import { index as ordersIndex } from '@/routes/account/orders';
import { pay as orderPay } from '@/routes/orders';
import shop from '@/routes/shop';
import type { Order } from '@/types/order';

const props = defineProps<{
    order: Order;
}>();

function payNow(): void {
    const route = orderPay(props.order.id);
    router.post(route.url);
}

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

    <StorefrontLayout>
        <section class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link
                    :href="ordersIndex()"
                    class="inline-flex items-center text-sm font-semibold text-primary transition hover:opacity-80"
                >
                    Back to orders
                </Link>
                <Link :href="shop.index()">
                    <Button variant="outline">Continue shopping</Button>
                </Link>
            </div>

            <article class="tm-shell p-6">
                <div
                    class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between"
                >
                    <div class="max-w-3xl">
                        <p class="tm-kicker text-primary">Order detail</p>
                        <h1
                            class="mt-2 tm-display text-4xl font-black text-foreground sm:text-5xl"
                        >
                            {{ order.number }}
                        </h1>
                        <p class="mt-3 text-base text-muted-foreground">
                            Placed {{ placedAtLabel }} and currently moving
                            through your fulfillment timeline.
                        </p>
                        <div class="mt-5 flex flex-wrap items-center gap-3">
                            <Badge :class="statusBadgeClass">
                                {{ order.statusLabel }}
                            </Badge>
                            <span class="tm-chip">
                                {{ order.summary.itemCount }} items
                            </span>
                            <span class="tm-chip">{{
                                order.summary.total
                            }}</span>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
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
                        <div class="tm-stat min-w-36">
                            <p class="tm-kicker text-muted-foreground">
                                Recipient
                            </p>
                            <p class="tm-title mt-2 text-lg">
                                {{ order.shipping.name }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ order.shipping.phone }}
                            </p>
                        </div>
                    </div>
                </div>
            </article>

            <article
                v-if="order.status === 'pending'"
                class="flex flex-wrap items-center justify-between gap-4 rounded-3xl border border-amber-300/70 bg-amber-50/80 px-5 py-4 text-sm text-amber-900 dark:border-amber-500/40 dark:bg-amber-950/30 dark:text-amber-100"
            >
                <span>
                    Payment is still pending. Complete payment to confirm your
                    order.
                </span>
                <Button size="sm" @click="payNow">
                    <CreditCard class="mr-2 size-3.5" />
                    Pay now
                </Button>
            </article>

            <article
                v-else-if="order.paymentTransactionId"
                class="flex items-center gap-3 rounded-3xl border border-emerald-200 bg-emerald-50/80 px-5 py-4 text-sm text-emerald-800 dark:border-emerald-500/40 dark:bg-emerald-950/30 dark:text-emerald-100"
            >
                <ShieldCheck class="size-4 shrink-0" />
                <span>
                    Payment confirmed via
                    {{ order.paymentMethod ?? 'card' }}
                </span>
            </article>

            <div class="grid gap-5 xl:grid-cols-[1.15fr_360px]">
                <section class="space-y-5">
                    <article class="tm-panel p-5">
                        <div class="flex items-center gap-2">
                            <ReceiptText class="size-4 text-primary" />
                            <p class="tm-subtitle">Line items</p>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead
                                    class="border-b border-border/70 text-muted-foreground"
                                >
                                    <tr>
                                        <th class="px-0 py-3 font-semibold">
                                            Item
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-semibold"
                                        >
                                            Qty
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right font-semibold"
                                        >
                                            Unit
                                        </th>
                                        <th
                                            class="px-0 py-3 text-right font-semibold"
                                        >
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
                                        <td class="px-0 py-4">
                                            <div
                                                class="flex items-center gap-4"
                                            >
                                                <div
                                                    class="flex h-16 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br"
                                                    :class="
                                                        item.gradient ??
                                                        'from-amber-100 via-orange-100 to-rose-100'
                                                    "
                                                >
                                                    <img
                                                        v-if="item.imageUrl"
                                                        :src="item.imageUrl"
                                                        :alt="item.productName"
                                                        class="h-full w-full object-cover"
                                                    />
                                                </div>
                                                <div>
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
                                                </div>
                                            </div>
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right text-muted-foreground"
                                        >
                                            {{ item.quantity }}
                                        </td>
                                        <td
                                            class="px-4 py-4 text-right text-muted-foreground"
                                        >
                                            {{ toRinggit(item.unitPriceInSen) }}
                                        </td>
                                        <td
                                            class="px-0 py-4 text-right font-semibold text-foreground"
                                        >
                                            {{ toRinggit(item.subtotalInSen) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="tm-panel p-5">
                        <div class="flex items-center gap-2">
                            <PackageCheck class="size-4 text-primary" />
                            <p class="tm-subtitle">Shipping information</p>
                        </div>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="tm-list-item">
                                <p class="tm-kicker text-muted-foreground">
                                    Address
                                </p>
                                <p
                                    class="mt-2 text-sm font-semibold text-foreground"
                                >
                                    {{ order.shipping.name }}
                                </p>
                                <p
                                    class="mt-2 text-sm whitespace-pre-line text-muted-foreground"
                                >
                                    {{ order.shipping.address }}
                                </p>
                            </div>
                            <div class="tm-list-item">
                                <p class="tm-kicker text-muted-foreground">
                                    Delivery contact
                                </p>
                                <p class="mt-2 text-sm text-foreground">
                                    {{ order.shipping.postcode }}
                                    {{ order.shipping.city }}
                                </p>
                                <p class="mt-1 text-sm text-foreground">
                                    {{ order.shipping.state }}
                                </p>
                                <p class="mt-3 text-sm text-muted-foreground">
                                    {{ order.shipping.phone }}
                                </p>
                            </div>
                        </div>

                        <div v-if="order.notes" class="tm-list-item mt-4">
                            <p class="tm-kicker text-muted-foreground">Notes</p>
                            <p
                                class="mt-2 text-sm whitespace-pre-line text-foreground"
                            >
                                {{ order.notes }}
                            </p>
                        </div>
                    </article>
                </section>

                <aside class="space-y-4 xl:sticky xl:top-24 xl:h-fit">
                    <article class="tm-panel p-5">
                        <div class="flex items-center gap-2">
                            <Clock3 class="size-4 text-primary" />
                            <p class="tm-subtitle">Status timeline</p>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div
                                v-for="entry in timeline"
                                :key="entry.label"
                                class="rounded-3xl border border-border/60 bg-background/60 px-4 py-3"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                >
                                    {{ entry.label }}
                                </p>
                                <p
                                    class="mt-1 text-sm font-medium text-foreground"
                                >
                                    {{ formatTimestamp(entry.value) }}
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="tm-panel p-5">
                        <p class="tm-subtitle">Payment summary</p>
                        <div class="mt-4 space-y-3">
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
                                <span class="text-primary">{{
                                    order.summary.total
                                }}</span>
                            </div>
                        </div>

                        <div
                            v-if="order.couponCode"
                            class="mt-4 rounded-3xl border border-border/70 bg-secondary/30 px-4 py-3"
                        >
                            <p class="tm-kicker text-muted-foreground">
                                Applied offer
                            </p>
                            <p
                                class="mt-2 text-sm font-semibold text-foreground"
                            >
                                {{ order.couponCode }}
                            </p>
                        </div>
                    </article>

                    <article class="tm-panel p-5">
                        <div class="flex items-start gap-3">
                            <Truck class="mt-0.5 size-4 text-primary" />
                            <div>
                                <p class="tm-subtitle">Delivery guidance</p>
                                <div class="mt-3 space-y-2">
                                    <p class="text-sm text-muted-foreground">
                                        This page reflects the saved shipping
                                        details and order snapshot from
                                        checkout.
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        If the timeline has not advanced yet,
                                        our team is still processing the next
                                        fulfillment step.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </article>
                </aside>
            </div>
        </section>
    </StorefrontLayout>
</template>
