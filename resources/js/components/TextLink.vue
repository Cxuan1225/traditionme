<script setup lang="ts">
import type { LinkComponentBaseProps, Method } from '@inertiajs/core';
import { Link } from '@inertiajs/vue3';

type Props = {
    href: LinkComponentBaseProps['href'];
    tabindex?: number;
    method?: Method;
    as?: string;
};

defineProps<Props>();
</script>

<template>
    <Link
        :href="href"
        :tabindex="tabindex"
        :method="method"
        :as="as"
        class="text-zinc-900 underline decoration-zinc-500 underline-offset-4 transition-colors duration-300 ease-out hover:text-zinc-700 hover:decoration-current! dark:text-zinc-100 dark:decoration-zinc-400 dark:hover:text-zinc-200"
    >
        <slot />
    </Link>
</template>
