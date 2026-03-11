<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { getInitials } from '@/composables/useInitials';
import AdminLayout from '@/layouts/admin/Layout.vue';
import SettingsLayout from '@/layouts/settings/AdminSettingsLayout.vue';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { BreadcrumbItem } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit(),
    },
];

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
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <h1 class="sr-only">Profile settings</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    title="Profile information"
                    description="Update your name and email address"
                />

                <Form
                    :action="ProfileController.update().url"
                    :method="ProfileController.update().method"
                    class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }"
                >
                    <div
                        class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/70"
                    >
                        <p
                            class="text-sm font-semibold text-zinc-900 dark:text-zinc-100"
                        >
                            Profile photo
                        </p>
                        <div
                            class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center"
                        >
                            <Avatar
                                class="h-24 w-20 rounded-xl border border-zinc-200 dark:border-zinc-700"
                            >
                                <AvatarImage
                                    v-if="displayedAvatar"
                                    :src="displayedAvatar"
                                    :alt="user.name"
                                    class="object-cover object-center"
                                />
                                <AvatarFallback
                                    class="bg-zinc-900 text-sm font-bold text-zinc-100 dark:bg-zinc-100 dark:text-zinc-900"
                                >
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
                                <input
                                    v-if="removeAvatar"
                                    type="hidden"
                                    name="remove_avatar"
                                    value="1"
                                />
                                <InputError
                                    :message="
                                        errors.avatar || errors.remove_avatar
                                    "
                                />
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
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
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            A new verification link has been sent to your email
                            address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            :disabled="processing"
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
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AdminLayout>
</template>
