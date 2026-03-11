<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    BadgePercent,
    MapPinned,
    ShieldCheck,
} from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { toRinggit } from '@/composables/useCurrency';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import { malaysiaStateOptions } from '@/lib/malaysiaLocations';
import cart from '@/routes/cart';
import checkout from '@/routes/checkout';
import shop from '@/routes/shop';
import type { Auth } from '@/types';
import type { CheckoutCartLine } from '@/types/order';

const props = withDefaults(
    defineProps<{
        lines: CheckoutCartLine[];
    }>(),
    {
        lines: () => [],
    },
);

const page = usePage<{ auth?: Auth }>();
const auth = computed(() => page.props.auth);
const stateOptions = malaysiaStateOptions;

const form = useForm({
    shipping_name: auth.value?.user?.name ?? '',
    shipping_address: '',
    shipping_city: '',
    shipping_state: '',
    shipping_postcode: '',
    shipping_phone: '',
    coupon_code: '',
    notes: '',
});

const itemCount = computed<number>(() =>
    props.lines.reduce((sum, line) => sum + line.quantity, 0),
);

const subtotalInSen = computed<number>(() =>
    props.lines.reduce(
        (sum, line) => sum + line.unitPriceInSen * line.quantity,
        0,
    ),
);

const normalizedCoupon = computed<string>(() =>
    form.coupon_code.trim().toUpperCase(),
);

