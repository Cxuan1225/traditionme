<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ShieldCheck,
    TicketPercent,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import shop from '@/routes/shop';

type CartLine = {
    id: number;
    slug: string;
    name: string;
    category: string;
    size: string;
    unitPriceInSen: number;
    quantity: number;
    tone: string;
};

const toRinggit = (valueInSen: number): string =>
    `RM ${(valueInSen / 100).toFixed(2)}`;

const cartLines = ref<CartLine[]>([
    {
        id: 1,
        slug: 'seri-heritage-kurung',
        name: 'Seri Heritage Kurung',
        category: 'Baju Kurung',
        size: 'M',
        unitPriceInSen: 28900,
        quantity: 1,
        tone: 'from-rose-100 via-amber-50 to-orange-100',
    },
    {
        id: 2,
        slug: 'modern-cheongsam-luna',
        name: 'Modern Cheongsam Luna',
        category: 'Cheongsam',
        size: 'S',
        unitPriceInSen: 25900,
        quantity: 1,
        tone: 'from-fuchsia-100 via-rose-50 to-orange-100',
    },
]);

const couponInput = ref<string>('');
const couponMessage = ref<string>('');
const appliedCoupon = ref<string | null>(null);
const couponState = ref<'warning' | 'success' | 'danger'>('warning');

const itemCount = computed<number>(() =>
    cartLines.value.reduce((sum, line) => sum + line.quantity, 0),
);

const subtotalInSen = computed<number>(() =>
    cartLines.value.reduce(
        (sum, line) => sum + line.unitPriceInSen * line.quantity,
        0,
    ),
);

const discountInSen = computed<number>(() => {
    if (appliedCoupon.value === 'HERITAGE10') {
        return Math.round(subtotalInSen.value * 0.1);
    }

    return 0;
});

const shippingInSen = computed<number>(() => {
    if (cartLines.value.length === 0) {
        return 0;
    }

    return subtotalInSen.value - discountInSen.value >= 20000 ? 0 : 1200;
});

const totalInSen = computed<number>(
    () => subtotalInSen.value - discountInSen.value + shippingInSen.value,
);

const increaseQty = (lineId: number): void => {
    cartLines.value = cartLines.value.map((line) =>
        line.id === lineId ? { ...line, quantity: line.quantity + 1 } : line,
    );
};

const decreaseQty = (lineId: number): void => {
    cartLines.value = cartLines.value
        .map((line) =>
            line.id === lineId
                ? { ...line, quantity: Math.max(1, line.quantity - 1) }
                : line,
        )
        .filter((line) => line.quantity > 0);
};

const removeLine = (lineId: number): void => {
    cartLines.value = cartLines.value.filter((line) => line.id !== lineId);
};

const applyCoupon = (): void => {
    const code = couponInput.value.trim().toUpperCase();
    if (code === '') {
        couponMessage.value = 'Enter a promo code first.';
        appliedCoupon.value = null;
        couponState.value = 'warning';
        return;
    }
    if (code === 'HERITAGE10') {
        appliedCoupon.value = code;
        couponMessage.value = 'Promo applied: 10% off selected cart value.';
        couponState.value = 'success';
        return;
    }

    appliedCoupon.value = null;
    couponMessage.value = 'Promo code not recognized.';
    couponState.value = 'danger';
};
</script>

