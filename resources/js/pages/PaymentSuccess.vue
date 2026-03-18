<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, ShieldCheck } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { toRinggit } from '@/composables/useCurrency';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import { show as orderShow } from '@/routes/account/orders';
import shop from '@/routes/shop';
import type { Order } from '@/types/order';

defineProps<{
    order: Order;
    verified: boolean;
}>();
</script>

<template>
    <Head
        :title="`Payment ${verified ? 'Successful' : 'Processing'} — ${order.number}`"
    />

    <StorefrontLayout>
        <section class="mx-auto max-w-2xl space-y-6">
            <article
                class="tm-section overflow-hidden border p-8 text-center"
                :class="
                    verified
                        ? 'border-emerald-200/80 bg-[radial-gradient(circle_at_top,hsl(144_70%_80%/.32),transparent_50%),linear-gradient(145deg,hsl(148_55%_96%),hsl(49_89%_96%)_50%,hsl(0_0%_100%)_100%)] dark:border-emerald-500/30 dark:bg-[radial-gradient(circle_at_top,hsl(152_70%_30%/.3),transparent_50%),linear-gradient(145deg,hsl(156_20%_15%),hsl(210_18%_12%)_50%,hsl(220_18%_10%)_100%)]'
                        : 'border-amber-200/80 bg-amber-50/80 dark:border-amber-500/30 dark:bg-amber-950/30'
                "
            >
                <div class="flex justify-center">
                    <div
                        class="flex size-16 items-center justify-center rounded-full"
                        :class="
                            verified
                                ? 'bg-emerald-100 dark:bg-emerald-900/40'
                                : 'bg-amber-100 dark:bg-amber-900/40'
                        "
                    >
                        <ShieldCheck
                            v-if="verified"
                            class="size-8 text-emerald-600 dark:text-emerald-400"
                        />
                        <CheckCircle2
                            v-else
                            class="size-8 text-amber-600 dark:text-amber-400"
                        />
                    </div>
                </div>

                <h1 class="tm-title-xl mt-5">
                    {{
                        verified ? 'Payment successful!' : 'Payment processing'
                    }}
                </h1>
                <p class="tm-body mt-3">
                    {{
                        verified
                            ? `Your payment for order ${order.number} has been confirmed.`
                            : `Your payment for order ${order.number} is being processed. You'll receive an email once it's confirmed.`
                    }}
                </p>

                <div
                    class="mt-5 flex flex-wrap items-center justify-center gap-3"
                >
                    <Badge
                        class="bg-emerald-600 text-white hover:bg-emerald-600"
                    >
                        {{ order.number }}
                    </Badge>
                    <span class="tm-chip">{{
                        toRinggit(order.summary.totalInSen)
                    }}</span>
                </div>
            </article>

            <div class="flex flex-col gap-3 sm:flex-row">
                <Link :href="orderShow({ order: order.id })" class="flex-1">
                    <Button variant="outline" class="w-full">
                        View order details
                    </Button>
                </Link>
                <Link :href="shop.index()" class="flex-1">
                    <Button class="w-full">Continue shopping</Button>
                </Link>
            </div>
        </section>
    </StorefrontLayout>
</template>
