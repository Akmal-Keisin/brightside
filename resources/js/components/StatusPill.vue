<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    status?: string | null;
    label?: string | null;
    class?: string;
}>();

const tone = computed(() => {
    switch ((props.status ?? '').toLowerCase()) {
        case 'published':
        case 'paid':
        case 'passed':
        case 'settlement':
        case 'capture':
        case 'success':
        case 'active':
            return 'bg-success/12 text-success ring-success/20';
        case 'pending':
        case 'draft':
        case 'in_progress':
        case 'in progress':
            return 'bg-warning/15 text-warning-foreground ring-warning/25';
        case 'archived':
        case 'refunded':
        case 'partial_refund':
        case 'expired':
        case 'revoked':
            return 'bg-muted text-muted-foreground ring-border';
        case 'failed':
        case 'deny':
        case 'cancel':
        case 'cancelled':
        case 'failure':
            return 'bg-destructive/10 text-destructive ring-destructive/20';
        default:
            return 'bg-secondary text-secondary-foreground ring-border';
    }
});
</script>

<template>
    <span
        :class="
            cn(
                'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
                tone,
                props.class,
            )
        "
    >
        <span class="size-1.5 rounded-full bg-current" />
        {{ label ?? status ?? 'Unknown' }}
    </span>
</template>
