<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Mail, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const showPassword = ref<boolean>(false);
const fieldClass =
    'h-11 border-border bg-background pr-16 pl-10 font-medium text-foreground placeholder:text-muted-foreground focus-visible:ring-primary';
</script>

<template>
    <AuthBase
        title="Member Login"
        description="Access your account to manage orders, checkout faster, and track new arrivals."
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700"
        >
            {{ status }}
        </div>

        <Form
            :action="store().url"
            :method="store().method"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="space-y-5"
        >
            <section class="tm-shell p-5">
                <div class="mb-5 flex items-center gap-3">
                    <span
                        class="inline-flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground"
                    >
                        <ShieldCheck class="size-5" />
                    </span>
                    <div>
                        <h2
                            class="tm-display text-lg font-black text-foreground"
                        >
                            Secure Sign In
                        </h2>
                        <p class="text-xs font-medium text-muted-foreground">
                            Protected account access for Tradition Me members.
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="email" class="font-semibold text-foreground"
                            >Email address</Label
                        >
                        <div class="relative">
                            <Mail
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="email@example.com"
                                :class="fieldClass"
                            />
                        </div>
                        <InputError :message="errors.email" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label
                                for="password"
                                class="font-semibold text-foreground"
                                >Password</Label
                            >
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm font-bold !text-primary hover:!text-primary/80"
                                :tabindex="5"
                            >
                                Forgot password?
                            </TextLink>
                        </div>
                        <div class="relative">
                            <Lock
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Password"
                                :class="fieldClass"
                            />
                            <button
                                type="button"
                                class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="size-4" />
                                <Eye v-else class="size-4" />
                            </button>
                        </div>
                        <InputError :message="errors.password" />
                    </div>

                    <Label
                        for="remember"
                        class="flex items-center space-x-3 text-sm font-medium text-foreground"
                    >
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me on this device</span>
                    </Label>
                </div>
            </section>

            <Button
                type="submit"
                class="h-11 w-full rounded-xl bg-primary text-primary-foreground hover:bg-primary/90"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Log in
            </Button>

            <div
                v-if="canRegister"
                class="rounded-xl border border-border bg-secondary/60 px-4 py-3 text-center text-sm font-medium text-foreground"
            >
                New here?
                <TextLink
                    :href="register()"
                    :tabindex="5"
                    class="font-bold !text-primary hover:!text-primary/80"
                    >Create your account</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
