<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';

const props = defineProps<{ passwordRules: string }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Security settings', href: edit() }],
    },
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put('/settings/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};
</script>

<template>
    <Head title="Security settings" />

    <h1 class="sr-only">Security settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Update password"
            description="Use a long, unique password to keep your account safe."
        />

        <form class="space-y-6" @submit.prevent="updatePassword">
            <div class="grid gap-2">
                <Label for="current_password">Current password</Label>
                <PasswordInput
                    id="current_password"
                    v-model="passwordForm.current_password"
                    autocomplete="current-password"
                />
                <InputError :message="passwordForm.errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password">New password</Label>
                <PasswordInput
                    id="password"
                    v-model="passwordForm.password"
                    autocomplete="new-password"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="passwordForm.errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm new password</Label>
                <PasswordInput
                    id="password_confirmation"
                    v-model="passwordForm.password_confirmation"
                    autocomplete="new-password"
                    :passwordrules="props.passwordRules"
                />
                <InputError
                    :message="passwordForm.errors.password_confirmation"
                />
            </div>

            <Button
                :disabled="passwordForm.processing"
                data-test="update-password-button"
                >Save password</Button
            >
        </form>
    </div>
</template>
