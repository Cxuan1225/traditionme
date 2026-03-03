<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(251,191,36,0.16),transparent_42%),radial-gradient(circle_at_bottom_right,rgba(239,68,68,0.12),transparent_35%)]"
        />
        <div class="flex w-full max-w-md flex-col gap-6">
            <Link
                :href="home()"
                class="flex items-center gap-2 self-center font-medium"
            >
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/15 text-primary"
                >
                    <AppLogoIcon class="size-8 fill-current" />
                </div>
                <span class="tm-display text-lg font-black text-foreground"
                    >Tradition Me</span
                >
            </Link>

            <div class="flex flex-col gap-6">
                <Card class="tm-shell rounded-3xl border-border/80">
                    <CardHeader class="px-10 pt-8 pb-0 text-center">
                        <CardTitle class="tm-display text-xl">{{
                            title
                        }}</CardTitle>
                        <CardDescription class="text-muted-foreground">
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-10 py-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
