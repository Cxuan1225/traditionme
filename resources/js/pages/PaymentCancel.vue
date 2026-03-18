<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { XCircle } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { toRinggit } from '@/composables/useCurrency';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import { show as orderShow } from '@/routes/account/orders';
import { pay as orderPay } from '@/routes/orders';
import shop from '@/routes/shop';
import type { Order } from '@/types/order';

const props = defineProps<{
    order: Order;
}>();

function retryPayment(): void {
    const route = orderPay(props.order.id);
    router.post(route.url);
}
</script>

<template>
    <Head :title="`Payment Cancelled — ${order.number}`" />

    <StorefrontLayout>
        <section class="mx-auto max-w-2xl space-y-6">
            <article
                class="tm-section overflow-hidden border border-rose-200/80 bg-[radial-gradient(circle_at_top,hsl(0_70%_80%/.2),transparent_50%),linear-gradient(145deg,hsl(0_55%_97%),hsl(0_0%_100%)_100%)] p-8 text-center dark:border-rose-500/30 dark:bg-[radial-gradient(circle_at_top,hsl(0_70%_30%/.2),transparent_50%),linear-gradient(145deg,hsl(0_20%_15%),hsl(220_18%_10%)_100%)]"
            >
                <div class="flex justify-center">
                    <div
                        class="flex size-16 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/40"
                    >
                        <XCircle
                            class="size-8 text-rose-600 dark:text-rose-400"
                        />
                    </div>
                </div>

                <h1 class="tm-title-xl mt-5">Payment was not completed</h1>
                <p class="tm-body mt-3">
                    Your order {{ order.number }} has been reserved but payment
                    was not completed. You can retry the payment or return to
                    your order.
                </p>

                <div
                    class="mt-5 flex flex-wrap items-center justify-center gap-3"
                >
                    <Badge class="bg-amber-600 text-white hover:bg-amber-600">
                        {{ order.number }}
                    </Badge>
                    <span class="tm-chip">{{
                        toRinggit(order.summary.totalInSen)
                    }}</span>
                </div>
            </article>

            <div class="flex flex-col gap-3 sm:flex-row">
                <Button class="flex-1" @click="retryPayment">
                    Retry payment
                </Button>
                <Link :href="orderShow({ order: order.id })" class="flex-1">
                    <Button variant="outline" class="w-full">
                        View order
                    </Button>
                </Link>
                <Link :href="shop.index()" class="flex-1">
                    <Button variant="ghost" class="w-full">
                        Continue shopping
                    </Button>
                </Link>
            </div>
        </section>
    </StorefrontLayout>
</template>