const discountInSen = computed<number>(() => {
    if (normalizedCoupon.value !== 'HERITAGE10') {
        return 0;
    }

    return Math.round(subtotalInSen.value * 0.1);
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

const availableCities = computed<string[]>(() => {
    const selectedState = stateOptions.find(
        ({ state }) => state === form.shipping_state,
    );

    return selectedState?.cities ?? [];
});

const couponHint = computed<string>(() => {
    if (normalizedCoupon.value === '') {
        return 'Use HERITAGE10 for 10% off.';
    }

    return normalizedCoupon.value === 'HERITAGE10'
        ? 'Coupon recognized. Discount will be applied when you place the order.'
        : 'Unknown coupon. The order will continue without a discount.';
});

const submit = (): void => {
    form.transform((data) => ({
        ...data,
        coupon_code: data.coupon_code.trim().toUpperCase(),
    })).post(checkout.store().url);
};

watch(
    () => form.shipping_state,
    (state, previousState) => {
        if (state === previousState) {
            return;
        }

        if (!availableCities.value.includes(form.shipping_city)) {
            form.shipping_city = '';
        }
    },
);
</script>

<template>
    <Head title="Checkout" />

    <StorefrontLayout>
        <section class="space-y-6">
            <article
                class="tm-section relative overflow-hidden border border-border/70 bg-[radial-gradient(circle_at_top_left,hsl(18_84%_82%/.35),transparent_35%),linear-gradient(135deg,hsl(36_70%_96%),hsl(25_60%_92%)_45%,hsl(0_0%_100%)_100%)] p-6 dark:bg-[radial-gradient(circle_at_top_left,hsl(15_65%_30%/.42),transparent_35%),linear-gradient(135deg,hsl(24_18%_16%),hsl(20_16%_12%)_45%,hsl(220_18%_10%)_100%)]"
            >
                <div
                    class="absolute inset-y-0 right-0 w-40 bg-[linear-gradient(180deg,transparent,hsl(18_85%_58%/.18),transparent)] blur-2xl"
                />
                <p class="tm-kicker relative text-primary">Secure Checkout</p>
                <div
                    class="relative mt-3 flex flex-wrap items-end justify-between gap-4"
                >
                    <div>
                        <h1 class="tm-title-xl max-w-3xl">
                            Confirm delivery details and place your heritage
                            order.
                        </h1>
                        <p class="tm-body mt-3 max-w-2xl">
                            Every order is reviewed for fit, finish, and
                            delivery readiness before it leaves our studio.
                        </p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="tm-stat min-w-28">
                            <p class="tm-kicker text-muted-foreground">Items</p>
                            <p class="tm-title mt-2">{{ itemCount }}</p>
                        </div>
                        <div class="tm-stat min-w-28">
                            <p class="tm-kicker text-muted-foreground">
                                Subtotal
                            </p>
                            <p class="tm-title mt-2">
                                {{ toRinggit(subtotalInSen) }}
                            </p>
                        </div>
                        <div class="tm-stat min-w-28">
                            <p class="tm-kicker text-muted-foreground">
                                Delivery
                            </p>
                            <p class="tm-title mt-2">
                                {{
                                    shippingInSen === 0
                                        ? 'Free'
                                        : toRinggit(shippingInSen)
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </article>

            <div class="grid gap-5 xl:grid-cols-[1.1fr_360px]">
                <form class="space-y-5" @submit.prevent="submit">
                    <article class="tm-panel overflow-hidden p-0">
                        <div class="border-b border-border/70 px-5 py-4">
                            <div class="flex items-center gap-2">
                                <MapPinned class="size-4 text-primary" />
                                <p class="tm-subtitle">Shipping details</p>
                            </div>
                            <p class="tm-body-sm mt-2">
                                We currently support nationwide delivery across
                                Malaysia.
                            </p>
                        </div>

                        <div class="grid gap-4 px-5 py-5 md:grid-cols-2">
                            <div class="tm-form-field md:col-span-2">
                                <label class="tm-label" for="shipping_name"
                                    >Full name</label
                                >
                                <Input
                                    id="shipping_name"
                                    v-model="form.shipping_name"
                                    class="tm-input-surface"
                                    autocomplete="name"
                                />
                                <p
                                    v-if="form.errors.shipping_name"
                                    class="tm-state-note tm-state-note-danger mt-2"
                                >
                                    {{ form.errors.shipping_name }}
                                </p>
                            </div>

                            <div class="tm-form-field md:col-span-2">
                                <label class="tm-label" for="shipping_address"
                                    >Street address</label
                                >
                                <textarea
                                    id="shipping_address"
                                    v-model="form.shipping_address"
                                    rows="4"
                                    class="tm-input-surface min-h-28 w-full rounded-3xl px-4 py-3 text-sm transition outline-none"
                                    autocomplete="street-address"
                                />
                                <p
                                    v-if="form.errors.shipping_address"
                                    class="tm-state-note tm-state-note-danger mt-2"
                                >
                                    {{ form.errors.shipping_address }}
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="tm-form-field">
                                        <label
                                            class="tm-label"
                                            for="shipping_state"
                                            >State</label
                                        >
                                        <Select v-model="form.shipping_state">
                                            <SelectTrigger
                                                id="shipping_state"
                                                class="tm-input-surface w-full justify-between"
                                                aria-label="Select shipping state"
                                            >
                                                <SelectValue
                                                    placeholder="Choose a Malaysian state"
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="option in stateOptions"
                                                    :key="option.state"
                                                    :value="option.state"
                                                >
                                                    {{ option.state }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p
                                            v-if="form.errors.shipping_state"
                                            class="tm-state-note tm-state-note-danger mt-2"
                                        >
                                            {{ form.errors.shipping_state }}
                                        </p>
                                    </div>

                                    <div class="tm-form-field">
                                        <label
                                            class="tm-label"
                                            for="shipping_city"
                                            >City</label
                                        >
                                        <Select
                                            v-model="form.shipping_city"
                                            :disabled="
                                                availableCities.length === 0
                                            "
                                        >
                                            <SelectTrigger
                                                id="shipping_city"
                                                class="tm-input-surface w-full justify-between"
                                                aria-label="Select shipping city"
                                            >
                                                <SelectValue
                                                    :placeholder="
                                                        availableCities.length ===
                                                        0
                                                            ? 'Choose state first'
                                                            : 'Choose a city'
                                                    "
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="city in availableCities"
                                                    :key="city"
                                                    :value="city"
                                                >
                                                    {{ city }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p
                                            v-if="form.errors.shipping_city"
                                            class="tm-state-note tm-state-note-danger mt-2"
                                        >
                                            {{ form.errors.shipping_city }}
                                        </p>
                                    </div>
                                </div>
                                <p
                                    v-if="
                                        !form.errors.shipping_state &&
                                        !form.errors.shipping_city
                                    "
                                    class="tm-body-sm mt-2 text-muted-foreground"
                                >
                                    {{
                                        availableCities.length === 0
                                            ? 'Select a state to load matching cities.'
                                            : 'City options are filtered by the selected state.'
                                    }}
                                </p>
                            </div>

                            <div class="tm-form-field">
                                <label class="tm-label" for="shipping_postcode"
                                    >Postcode</label
                                >
                                <Input
                                    id="shipping_postcode"
                                    v-model="form.shipping_postcode"
                                    class="tm-input-surface"
                                    autocomplete="postal-code"
                                />
                                <p
                                    v-if="form.errors.shipping_postcode"
                                    class="tm-state-note tm-state-note-danger mt-2"
                                >
                                    {{ form.errors.shipping_postcode }}
                                </p>
                            </div>

                            <div class="tm-form-field">
                                <label class="tm-label" for="shipping_phone"
                                    >Phone</label
                                >
                                <Input
                                    id="shipping_phone"
                                    v-model="form.shipping_phone"
                                    class="tm-input-surface"
                                    autocomplete="tel"
                                />
                                <p
                                    v-if="form.errors.shipping_phone"
                                    class="tm-state-note tm-state-note-danger mt-2"
                                >
                                    {{ form.errors.shipping_phone }}
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="tm-panel p-5">
                        <div class="flex items-center gap-2">
                            <BadgePercent class="size-4 text-primary" />
                            <p class="tm-subtitle">Offer and delivery notes</p>
                        </div>

                        <div class="mt-4 grid gap-4 md:grid-cols-[220px_1fr]">
                            <div class="tm-form-field">
                                <label class="tm-label" for="coupon_code"
                                    >Coupon code</label
                                >
                                <Input
                                    id="coupon_code"
                                    v-model="form.coupon_code"
                                    class="tm-input-surface uppercase"
                                    placeholder="HERITAGE10"
                                />
                                <p
                                    class="tm-state-note mt-2"
                                    :class="
                                        normalizedCoupon === ''
                                            ? 'tm-state-note-warning'
                                            : normalizedCoupon === 'HERITAGE10'
                                              ? 'tm-state-note-success'
                                              : 'tm-state-note-danger'
                                    "
                                >
                                    {{ couponHint }}
                                </p>
                            </div>

                            <div class="tm-form-field">
                                <label class="tm-label" for="notes"
                                    >Notes</label
                                >
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="4"
                                    class="tm-input-surface min-h-28 w-full rounded-3xl px-4 py-3 text-sm transition outline-none"
                                    placeholder="Gate code, preferred arrival window, or special handling request."
                                />
                                <p
                                    v-if="form.errors.notes"
                                    class="tm-state-note tm-state-note-danger mt-2"
                                >
                                    {{ form.errors.notes }}
                                </p>
                            </div>
                        </div>
                    </article>
                </form>

                <aside class="space-y-4 xl:sticky xl:top-24 xl:h-fit">
                    <article class="tm-panel p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="tm-subtitle">Order summary</p>
                            <span class="tm-chip"
                                >{{ itemCount }} piece(s)</span
                            >
                        </div>

                        <div class="mt-4 space-y-3">
                            <div
                                v-for="line in lines"
                                :key="line.id"
                                class="tm-list-item grid gap-3 sm:grid-cols-[72px_1fr_auto]"
                            >
                                <div
                                    class="tm-product-media h-18 bg-linear-to-br"
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
                                </div>
                                <div class="text-right">
                                    <p class="tm-body-sm">
                                        x{{ line.quantity }}
                                    </p>
                                    <p class="tm-title mt-1 text-primary">
                                        {{
                                            toRinggit(
                                                line.unitPriceInSen *
                                                    line.quantity,
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3 border-t border-border pt-4">
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
                            <div
                                class="tm-summary-row border-t border-border pt-3 text-base font-black"
                            >
                                <span>Total</span>
                                <span class="text-primary">{{
                                    toRinggit(totalInSen)
                                }}</span>
                            </div>
                        </div>

                        <Button
                            class="mt-5 w-full"
                            :disabled="form.processing || lines.length === 0"
                            @click="submit"
                        >
                            {{
                                form.processing
                                    ? 'Placing order...'
                                    : 'Place order'
                            }}
                        </Button>
                        <Link :href="cart.show()" class="mt-2 block">
                            <Button variant="outline" class="w-full"
                                >Back to cart</Button
                            >
                        </Link>
                    </article>

                    <article class="tm-panel p-5">
                        <div class="flex items-start gap-3">
                            <ShieldCheck class="mt-0.5 size-4 text-primary" />
                            <div>
                                <p class="tm-subtitle">Confidence checks</p>
                                <div class="mt-3 space-y-2">
                                    <p class="tm-body-sm">
                                        Free shipping unlocks at RM 200 after
                                        discounts.
                                    </p>
                                    <p class="tm-body-sm">
                                        Every line item is revalidated against
                                        active catalog data before order
                                        placement.
                                    </p>
                                    <p class="tm-body-sm">
                                        Confirmation details remain available in
                                        your account session immediately after
                                        checkout.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </article>

                    <article
                        v-if="form.errors.cart"
                        class="tm-panel border border-red-200 bg-red-50/80 p-5 dark:border-red-500/40 dark:bg-red-950/30"
                    >
                        <div class="flex items-start gap-3">
                            <AlertCircle class="mt-0.5 size-4 text-red-600" />
                            <div>
                                <p
                                    class="tm-subtitle text-red-700 dark:text-red-200"
                                >
                                    Cart update required
                                </p>
                                <p
                                    class="tm-body-sm mt-2 text-red-700/90 dark:text-red-100/80"
                                >
                                    {{ form.errors.cart }}
                                </p>
                            </div>
                        </div>
                    </article>

                    <article class="tm-section text-center">
                        <p class="tm-body-sm">
                            Need more pieces before checkout? Return to the
                            catalog and continue curating your set.
                        </p>
                        <Link :href="shop.index()" class="mt-4 inline-block">
                            <Button variant="outline">Continue shopping</Button>
                        </Link>
                    </article>
                </aside>
            </div>
        </section>
    </StorefrontLayout>
</template>
