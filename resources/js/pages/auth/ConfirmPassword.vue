<script setup lang="ts">
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import AdminLayout from '@/layouts/admin/Layout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';
import type { Auth } from '@/types';

const page = usePage<{ auth?: Auth }>();
const isAdminMode = computed(
    () =>
        page.props.auth?.isAdmin === true &&
        page.props.auth?.adminViewMode === 'admin',
);
</script>

<template>
    <Head title="Confirm password" />

    <AuthLayout
        v-if="!$page.props.auth?.user"
        title="Confirm your password"
        description="This is a secure area of the application. Please confirm your password before continuing."
    >
        <Form
            :action="store().url"
            :method="store().method"
            reset-on-success
            v-slot="{ errors, processing }"
            class="tm-shell p-5"
        >
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="current-password"
                        autofocus
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center">
                    <Button
                        class="h-11 w-full rounded-xl bg-primary text-primary-foreground hover:bg-primary/90"
                        :disabled="processing"
                        data-test="confirm-password-button"
                    >
                        <Spinner v-if="processing" />
                        Confirm password
                    </Button>
                </div>
            </div>
        </Form>
    </AuthLayout>

    <AdminLayout v-else-if="isAdminMode">
        <div class="mx-auto max-w-xl py-8">
            <Card class="tm-shell border-border bg-card/90">
                <CardHeader>
                    <CardTitle>Confirm your password</CardTitle>
                    <CardDescription>
                        Secure action detected. Confirm your password to
                        continue with two-factor settings.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Form
                        :action="store().url"
                        :method="store().method"
                        reset-on-success
                        v-slot="{ errors, processing }"
                        class="space-y-6"
                    >
                        <div class="grid gap-2">
                            <Label for="admin-password">Password</Label>
                            <Input
                                id="admin-password"
                                type="password"
                                name="password"
                                class="mt-1 block w-full"
                                required
                                autocomplete="current-password"
                                autofocus
                            />
                            <InputError :message="errors.password" />
                        </div>
                        <Button
                            class="h-11 w-full rounded-xl bg-primary text-primary-foreground hover:bg-primary/90"
                            :disabled="processing"
                            data-test="confirm-password-button"
                        >
                            <Spinner v-if="processing" />
                            Confirm password
                        </Button>
                    </Form>
                </CardContent>
            </Card>
        </div>
    </AdminLayout>

    <StorefrontLayout v-else>
        <div class="mx-auto max-w-xl py-8">
            <Card class="tm-shell border-border bg-card/90">
                <CardHeader>
                    <CardTitle>Confirm your password</CardTitle>
                    <CardDescription>
                        Secure action detected. Confirm your password to
                        continue with two-factor settings.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Form
                        :action="store().url"
                        :method="store().method"
                        reset-on-success
                        v-slot="{ errors, processing }"
                        class="space-y-6"
                    >
                        <div class="grid gap-2">
                            <Label for="account-password">Password</Label>
                            <Input
                                id="account-password"
                                type="password"
                                name="password"
                                class="mt-1 block w-full"
                                required
                                autocomplete="current-password"
                                autofocus
                            />
                            <InputError :message="errors.password" />
                        </div>
                        <Button
                            class="h-11 w-full rounded-xl bg-primary text-primary-foreground hover:bg-primary/90"
                            :disabled="processing"
                            data-test="confirm-password-button"
                        >
                            <Spinner v-if="processing" />
                            Confirm password
                        </Button>
                    </Form>
                </CardContent>
            </Card>
        </div>
    </StorefrontLayout>
</template>
