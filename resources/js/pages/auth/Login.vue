<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Eye, EyeOff } from 'lucide-vue-next';
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
</script>

<template>
    <AuthBase
        title="Welcome back"
        description="Sign in to continue your Tradition Me experience."
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="rounded-2xl border border-zinc-200 bg-white px-5 py-5 shadow-sm" data-aos="fade-up" data-aos-delay="80">
                <div class="grid gap-5">
                    <div class="grid gap-2">
                        <Label for="email" class="font-semibold text-zinc-800">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="h-11 border-zinc-300 bg-white text-zinc-900 placeholder:text-zinc-500 focus-visible:ring-amber-400"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password" class="font-semibold text-zinc-800">Password</Label>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm font-semibold text-amber-700 hover:text-amber-600"
                                :tabindex="5"
                            >
                                Forgot password?
                            </TextLink>
                        </div>
                        <div class="relative">
                            <Input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="Password"
                                class="h-11 border-zinc-300 bg-white pr-16 text-zinc-900 placeholder:text-zinc-500 focus-visible:ring-amber-400"
                            />
                            <button
                                type="button"
                                class="absolute top-1/2 right-3 -translate-y-1/2 text-zinc-600 hover:text-zinc-900"
                                @click="showPassword = !showPassword"
                            >
                                <EyeOff v-if="showPassword" class="h-4 w-4" />
                                <Eye v-else class="h-4 w-4" />
                            </button>
                        </div>
                        <InputError :message="errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <Label for="remember" class="flex items-center space-x-3 text-zinc-700">
                            <Checkbox id="remember" name="remember" :tabindex="3" />
                            <span>Remember me</span>
                        </Label>
                    </div>

                    <Button
                        type="submit"
                        class="mt-2 h-11 w-full bg-zinc-900 text-zinc-100 hover:bg-zinc-800"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" />
                        Log in
                    </Button>
                </div>
            </div>

            <div
                class="text-center text-sm text-zinc-600"
                v-if="canRegister"
                data-aos="fade-up"
                data-aos-delay="140"
            >
                Don't have an account?
                <TextLink :href="register()" :tabindex="5" class="font-semibold text-amber-700 hover:text-amber-600">Create one</TextLink>
            </div>

            <p class="text-center text-xs text-zinc-500" data-aos="fade-up" data-aos-delay="180">
                By continuing, you agree to secure account access for Tradition Me services.
            </p>
        </Form>
    </AuthBase>
</template>
