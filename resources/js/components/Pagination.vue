<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';

type Paginator = {
    links: { url: string | null; label: string; active: boolean }[];
    from: number | null;
    to: number | null;
    total: number;
};

defineProps<{ paginator: Paginator }>();

const label = (text: string) =>
    text
        .replace('&laquo;', '')
        .replace('&raquo;', '')
        .replace('Previous', '')
        .replace('Next', '')
        .trim();
</script>

<template>
    <nav
        v-if="paginator.links.length > 3"
        class="flex flex-wrap items-center justify-between gap-4 pt-8"
        aria-label="Pagination"
    >
        <p class="text-sm text-muted-foreground">
            Showing {{ paginator.from }}–{{ paginator.to }} of
            {{ paginator.total }}
        </p>
        <div class="flex items-center gap-1">
            <template v-for="(link, index) in paginator.links" :key="index">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border px-2 text-sm font-medium transition hover:bg-muted"
                    :class="
                        link.active
                            ? 'border-foreground bg-foreground text-background'
                            : 'bg-card'
                    "
                >
                    <ChevronLeft
                        v-if="link.label.includes('Previous')"
                        class="size-4"
                    />
                    <ChevronRight
                        v-else-if="link.label.includes('Next')"
                        class="size-4"
                    />
                    <template v-else>{{ label(link.label) }}</template>
                </Link>
                <span
                    v-else
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border px-2 text-sm text-muted-foreground opacity-45"
                >
                    <ChevronLeft
                        v-if="link.label.includes('Previous')"
                        class="size-4"
                    />
                    <ChevronRight
                        v-else-if="link.label.includes('Next')"
                        class="size-4"
                    />
                    <template v-else>{{ label(link.label) }}</template>
                </span>
            </template>
        </div>
    </nav>
</template>
