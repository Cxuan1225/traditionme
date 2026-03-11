<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    PackageCheck,
    ReceiptText,
    Truck,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { toRinggit } from '@/composables/useCurrency';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import shop from '@/routes/shop';
import type { Order } from '@/types/order';

const props = defineProps<{
    order: Order;
}>();

const page = usePage<{ flash?: { status?: string } }>();

const placedAtLabel = computed<string>(() => {
    if (props.order.placedAt === null) {
        return 'Just now';
    }

    return new Intl.DateTimeFormat('en-MY', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(props.order.placedAt));
});
</script>

<template>
    <Head :title="`Order ${order.number}`" />

    <StorefrontLayout>
        <section class="space-y-6">
            <article
                class="tm-section overflow-hidden border border-emerald-200/80 bg-[radial-gradient(circle_at_top_left,hsl(144_70%_80%/.32),transparent_35%),linear-gradient(145deg,hsl(148_55%_96%),hsl(49_89%_96%)_50%,hsl(0_0%_100%)_100%)] p-6 dark:border-emerald-500/30 dark:bg-[radial-gradient(circle_at_top_left,hsl(152_70%_30%/.3),transparent_35%),linear-gradient(145deg,hsl(156_20%_15%),hsl(210_18%_12%)_50%,hsl(220_18%_10%)_100%)]"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="max-w-3xl">
                        <p
                            class="tm-kicker text-emerald-700 dark:text-emerald-300"
                        >
                            Order confirmed
                        </p>
                        <h1 class="tm-title-xl mt-2">
                            Your order is in motion.
                        </h1>
                        <p class="tm-body mt-3">
                            We’ve captured your shipping details and locked the
                            current product snapshot for fulfillment.
                        </p>
                        <div class="mt-5 flex flex-wrap items-center gap-3">
                            <Badge
                                class="bg-emerald-600 text-white hover:bg-emerald-600"
                            >
                                <CheckCircle2 class="mr-1 size-3.5" />
                                {{ order.statusLabel }}
                            </Badge>
                            <span class="tm-chip">{{ order.number }}</span>
                            <span class="tm-chip">{{ placedAtLabel }}</span>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="tm-stat min-w-28">
                            <p class="tm-kicker text-muted-foreground">Items</p>
                            <p class="tm-title mt-2">
                                {{ order.summary.itemCount }}
                            </p>
                        </div>
                        <div class="tm-stat min-w-28">
                            <p class="tm-kicker text-muted-foreground">Total</p>
                            <p class="tm-title mt-2">
                                {{ toRinggit(order.summary.totalInSen) }}
                            </p>
                        </div>
                        <div class="tm-stat min-w-28">
                            <p class="tm-kicker text-muted-foreground">
                                Ship to
                            </p>
                            <p class="tm-title mt-2 text-lg">
                                {{ order.shipping.state }}
                            </p>
                        </div>
                    </div>
                </div>
            </article>

            <article
                v-if="page.props.flash?.status"
                class="tm-panel border border-emerald-200 bg-emerald-50/80 p-4 dark:border-emerald-500/40 dark:bg-emerald-950/30"
            >
                <p class="tm-body-sm text-emerald-800 dark:text-emerald-100">
                    {{ page.props.flash.status }}
                </p>
            </article>

            <div class="grid gap-5 xl:grid-cols-[1.1fr_360px]">
                <section class="space-y-4">
                    <article class="tm-panel p-5">
                        <div class="flex items-center gap-2">
                            <ReceiptText class="size-4 text-primary" />
                            <p class="tm-subtitle">Line items</p>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="text-muted-foreground">
                                    <tr class="border-b border-border">
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
                                            <p
                                                class="font-semibold text-foreground"
                                            >
                                                {{ item.productName }}
                                            </p>
                                            <p
                                                v-if="item.category"
                                                class="tm-body-sm mt-1"
                                            >
                                                {{ item.category }}
                                            </p>
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
                            <p class="tm-subtitle">Shipping destination</p>
                        </div>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="tm-list-item">
                                <p class="tm-kicker text-muted-foreground">
                                    Recipient
                                </p>
                                <p class="tm-subtitle mt-2">
                                    {{ order.shipping.name }}
                                </p>
                                <p class="tm-body-sm mt-2 whitespace-pre-line">
                                    {{ order.shipping.address }}
                                </p>
                            </div>
                            <div class="tm-list-item">
                                <p class="tm-kicker text-muted-foreground">
                                    Delivery details
                                </p>
                                <p class="tm-body-sm mt-2">
                                    {{ order.shipping.postcode }}
                                    {{ order.shipping.city }}
                                </p>
                                <p class="tm-body-sm mt-1">
                                    {{ order.shipping.state }}
                                </p>
                                <p class="tm-body-sm mt-3">
                                    {{ order.shipping.phone }}
                                </p>
                            </div>
                        </div>
                        <div v-if="order.notes" class="tm-list-item mt-4">
                            <p class="tm-kicker text-muted-foreground">Notes</p>
                            <p class="tm-body-sm mt-2 whitespace-pre-line">
                                {{ order.notes }}
                            </p>
                        </div>
                    </article>
                </section>

                <aside class="space-y-4 xl:sticky xl:top-24 xl:h-fit">
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
                            class="mt-4 rounded-3xl border border-border/70 bg-secondary/40 p-4"
                        >
                            <p class="tm-kicker text-muted-foreground">
                                Applied offer
                            </p>
                            <p class="tm-subtitle mt-2">
                                {{ order.couponCode }}
                            </p>
                        </div>

                        <Link :href="shop.index()" class="mt-5 block">
                            <Button class="w-full">Continue shopping</Button>
                        </Link>
                    </article>

                    <article class="tm-panel p-5">
                        <div class="flex items-start gap-3">
                            <Truck class="mt-0.5 size-4 text-primary" />
                            <div>
                                <p class="tm-subtitle">What happens next</p>
                                <div class="mt-3 space-y-2">
                                    <p class="tm-body-sm">
                                        Your order remains in `pending` status
                                        until payment and fulfillment are
                                        processed.
                                    </p>
                                    <p class="tm-body-sm">
                                        Item names and pricing are stored as
                                        order snapshots, so this confirmation
                                        stays stable even if the catalog changes
                                        later.
                                    </p>
                                    <p class="tm-body-sm">
                                        Our team will use the provided address
                                        and phone details for delivery
                                        coordination.
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
