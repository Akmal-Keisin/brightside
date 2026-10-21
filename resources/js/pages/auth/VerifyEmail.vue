<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { MailCheck } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        title: 'Verify your email',
        description: 'One quick step before you can start learning.',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Verify your email" />

    <div class="flex flex-col items-center gap-6 text-center">
        <div
            class="flex size-16 items-center justify-center rounded-full bg-accent text-accent-foreground"
        >
            <MailCheck class="size-8" :stroke-width="1.75" />
        </div>

        <div class="space-y-2">
            <p class="text-sm leading-6 text-muted-foreground">
                We sent a verification link to your inbox. Open it to activate
                your account, then come back to start learning.
            </p>
            <p v-if="status" class="text-sm font-medium text-success">
                {{ status }}
            </p>
        </div>

        <Form v-bind="send.form()" v-slot="{ processing }" class="w-full">
            <Button
                type="submit"
                variant="outline"
                class="w-full"
                :disabled="processing"
            >
                <Spinner v-if="processing" />
                Resend verification email
            </Button>
        </Form>

        <Form method="post" action="/logout" class="w-full">
            <Button type="submit" variant="ghost" class="w-full">
                Log out
            </Button>
        </Form>
    </div>
</template>
