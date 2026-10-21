<script setup lang="ts">
withDefaults(
    defineProps<{
        percent: number;
        tone?: 'primary' | 'success' | 'accent';
    }>(),
    { tone: 'primary' },
);

const toneClass = {
    primary: 'bg-primary',
    success: 'bg-success',
    accent: 'bg-accent',
};
</script>

<template>
    <div
        class="h-2 w-full overflow-hidden rounded-full bg-muted"
        role="progressbar"
        :aria-valuenow="percent"
        aria-valuemin="0"
        aria-valuemax="100"
    >
        <div
            class="h-full rounded-full transition-all duration-500"
            :class="toneClass[tone]"
            :style="{ width: `${Math.min(100, Math.max(0, percent))}%` }"
        />
    </div>
</template>
