<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { KeyRound, ShieldCheck, TriangleAlert } from 'lucide-vue-next';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/Layout.vue';
import SettingsLayout from '@/layouts/settings/AdminSettingsLayout.vue';
import { edit } from '@/routes/user-password';
import type { BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: edit(),
    },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Password settings" />

        <h1 class="sr-only">Password settings</h1>

        <SettingsLayout>
            <div
                class="space-y-6 rounded-[1.75rem] border border-border bg-card/90 p-6 shadow-sm"
            >
                <div
                    class="rounded-2xl border border-primary/25 bg-gradient-to-r from-emerald-50 via-cyan-50 to-teal-100 p-5 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-900"
                >
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-primary/35 bg-white/80 px-3 py-1 text-xs font-bold tracking-[0.14em] text-primary uppercase dark:border-primary/50 dark:bg-zinc-900 dark:text-amber-300"
                    >
                        <KeyRound class="size-3.5" />
                        Credentials
                    </div>
                    <Heading
                        variant="small"
                        title="Update password"
                        description="Protect administrative access with a unique password rotation policy."
                    />
                </div>

                <Form
                    v-bind="PasswordController.update.form()"
                    :options="{
                        preserveScroll: true,
                    }"
                    reset-on-success
                    :reset-on-error="[
                        'password',
                        'password_confirmation',
                        'current_password',
                    ]"
                    class="space-y-6 rounded-2xl border border-border bg-background/60 p-5"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div class="grid gap-2">
                        <Label for="current_password">Current password</Label>
                        <Input
                            id="current_password"
                            name="current_password"
                            type="password"
                            class="mt-1 block h-11 w-full rounded-xl border-border bg-card"
                            autocomplete="current-password"
                            placeholder="Current password"
                        />
                        <InputError :message="errors.current_password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">New password</Label>
                        <Input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-1 block h-11 w-full rounded-xl border-border bg-card"
                            autocomplete="new-password"
                            placeholder="New password"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation"
                            >Confirm password</Label
                        >
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="mt-1 block h-11 w-full rounded-xl border-border bg-card"
                            autocomplete="new-password"
                            placeholder="Confirm password"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
                            class="h-11 rounded-full bg-primary px-6 text-primary-foreground hover:bg-primary/90"
                            data-test="update-password-button"
                            >Save password</Button
                        >

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="recentlySuccessful"
                                class="text-sm text-muted-foreground"
                            >
                                Saved.
                            </p>
                        </Transition>
                    </div>
                </Form>

                <aside
                    class="rounded-2xl border border-border bg-background/60 p-4"
                >
                    <p
                        class="text-xs font-bold tracking-[0.14em] text-muted-foreground uppercase"
                    >
                        Security checklist
                    </p>
                    <div class="mt-3 space-y-2 text-sm text-foreground/80">
                        <p class="rounded-xl bg-secondary px-3 py-2">
                            <span class="inline-flex items-center gap-2"
                                ><ShieldCheck class="size-3.5" />Use 12+
                                characters</span
                            >
                        </p>
                        <p class="rounded-xl bg-secondary px-3 py-2">
                            <span class="inline-flex items-center gap-2"
                                ><ShieldCheck class="size-3.5" />Mix letters,
                                numbers, symbols</span
                            >
                        </p>
                        <p class="rounded-xl bg-secondary px-3 py-2">
                            <span class="inline-flex items-center gap-2"
                                ><TriangleAlert class="size-3.5" />Avoid reused
                                credentials</span
                            >
                        </p>
                    </div>
                </aside>
            </div>
        </SettingsLayout>
    </AdminLayout>
</template>
