<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import SettingsLayout from '@/layouts/settings/AccountSettingsLayout.vue';
import { send } from '@/routes/verification';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <StorefrontLayout>
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile settings</h1>

        <SettingsLayout>
            <div class="rounded-[1.75rem] border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-100 p-4 dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-800">
                    <p class="text-xs font-bold tracking-[0.16em] text-amber-700 uppercase dark:text-amber-300">Buyer profile</p>
                    <Heading
                        variant="small"
                        title="Profile information"
                        description="Keep your identity and contact details up to date for orders and notifications."
                    />
                </div>

                <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
                    <Form
                        v-bind="ProfileController.update.form()"
                        class="space-y-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                class="mt-1 block h-11 w-full rounded-xl border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900"
                                name="name"
                                :default-value="user.name"
                                required
                                autocomplete="name"
                                placeholder="Full name"
                            />
                            <InputError class="mt-2" :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email">Email address</Label>
                            <Input
                                id="email"
                                type="email"
                                class="mt-1 block h-11 w-full rounded-xl border-zinc-300 bg-white dark:border-zinc-700 dark:bg-zinc-900"
                                name="email"
                                :default-value="user.email"
                                required
                                autocomplete="username"
                                placeholder="Email address"
                            />
                            <InputError class="mt-2" :message="errors.email" />
                        </div>

                        <div v-if="mustVerifyEmail && !user.email_verified_at" class="rounded-xl border border-amber-200 bg-amber-50 p-3 dark:border-amber-700/40 dark:bg-amber-900/20">
                            <p class="text-sm text-zinc-700 dark:text-zinc-200">
                                Your email address is unverified.
                                <Link
                                    :href="send()"
                                    as="button"
                                    class="font-semibold text-zinc-900 underline decoration-zinc-400 underline-offset-4 dark:text-zinc-100"
                                >
                                    Resend verification email.
                                </Link>
                            </p>

                            <div
                                v-if="status === 'verification-link-sent'"
                                class="mt-2 text-sm font-medium text-green-600"
                            >
                                A new verification link has been sent to your email address.
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="processing"
                                class="h-11 rounded-full bg-zinc-900 px-6 text-white hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                                data-test="update-profile-button"
                                >Save</Button
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
                        <p class="text-xs font-bold tracking-[0.14em] text-zinc-500 uppercase dark:text-zinc-400">Profile summary</p>
                        <p class="mt-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ user.name }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ user.email }}</p>
                        <div class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                            <p class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60">Use your legal name to avoid shipping issues.</p>
                            <p class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60">Keep email updated for order tracking and alerts.</p>
                        </div>
                    </aside>
                </div>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </StorefrontLayout>
</template>
