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
    'h-11 border-zinc-400 bg-white pr-16 pl-10 font-medium text-zinc-900 placeholder:text-zinc-600 focus-visible:ring-amber-500 dark:border-zinc-400 dark:bg-white dark:text-zinc-900 dark:placeholder:text-zinc-600';
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
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="space-y-5"
        >
            <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-200 dark:bg-white">
                <div class="mb-5 flex items-center gap-3">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-zinc-900 text-zinc-100">
                        <ShieldCheck class="size-5" />
                    </span>
                    <div>
                        <h2 class="text-lg font-black text-zinc-900 dark:text-zinc-900">Secure Sign In</h2>
                        <p class="text-xs font-medium text-zinc-700 dark:text-zinc-700">Protected account access for Tradition Me members.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="email" class="font-semibold text-zinc-800 dark:text-zinc-800">Email address</Label>
                        <div class="relative">
                            <Mail class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-700 dark:text-zinc-700" />
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
                            <Label for="password" class="font-semibold text-zinc-800 dark:text-zinc-800">Password</Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="!text-amber-800 text-sm font-bold hover:!text-amber-700 dark:!text-amber-800 dark:hover:!text-amber-700"
                                :tabindex="5"
                            >
                                Forgot password?
                            </TextLink>
                        </div>
                        <div class="relative">
                            <Lock class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-zinc-700 dark:text-zinc-700" />
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
                                class="absolute top-1/2 right-3 -translate-y-1/2 text-zinc-700 hover:text-zinc-900 dark:text-zinc-700 dark:hover:text-zinc-900"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="size-4" />
                                <Eye v-else class="size-4" />
                            </button>
                        </div>
                        <InputError :message="errors.password" />
                    </div>

                    <Label for="remember" class="flex items-center space-x-3 text-sm font-medium text-zinc-800 dark:text-zinc-800">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me on this device</span>
                    </Label>
                </div>
            </section>

            <Button
                type="submit"
                class="h-11 w-full rounded-xl bg-zinc-900 text-zinc-100 hover:bg-zinc-800"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Log in
            </Button>

            <div v-if="canRegister" class="rounded-xl border border-zinc-300 bg-zinc-100 px-4 py-3 text-center text-sm font-medium text-zinc-900 dark:border-zinc-300 dark:bg-zinc-100 dark:text-zinc-900">
                New here?
                <TextLink :href="register()" :tabindex="5" class="!text-amber-800 font-bold hover:!text-amber-700 dark:!text-amber-800 dark:hover:!text-amber-700">Create your account</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
