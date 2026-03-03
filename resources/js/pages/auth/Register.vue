<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Eye, EyeOff, Lock, Mail, UserRound } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';

const showPassword = ref<boolean>(false);
const showPasswordConfirmation = ref<boolean>(false);
const fieldClass =
    'h-11 border-border bg-background pr-16 pl-10 font-medium text-foreground placeholder:text-muted-foreground focus-visible:ring-primary';
</script>

<template>
    <AuthBase
        title="Create Member Account"
        description="Set up your Tradition Me profile for faster checkout and order tracking."
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="space-y-5"
        >
            <section class="tm-shell p-5">
                <div class="mb-5">
                    <h2 class="tm-display text-lg font-black text-foreground">
                        Account Details
                    </h2>
                    <p class="text-xs font-medium text-muted-foreground">
                        Use real details so delivery and account recovery stay
                        accurate.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name" class="font-semibold text-foreground"
                            >Name</Label
                        >
                        <div class="relative">
                            <UserRound
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                id="name"
                                type="text"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="name"
                                name="name"
                                placeholder="Full name"
                                :class="fieldClass"
                            />
                        </div>
                        <InputError :message="errors.name" />
                    </div>

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
                                required
                                :tabindex="2"
                                autocomplete="email"
                                name="email"
                                placeholder="email@example.com"
                                :class="fieldClass"
                            />
                        </div>
                        <InputError :message="errors.email" />
                    </div>

                    <div class="space-y-2">
                        <Label
                            for="password"
                            class="font-semibold text-foreground"
                            >Password</Label
                        >
                        <div class="relative">
                            <Lock
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                :tabindex="3"
                                autocomplete="new-password"
                                name="password"
                                placeholder="Create password"
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

                    <div class="space-y-2">
                        <Label
                            for="password_confirmation"
                            class="font-semibold text-foreground"
                            >Confirm password</Label
                        >
                        <div class="relative">
                            <Lock
                                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            />
                            <Input
                                id="password_confirmation"
                                :type="
                                    showPasswordConfirmation
                                        ? 'text'
                                        : 'password'
                                "
                                required
                                :tabindex="4"
                                autocomplete="new-password"
                                name="password_confirmation"
                                placeholder="Repeat password"
                                :class="fieldClass"
                            />
                            <button
                                type="button"
                                class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                @click="
                                    showPasswordConfirmation =
                                        !showPasswordConfirmation
                                "
                            >
                                <EyeOff
                                    v-if="showPasswordConfirmation"
                                    class="size-4"
                                />
                                <Eye v-else class="size-4" />
                            </button>
                        </div>
                        <InputError :message="errors.password_confirmation" />
                    </div>
                </div>
            </section>

            <Button
                type="submit"
                class="h-11 w-full rounded-xl bg-primary text-primary-foreground hover:bg-primary/90"
                tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                Create account
            </Button>

            <div
                class="rounded-xl border border-border bg-secondary/60 px-4 py-3 text-center text-sm font-medium text-foreground"
            >
                Already have an account?
                <TextLink
                    :href="login()"
                    class="font-bold !text-primary hover:!text-primary/80"
                    :tabindex="6"
                >
                    Log in
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
