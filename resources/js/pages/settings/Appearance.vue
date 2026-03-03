<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
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
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <h1 class="sr-only">Appearance settings</h1>

        <SettingsLayout>
            <div class="tm-shell space-y-6 p-5 lg:p-6">
                <p class="tm-kicker text-primary">Display</p>
                <Heading
                    variant="small"
                    title="Appearance settings"
                    description="Update your account's appearance settings"
                />
                <div class="tm-subtle-card p-5">
                    <AppearanceTabs />
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
