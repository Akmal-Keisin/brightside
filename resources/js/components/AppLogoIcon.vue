<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({ inheritAttrs: false });

type Props = { className?: HTMLAttributes['class'] };

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 32 32"
        fill="none"
        :class="className"
        v-bind="$attrs"
        aria-hidden="true"
    >
        <path
            d="M7 7.5c3.6-1.4 6.6-.9 9 1.1 2.4-2 5.4-2.5 9-1.1v16c-3.6-1.4-6.6-.9-9 1.1-2.4-2-5.4-2.5-9-1.1v-16Z"
            stroke="currentColor"
            stroke-width="2"
            stroke-linejoin="round"
        />
        <path d="M16 8.6v15.5" stroke="currentColor" stroke-width="2" />
        <path
            d="m25.5 4 .9 2.1 2.1.9-2.1.9-.9 2.1-.9-2.1-2.1-.9 2.1-.9.9-2.1Z"
            fill="currentColor"
        />
    </svg>
</template>
