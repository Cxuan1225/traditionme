<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Verify email"
        description="Please verify your email address by clicking on the link we just emailed to you."
    >
        <Head title="Email verification" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-center text-sm font-semibold text-emerald-700"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <Form
            :action="send().url"
            :method="send().method"
            class="tm-shell space-y-6 p-5 text-center"
            v-slot="{ processing }"
        >
            <Button
                :disabled="processing"
                class="h-11 rounded-xl bg-primary px-6 text-primary-foreground hover:bg-primary/90"
            >
                <Spinner v-if="processing" />
                Resend verification email
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm font-semibold !text-primary hover:!text-primary/80"
            >
                Log out
            </TextLink>
        </Form>
    </AuthLayout>
</template>
