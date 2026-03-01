<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import SettingsLayout from '@/layouts/settings/AccountSettingsLayout.vue';
</script>

<template>
    <StorefrontLayout>
        <Head title="Password settings" />

        <h1 class="sr-only">Password settings</h1>

        <SettingsLayout>
            <div class="rounded-[1.75rem] border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-cyan-100 p-4 dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-800">
                    <Heading
                        variant="small"
                        title="Update password"
                        description="Strengthen your buyer account by setting a strong password and rotating it regularly."
                    />
                </div>

                <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
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
                        class="space-y-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-2">
                            <Label for="current_password">Current password</Label>
                            <Input
                                id="current_password"
                                name="current_password"
                                type="password"
                                class="mt-1 block h-11 w-full rounded-xl border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900"
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
                                class="mt-1 block h-11 w-full rounded-xl border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900"
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
                                class="mt-1 block h-11 w-full rounded-xl border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900"
                                autocomplete="new-password"
                                placeholder="Confirm password"
                            />
                            <InputError :message="errors.password_confirmation" />
                        </div>

                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="processing"
                                class="h-11 rounded-full bg-zinc-900 px-6 text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
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
                                    class="text-sm text-neutral-600"
                                >
                                    Saved.
                                </p>
                            </Transition>
                        </div>
                    </Form>

                    <aside class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800/90">
                        <p class="text-xs font-bold tracking-[0.14em] text-zinc-500 uppercase dark:text-zinc-400">Password rules</p>
                        <ul class="mt-3 space-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                            <li class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60">At least 12 characters.</li>
                            <li class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60">Mix uppercase, lowercase, number, and symbol.</li>
                            <li class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60">Avoid reuse from other apps.</li>
                        </ul>
                    </aside>
                </div>
            </div>
        </SettingsLayout>
    </StorefrontLayout>
</template>
