<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineOptions({
    layout: {
        title: 'Create your account',
        description: 'Start learning with Brightside today.',
    },
});

defineProps<{
    passwordRules: string;
}>();
</script>

<template>
    <Head title="Create account" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="name">Your name</Label>
                <Input
                    id="name"
                    name="name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Rani Putri"
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                    placeholder="you@example.com"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Password</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="At least 10 characters"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm password</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Repeat your password"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <div class="grid gap-2">
                <Label for="terms" class="flex items-start gap-3 font-normal">
                    <Checkbox id="terms" name="terms" required class="mt-0.5" />
                    <span class="text-sm leading-6 text-muted-foreground">
                        I agree to the
                        <a
                            href="/terms"
                            class="font-medium text-foreground underline underline-offset-4"
                            >terms of service</a
                        >
                        and
                        <a
                            href="/privacy"
                            class="font-medium text-foreground underline underline-offset-4"
                            >privacy policy</a
                        >.
                    </span>
                </Label>
                <InputError :message="errors.terms" />
            </div>

            <Button type="submit" class="w-full" :disabled="processing">
                <Spinner v-if="processing" />
                Create account
            </Button>
        </div>

        <p class="text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink :href="login()">Log in</TextLink>
        </p>
    </Form>
</template>
