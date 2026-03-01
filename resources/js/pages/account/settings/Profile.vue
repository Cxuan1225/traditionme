<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { BadgeCheck, MailCheck, UserRound } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { getInitials } from '@/composables/useInitials';
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
const avatarPreview = ref<string | null>(null);
const removeAvatar = ref<boolean>(false);

const displayedAvatar = computed<string | null>(() => {
    if (removeAvatar.value) {
        return avatarPreview.value;
    }

    return avatarPreview.value ?? user.value.avatar ?? null;
});

const handleAvatarChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (avatarPreview.value !== null) {
        URL.revokeObjectURL(avatarPreview.value);
        avatarPreview.value = null;
    }

    if (file === null) {
        return;
    }

    avatarPreview.value = URL.createObjectURL(file);
    removeAvatar.value = false;
};

onBeforeUnmount(() => {
    if (avatarPreview.value !== null) {
        URL.revokeObjectURL(avatarPreview.value);
    }
});
</script>

<template>
    <StorefrontLayout>
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile settings</h1>

        <SettingsLayout>
            <div class="rounded-[1.75rem] border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-6 rounded-2xl border border-rose-200 bg-gradient-to-r from-rose-50 via-orange-50 to-amber-100 p-5 dark:border-zinc-700 dark:from-zinc-800 dark:via-zinc-800 dark:to-zinc-800">
                    <div class="inline-flex items-center gap-2 rounded-full border border-rose-300 bg-white/80 px-3 py-1 text-xs font-bold tracking-[0.14em] text-rose-700 uppercase dark:border-zinc-600 dark:bg-zinc-900 dark:text-rose-300">
                        <UserRound class="size-3.5" />
                        Identity
                    </div>
                    <Heading
                        variant="small"
                        title="Profile information"
                        description="Keep your identity and contact details up to date for orders and notifications."
                    />
                </div>

                <div class="grid gap-5 xl:grid-cols-[1.25fr_0.75fr]">
                    <Form
                        v-bind="ProfileController.update.form()"
                        class="space-y-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Profile photo</p>
                            <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center">
                                <Avatar class="h-24 w-20 rounded-xl border border-zinc-200 dark:border-zinc-700">
                                    <AvatarImage
                                        v-if="displayedAvatar"
                                        :src="displayedAvatar"
                                        :alt="user.name"
                                        class="object-cover object-center"
                                    />
                                    <AvatarFallback class="bg-zinc-900 text-sm font-bold text-zinc-100 dark:bg-zinc-100 dark:text-zinc-900">
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="flex-1 space-y-2">
                                    <Input
                                        id="avatar"
                                        name="avatar"
                                        type="file"
                                        accept="image/png,image/jpeg,image/webp"
                                        class="cursor-pointer"
                                        @change="handleAvatarChange"
                                    />
                                    <Label
                                        v-if="user.avatar"
                                        class="inline-flex items-center gap-2 text-sm font-normal text-zinc-700 dark:text-zinc-300"
                                    >
                                        <input
                                            v-model="removeAvatar"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-zinc-300"
                                        />
                                        Remove current photo
                                    </Label>
                                    <input v-if="removeAvatar" type="hidden" name="remove_avatar" value="1" />
                                    <InputError :message="errors.avatar || errors.remove_avatar" />
                                </div>
                            </div>
                        </div>

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
                                >Save profile</Button
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
                        <p class="text-xs font-bold tracking-[0.14em] text-zinc-500 uppercase dark:text-zinc-400">Account card</p>
                        <div class="mt-3 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ user.name }}</p>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ user.email }}</p>
                            <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                                <BadgeCheck class="size-3.5" />
                                Account active
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                            <p class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60">
                                <span class="inline-flex items-center gap-2">
                                    <MailCheck class="size-3.5" />
                                    Keep this email for delivery updates.
                                </span>
                            </p>
                            <p class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60">
                                Use real profile details for smoother support verification.
                            </p>
                        </div>
                    </aside>
                </div>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </StorefrontLayout>
</template>
