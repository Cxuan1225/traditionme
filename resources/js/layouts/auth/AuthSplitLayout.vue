<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center bg-background px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <div
            class="relative hidden h-full flex-col bg-zinc-900 p-10 text-zinc-100 lg:flex dark:border-r"
        >
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_15%_15%,rgba(245,158,11,0.28),transparent_40%),radial-gradient(circle_at_80%_65%,rgba(239,68,68,0.24),transparent_45%),#111827]"
            />
            <Link
                :href="home()"
                class="relative z-20 flex items-center text-lg font-medium"
            >
                <AppLogoIcon class="mr-2 size-8 fill-current text-amber-300" />
                {{ name }}
            </Link>
            <div class="relative z-20 mt-auto">
                <p class="tm-kicker text-amber-200">Member Access</p>
                <p class="mt-3 max-w-sm text-sm text-zinc-300">
                    Sign in to manage orders, account settings, and secure
                    checkout history.
                </p>
            </div>
        </div>
        <div class="lg:p-8">
            <div
                class="tm-shell mx-auto flex w-full flex-col justify-center space-y-6 p-7 sm:w-[390px]"
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1
                        class="tm-display text-2xl font-black tracking-tight"
                        v-if="title"
                    >
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
