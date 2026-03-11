<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ShieldCheck,
    TicketPercent,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { toRinggit } from '@/composables/useCurrency';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import cartItems from '@/routes/cart/items';
import checkout from '@/routes/checkout';
import shop from '@/routes/shop';
import type { CartLine } from '@/types/order';

const props = withDefaults(
    defineProps<{
        lines: CartLine[];
    }>(),
    {
        lines: () => [],
    },
);

const couponInput = ref<string>('');
const couponMessage = ref<string>('');
const appliedCoupon = ref<string | null>(null);
const couponState = ref<'warning' | 'success' | 'danger'>('warning');

const itemCount = computed<number>(() =>
    props.lines.reduce((sum, line) => sum + line.quantity, 0),
);

const subtotalInSen = computed<number>(() =>
    props.lines.reduce(
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
    if (props.lines.length === 0) {
        return 0;
    }

    return subtotalInSen.value - discountInSen.value >= 20000 ? 0 : 1200;
});

const totalInSen = computed<number>(
    () => subtotalInSen.value - discountInSen.value + shippingInSen.value,
);

const updateQuantity = (line: CartLine, quantity: number): void => {
    router.patch(
        cartItems.update({ product: line.slug }),
        { quantity },
        { preserveScroll: true },
    );
};

const removeLine = (line: CartLine): void => {
    router.delete(cartItems.destroy({ product: line.slug }), {
        preserveScroll: true,
    });
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
                    summary.
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
                        v-if="lines.length === 0"
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
                                v-for="line in lines"
                                :key="line.id"
                                class="tm-list-item grid gap-3 sm:grid-cols-[86px_1fr_auto]"
                            >
                                <div
                                    class="tm-product-media h-21.5 bg-linear-to-br"
                                    :class="
                                        line.gradient ??
                                        'from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-700'
                                    "
                                >
                                    <img
                                        v-if="line.imageUrl"
                                        :src="line.imageUrl"
                                        :alt="line.name"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                </div>
                                <div class="min-w-0">
                                    <p class="tm-subtitle truncate">
                                        {{ line.name }}
                                    </p>
                                    <p class="tm-body-sm mt-1">
                                        {{ line.category }}
                                    </p>
                                    <p class="tm-title mt-2 text-primary">
                                        {{ toRinggit(line.unitPriceInSen) }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <div class="tm-qty-control">
                                        <button
                                            type="button"
                                            class="rounded-full px-2 py-1 text-sm font-bold"
                                            :aria-label="`Decrease quantity for ${line.name}`"
                                            @click="
                                                updateQuantity(
                                                    line,
                                                    line.quantity - 1,
                                                )
                                            "
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
                                            @click="
                                                updateQuantity(
                                                    line,
                                                    line.quantity + 1,
                                                )
                                            "
                                        >
                                            +
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        class="text-xs font-semibold text-red-600 transition hover:text-red-500"
                                        :aria-label="`Remove ${line.name} from cart`"
                                        @click="removeLine(line)"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="tm-panel p-5">
                        <div class="mb-3 flex items-center gap-2">
                            <TicketPercent class="size-4 text-primary" />
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
                            <div class="border-t border-border pt-3">
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

                        <Link :href="checkout.show()" class="mt-5 block">
                            <Button
                                class="w-full"
                                :disabled="lines.length === 0"
                            >
                                Proceed to checkout
                            </Button>
                        </Link>
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
                                <ShieldCheck class="size-4 text-primary" />
                                Secure payment processing
                            </p>
                            <p class="tm-body-sm flex items-center gap-2">
                                <Truck class="size-4 text-primary" />
                                Fast nationwide delivery
                            </p>
                            <p class="tm-body-sm flex items-center gap-2">
                                <CheckCircle2 class="size-4 text-primary" />
                                Easy size exchange support
                            </p>
                        </div>
                    </article>
                </aside>
            </div>
        </section>
    </StorefrontLayout>
</template>