<template>
    <Head title="Cart" />

    <StorefrontLayout>
        <section class="space-y-6">
            <article class="tm-section">
                <p class="tm-kicker text-primary">Checkout Ready Cart</p>
                <h1 class="tm-title-xl mt-2">
                    Review your selections before checkout.
                </h1>
                <p class="tm-body mt-3 max-w-2xl">
                    Adjust quantity, apply promotions, and confirm your order
                    summary. Shipping updates and payment flow can plug into
                    this structure directly.
                </p>
                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <div class="tm-stat">
                        <p class="tm-kicker text-muted-foreground">Items</p>
                        <p class="tm-title mt-2">{{ itemCount }}</p>
                    </div>
                    <div class="tm-stat">
                        <p class="tm-kicker text-muted-foreground">Subtotal</p>
                        <p class="tm-title mt-2">
                            {{ toRinggit(subtotalInSen) }}
                        </p>
                    </div>
                    <div class="tm-stat">
                        <p class="tm-kicker text-muted-foreground">Shipping</p>
                        <p class="tm-title mt-2">
                            {{
                                shippingInSen === 0
                                    ? 'Free'
                                    : toRinggit(shippingInSen)
                            }}
                        </p>
                    </div>
                </div>
            </article>

            <div class="grid gap-5 xl:grid-cols-[1fr_360px]">
                <section class="space-y-4">
                    <article
                        v-if="cartLines.length === 0"
                        class="tm-section text-center"
                        aria-live="polite"
                    >
                        <h2 class="tm-title">Your cart is empty</h2>
                        <p class="tm-body mt-2">
                            Explore curated collections and add your favorite
                            styles to start checkout.
                        </p>
                        <Link :href="shop.index()">
                            <Button class="mt-4">Continue shopping</Button>
                        </Link>
                    </article>

                    <article v-else class="tm-panel p-5">
                        <div
                            class="mb-4 flex items-center justify-between gap-2"
                        >
                            <p class="tm-subtitle">Line items</p>
                            <span class="tm-chip"
                                >{{ itemCount }} total piece(s)</span
                            >
                        </div>
                        <div class="space-y-3">
                            <div
                                v-for="line in cartLines"
                                :key="line.id"
                                class="tm-list-item grid gap-3 sm:grid-cols-[86px_1fr_auto]"
                            >
                                <div
                                    class="tm-product-media h-[86px]"
                                    :class="line.tone"
                                />
                                <div class="min-w-0">
                                    <p class="tm-subtitle truncate">
                                        {{ line.name }}
                                    </p>
                                    <p class="tm-body-sm mt-1">
                                        {{ line.category }} · Size
                                        {{ line.size }}
                                    </p>
                                    <p class="tm-title text-primary mt-2">
                                        {{ toRinggit(line.unitPriceInSen) }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <div class="tm-qty-control">
                                        <button
                                            type="button"
                                            class="rounded-full px-2 py-1 text-sm font-bold"
                                            :aria-label="`Decrease quantity for ${line.name}`"
                                            @click="decreaseQty(line.id)"
                                        >
                                            -
                                        </button>
                                        <span
                                            class="min-w-7 text-center text-sm font-bold"
                                        >
                                            {{ line.quantity }}
                                        </span>
                                        <button
                                            type="button"
                                            class="rounded-full px-2 py-1 text-sm font-bold"
                                            :aria-label="`Increase quantity for ${line.name}`"
                                            @click="increaseQty(line.id)"
                                        >
                                            +
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        class="text-xs font-semibold text-red-600 transition hover:text-red-500"
                                        :aria-label="`Remove ${line.name} from cart`"
                                        @click="removeLine(line.id)"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="tm-panel p-5">
                        <div class="mb-3 flex items-center gap-2">
                            <TicketPercent class="text-primary size-4" />
                            <p class="tm-subtitle">Promo code</p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <Input
                                v-model="couponInput"
                                class="tm-input-surface"
                                placeholder="Try HERITAGE10"
                            />
                            <Button variant="outline" @click="applyCoupon"
                                >Apply</Button
                            >
                        </div>
                        <p
                            v-if="couponMessage"
                            class="tm-state-note mt-2"
                            :class="{
                                'tm-state-note-warning':
                                    couponState === 'warning',
                                'tm-state-note-success':
                                    couponState === 'success',
                                'tm-state-note-danger':
                                    couponState === 'danger',
                            }"
                            aria-live="polite"
                        >
                            {{ couponMessage }}
                        </p>
                    </article>
                </section>

                <aside class="space-y-4 xl:sticky xl:top-24 xl:h-fit">
                    <article class="tm-panel p-5">
                        <p class="tm-subtitle">Order summary</p>
                        <div class="mt-4 space-y-3">
                            <div class="tm-summary-row">
                                <span>Subtotal</span>
                                <span>{{ toRinggit(subtotalInSen) }}</span>
                            </div>
                            <div class="tm-summary-row">
                                <span>Discount</span>
                                <span>
                                    {{
                                        discountInSen === 0
                                            ? 'RM 0.00'
                                            : `- ${toRinggit(discountInSen)}`
                                    }}
                                </span>
                            </div>
                            <div class="tm-summary-row">
                                <span>Shipping</span>
                                <span>
                                    {{
                                        shippingInSen === 0
                                            ? 'Free'
                                            : toRinggit(shippingInSen)
                                    }}
                                </span>
                            </div>
                            <div class="border-border border-t pt-3">
                                <div
                                    class="tm-summary-row text-base font-black"
                                >
                                    <span>Total</span>
                                    <span class="text-primary">{{
                                        toRinggit(totalInSen)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <Button
                            class="mt-5 w-full"
                            :disabled="cartLines.length === 0"
                        >
                            Proceed to checkout
                        </Button>
                        <Link :href="shop.index()" class="mt-2 block">
                            <Button variant="outline" class="w-full"
                                >Continue shopping</Button
                            >
                        </Link>
                    </article>

                    <article class="tm-panel p-5">
                        <p class="tm-subtitle">Why shop with Tradition Me</p>
                        <div class="mt-3 space-y-2">
                            <p class="tm-body-sm flex items-center gap-2">
                                <ShieldCheck class="text-primary size-4" />
                                Secure payment processing
                            </p>
                            <p class="tm-body-sm flex items-center gap-2">
                                <Truck class="text-primary size-4" />
                                Fast nationwide delivery
                            </p>
                            <p class="tm-body-sm flex items-center gap-2">
                                <CheckCircle2 class="text-primary size-4" />
                                Easy size exchange support
                            </p>
                        </div>
                    </article>
                </aside>
            </div>
        </section>
    </StorefrontLayout>
</template>
