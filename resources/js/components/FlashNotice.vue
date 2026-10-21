<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CheckCircle2, Info, TriangleAlert, X } from '@lucide/vue';
import { computed, ref, watch } from 'vue';

const page = usePage();
const flash = computed(
    () => (page.props.flash ?? {}) as Record<string, string | undefined>,
);
const dismissed = ref<string | null>(null);

watch(flash, () => (dismissed.value = null), { deep: true });

const messages = computed(() =>
    (['success', 'error', 'info'] as const)
        .filter((key) => flash.value[key])
        .map((key) => ({
            key,
            message: flash.value[key] as string,
            icon:
                key === 'success'
                    ? CheckCircle2
                    : key === 'error'
                      ? TriangleAlert
                      : Info,
            classes:
                key === 'success'
                    ? 'border-success/60 bg-success/15'
                    : key === 'error'
                      ? 'border-destructive/40 bg-destructive/10'
                      : 'border-info/50 bg-info/10',
        })),
);
</script>

<template>
    <div v-if="messages.length" class="space-y-2">
        <div
            v-for="item in messages"
            v-show="dismissed !== item.key"
            :key="item.key"
            class="flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm font-medium"
            :class="item.classes"
            role="status"
        >
            <component :is="item.icon" class="mt-0.5 size-5 shrink-0" />
            <p class="flex-1 leading-6">{{ item.message }}</p>
            <button
                type="button"
                class="rounded-md p-1 opacity-60 hover:opacity-100"
                aria-label="Dismiss"
                @click="dismissed = item.key"
            >
                <X class="size-4" />
            </button>
        </div>
    </div>
</template>
