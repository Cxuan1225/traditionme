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
import StorefrontLayout from '@/layouts/account/StorefrontLayout.vue';
import SettingsLayout from '@/layouts/settings/AccountSettingsLayout.vue';
import { disable, enable } from '@/routes/two-factor';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <StorefrontLayout>
        <Head title="Two-factor authentication" />

        <h1 class="sr-only">Two-factor authentication settings</h1>

        <SettingsLayout>
            <div
                class="space-y-6 rounded-[1.75rem] border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div
                    class="rounded-2xl border border-blue-200 bg-gradient-to-r from-sky-50 via-blue-50 to-indigo-100 p-5 dark:border-zinc-700 dark:from-zinc-800 dark:via-zinc-800 dark:to-zinc-800"
                >
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-blue-300 bg-white/80 px-3 py-1 text-xs font-bold tracking-[0.14em] text-blue-700 uppercase dark:border-zinc-600 dark:bg-zinc-900 dark:text-blue-300"
                    >
                        <ShieldEllipsis class="size-3.5" />
                        Protection
                    </div>
                    <Heading
                        variant="small"
                        title="Two-factor authentication"
                        description="Add one more proof of identity before account access."
                    />
                </div>

                <div class="grid gap-5 xl:grid-cols-[1.25fr_0.75fr]">
                    <div
                        v-if="!twoFactorEnabled"
                        class="flex flex-col items-start justify-start space-y-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        <Badge variant="destructive">Disabled</Badge>

                        <p class="text-muted-foreground">
                            Enable 2FA and confirm with your authenticator app
                            each time you sign in from a new session.
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
                                v-bind="enable.form()"
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
                        class="flex flex-col items-start justify-start space-y-4 rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        <Badge variant="default">Enabled</Badge>

                        <p class="text-muted-foreground">
                            Two-factor authentication is active. Keep recovery
                            codes safe and stored in a secure location.
                        </p>

                        <TwoFactorRecoveryCodes />

                        <div class="relative inline">
                            <Form
                                v-bind="disable.form()"
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
                        class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800/90"
                    >
                        <p
                            class="text-xs font-bold tracking-[0.14em] text-zinc-500 uppercase dark:text-zinc-400"
                        >
                            How it works
                        </p>
                        <div
                            class="mt-3 space-y-2 text-sm text-zinc-600 dark:text-zinc-300"
                        >
                            <p
                                class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60"
                            >
                                <span class="inline-flex items-center gap-2"
                                    ><Smartphone class="size-3.5" />Install an
                                    authenticator app.</span
                                >
                            </p>
                            <p
                                class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60"
                            >
                                Scan the QR code during setup.
                            </p>
                            <p
                                class="rounded-xl bg-zinc-100 px-3 py-2 dark:bg-zinc-700/60"
                            >
                                Enter one-time code to complete activation.
                            </p>
                        </div>
                    </aside>
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </StorefrontLayout>
</template>
