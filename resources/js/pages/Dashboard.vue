<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-5 px-1 pb-2">
            <section class="tm-shell p-6">
                <p class="tm-kicker text-primary">Workspace overview</p>
                <h1 class="tm-page-title mt-2 text-foreground">Dashboard</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted-foreground">
                    All internal modules now follow the refreshed visual
                    language with warm editorial styling and stronger hierarchy.
                </p>
            </section>

            <section class="grid gap-4 md:grid-cols-3">
                <article class="tm-shell p-4">
                    <p class="tm-kicker text-muted-foreground">System</p>
                    <div
                        class="relative mt-3 aspect-video overflow-hidden rounded-2xl border border-border/70"
                    >
                        <PlaceholderPattern />
                    </div>
                </article>
                <article class="tm-shell p-4">
                    <p class="tm-kicker text-muted-foreground">Engagement</p>
                    <div
                        class="relative mt-3 aspect-video overflow-hidden rounded-2xl border border-border/70"
                    >
                        <PlaceholderPattern />
                    </div>
                </article>
                <article class="tm-shell p-4">
                    <p class="tm-kicker text-muted-foreground">Operations</p>
                    <div
                        class="relative mt-3 aspect-video overflow-hidden rounded-2xl border border-border/70"
                    >
                        <PlaceholderPattern />
                    </div>
                </article>
            </section>

            <section class="tm-shell p-4">
                <p class="tm-kicker text-muted-foreground">Activity stream</p>
                <div
                    class="relative mt-3 min-h-[340px] overflow-hidden rounded-2xl border border-border/70 md:min-h-[460px]"
                >
                    <PlaceholderPattern />
                </div>
            </section>
        </div>
    </AppLayout>
</template>
