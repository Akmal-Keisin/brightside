<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineOptions({
    layout: {
        title: 'Two-factor authentication',
        description: 'Confirm your identity to finish signing in.',
    },
});

const recovery = ref(false);
const form = useForm({ code: '', recovery_code: '' });

const submit = () => {
    form.post('/two-factor-challenge', {
        onFinish: () => form.reset('code', 'recovery_code'),
    });
};
</script>

<template>
    <Head title="Two-factor authentication" />

    <form class="flex flex-col gap-6" @submit.prevent="submit">
        <div v-if="!recovery" class="grid gap-2">
            <Label for="code">Authentication code</Label>
            <Input
                id="code"
                v-model="form.code"
                inputmode="numeric"
                autocomplete="one-time-code"
                autofocus
                placeholder="123456"
            />
            <InputError :message="form.errors.code" />
        </div>

        <div v-else class="grid gap-2">
            <Label for="recovery_code">Recovery code</Label>
            <Input
                id="recovery_code"
                v-model="form.recovery_code"
                autocomplete="one-time-code"
                autofocus
                placeholder="xxxxx-xxxxx"
            />
            <InputError :message="form.errors.recovery_code" />
        </div>

        <Button type="submit" class="w-full" :disabled="form.processing">
            <Spinner v-if="form.processing" />
            Continue
        </Button>

        <button
            type="button"
            class="text-sm font-medium text-primary underline underline-offset-4"
            @click="recovery = !recovery"
        >
            {{
                recovery ? 'Use an authentication code' : 'Use a recovery code'
            }}
        </button>
    </form>
</template>
