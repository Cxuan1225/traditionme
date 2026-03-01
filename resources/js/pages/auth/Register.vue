<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <AuthBase
        title="Create your account"
        description="Join Tradition Me for faster checkout and curated drops."
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="rounded-2xl border border-zinc-200 bg-white px-5 py-5 shadow-sm" data-aos="fade-up" data-aos-delay="80">
                <div class="grid gap-5">
                    <div class="grid gap-2">
                        <Label for="name" class="font-semibold text-zinc-800">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            name="name"
                            placeholder="Full name"
                            class="h-11 border-zinc-300 focus-visible:ring-amber-400"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email" class="font-semibold text-zinc-800">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            :tabindex="2"
                            autocomplete="email"
                            name="email"
                            placeholder="email@example.com"
                            class="h-11 border-zinc-300 focus-visible:ring-amber-400"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password" class="font-semibold text-zinc-800">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="Password"
                            class="h-11 border-zinc-300 focus-visible:ring-amber-400"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation" class="font-semibold text-zinc-800">Confirm password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Confirm password"
                            class="h-11 border-zinc-300 focus-visible:ring-amber-400"
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>

                    <Button
                        type="submit"
                        class="mt-2 h-11 w-full bg-zinc-900 text-zinc-100 hover:bg-zinc-800"
                        tabindex="5"
                        :disabled="processing"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="processing" />
                        Create account
                    </Button>
                </div>
            </div>

            <div class="text-center text-sm text-zinc-600" data-aos="fade-up" data-aos-delay="140">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="font-semibold text-amber-700 hover:text-amber-600"
                    :tabindex="6"
                    >Log in</TextLink>
            </div>

            <p class="text-center text-xs text-zinc-500" data-aos="fade-up" data-aos-delay="180">
                Your account helps you track orders, save preferences, and checkout faster.
            </p>
        </Form>
    </AuthBase>
</template>
