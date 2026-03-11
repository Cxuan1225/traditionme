<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import {
    ShieldBan,
    ShieldCheck,
    ShieldEllipsis,
    Smartphone,
} from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AdminLayout from '@/layouts/admin/Layout.vue';
import SettingsLayout from '@/layouts/settings/AdminSettingsLayout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import type { BreadcrumbItem } from '@/types';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Two-factor authentication',
        href: show(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbs">
        <Head title="Two-factor authentication" />

        <h1 class="sr-only">Two-factor authentication settings</h1>

        <SettingsLayout>
            <div
                class="space-y-6 rounded-[1.75rem] border border-border bg-card/90 p-6 shadow-sm"
            >
                <div
                    class="rounded-2xl border border-primary/25 bg-gradient-to-r from-sky-50 via-blue-50 to-indigo-100 p-5 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-900"
                >
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-primary/35 bg-white/80 px-3 py-1 text-xs font-bold tracking-[0.14em] text-primary uppercase dark:border-primary/50 dark:bg-zinc-900 dark:text-amber-300"
                    >
                        <ShieldEllipsis class="size-3.5" />
                        Protection
                    </div>
                    <Heading
                        variant="small"
                        title="Two-factor authentication"
                        description="Require an additional verification code before admin access."
                    />
                </div>

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4 rounded-2xl border border-border bg-background/60 p-5"
                >
                    <Badge variant="destructive">Disabled</Badge>

                    <p class="text-muted-foreground">
                        When you enable two-factor authentication, you will be
                        prompted for a secure pin during login. This pin can be
                        retrieved from a TOTP-supported application on your
                        phone.
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            class="rounded-full"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck />Continue setup
                        </Button>
                        <Form
                            v-else
                            :action="enable().url"
                            :method="enable().method"
                            @success="showSetupModal = true"
                            #default="{ processing }"
                        >
                            <Button
                                type="submit"
                                :disabled="processing"
                                class="rounded-full"
                            >
                                <ShieldCheck />Enable 2FA</Button
                            ></Form
                        >
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4 rounded-2xl border border-border bg-background/60 p-5"
                >
                    <Badge variant="default">Enabled</Badge>

                    <p class="text-muted-foreground">
                        With two-factor authentication enabled, you will be
                        prompted for a secure, random pin during login, which
                        you can retrieve from the TOTP-supported application on
                        your phone.
                    </p>

                    <TwoFactorRecoveryCodes />

                    <div class="relative inline">
                        <Form
                            :action="disable().url"
                            :method="disable().method"
                            #default="{ processing }"
                        >
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="processing"
                                class="rounded-full"
                            >
                                <ShieldBan />
                                Disable 2FA
                            </Button>
                        </Form>
                    </div>
                </div>

                <aside
                    class="rounded-2xl border border-border bg-background/60 p-4"
                >
                    <p
                        class="text-xs font-bold tracking-[0.14em] text-muted-foreground uppercase"
                    >
                        How it works
                    </p>
                    <div class="mt-3 space-y-2 text-sm text-foreground/80">
                        <p class="rounded-xl bg-secondary px-3 py-2">
                            <span class="inline-flex items-center gap-2"
                                ><Smartphone class="size-3.5" />Use an
                                authenticator app.</span
                            >
                        </p>
                        <p class="rounded-xl bg-secondary px-3 py-2">
                            Scan the setup QR code.
                        </p>
                        <p class="rounded-xl bg-secondary px-3 py-2">
                            Enter one-time code to complete activation.
                        </p>
                    </div>
                </aside>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AdminLayout>
</template>
