<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        title: string;
        message?: string;
        action?: string;
        href?: string;
    }>(),
    { message: '', action: '', href: '' },
);
</script>

<template>
    <div
        class="flex flex-col items-center justify-center rounded-[1.5rem] border border-dashed bg-card px-6 py-14 text-center"
    >
        <div
            class="mb-5 flex size-14 items-center justify-center rounded-2xl bg-accent/20"
        >
            <slot name="icon">
                <span class="font-display text-2xl font-semibold">✦</span>
            </slot>
        </div>
        <h2 class="font-display text-xl font-semibold">{{ title }}</h2>
        <p
            v-if="message"
            class="mt-2 max-w-sm text-sm leading-6 text-muted-foreground"
        >
            {{ message }}
        </p>
        <Link
            v-if="action && href"
            :href="href"
            class="mt-6 inline-flex h-10 items-center rounded-xl bg-primary px-5 text-sm font-semibold text-primary-foreground"
        >
            {{ action }}
        </Link>
        <slot />
    </div>
</template>
