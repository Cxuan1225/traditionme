<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Palette } from 'lucide-vue-next';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import AdminLayout from '@/layouts/admin/Layout.vue';
import SettingsLayout from '@/layouts/settings/AdminSettingsLayout.vue';
import { edit } from '@/routes/appearance';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: edit(),
    },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <h1 class="sr-only">Appearance settings</h1>

        <SettingsLayout>
            <div
                class="space-y-6 rounded-[1.75rem] border border-border bg-card/90 p-6 shadow-sm"
            >
                <div
                    class="rounded-2xl border border-primary/25 bg-gradient-to-r from-amber-50 via-orange-50 to-rose-100 p-5 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-900"
                >
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-primary/35 bg-white/80 px-3 py-1 text-xs font-bold tracking-[0.14em] text-primary uppercase dark:border-primary/50 dark:bg-zinc-900 dark:text-amber-300"
                    >
                        <Palette class="size-3.5" />
                        Theme
                    </div>
                    <Heading
                        variant="small"
                        title="Appearance settings"
                        description="Adjust theme and reading comfort for long administration sessions."
                    />
                </div>
                <div
                    class="rounded-2xl border border-border bg-background/60 p-4"
                >
                    <AppearanceTabs />
                </div>
            </div>
        </SettingsLayout>
    </AdminLayout>
</template>
