<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, ReceiptText } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';

type Order = {
    order_number: string;
    status: string;
    status_label: string;
    course_title: string;
    course_slug: string | null;
    amount: string;
    created_at: string;
    is_payable: boolean;
};

defineProps<{
    orders: {
        data: Order[];
        links: never[];
        from: number;
        to: number;
        total: number;
    };
}>();

const badge = (status: string) =>
    status === 'Paid'
        ? 'bg-success/25 text-foreground'
        : ['Pending'].includes(status)
          ? 'bg-warning/35 text-foreground'
          : 'bg-muted text-muted-foreground';
</script>

<template>
    <Head title="Orders" />

    <div class="container-page max-w-4xl py-10 lg:py-14">
        <p class="eyebrow">Receipts & history</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
            Your orders
        </h1>
        <p class="mt-2 text-sm text-muted-foreground">
            Every purchase, receipt, and payment status in one place.
        </p>

        <div v-if="orders.data.length" class="mt-8 space-y-3">
            <div
                v-for="order in orders.data"
                :key="order.order_number"
                class="rounded-2xl border bg-card p-5"
            >
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="font-semibold">
                                {{ order.course_title }}
                            </h2>
                            <span
                                class="rounded-full px-2.5 py-1 text-xs font-semibold"
                                :class="badge(order.status)"
                                >{{ order.status_label }}</span
                            >
                        </div>
                        <p class="mt-2 font-mono text-xs text-muted-foreground">
                            {{ order.order_number }} · {{ order.created_at }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-mono font-semibold">
                            {{ order.amount }}
                        </p>
                        <Link
                            :href="`/orders/${order.order_number}`"
                            class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-primary"
                        >
                            Details <ArrowRight class="size-3" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        <EmptyState
            v-else
            class="mt-8"
            title="No orders yet."
            message="When you buy a course, your receipt and payment status will live here."
            action="Find a course"
            href="/courses"
        >
            <template #icon><ReceiptText class="size-6" /></template>
        </EmptyState>

        <Pagination :paginator="orders" />
    </div>
</template>
