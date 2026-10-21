<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
import Pagination from '@/components/Pagination.vue';
import StatusPill from '@/components/StatusPill.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type OrderRow = {
    order_number: string;
    customer: string;
    email: string;
    course: string;
    amount: string;
    status: string;
    status_label: string;
    created_at: string;
};

const props = defineProps<{
    orders: {
        data: OrderRow[];
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    filters: { q: string; status: string };
    statuses: Record<string, string>;
}>();

const q = ref(props.filters.q);
const status = ref(props.filters.status);

const filter = () => {
    router.get(
        '/admin/orders',
        { q: q.value || undefined, status: status.value || undefined },
        { preserveState: true, replace: true },
    );
};
</script>

<template>
    <Head title="Orders" />

    <div>
        <p class="eyebrow">Admin studio</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
            Orders
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">
            Payments, refunds, and learner purchases.
        </p>
    </div>

    <form
        class="mt-6 flex flex-wrap items-center gap-3 rounded-card border bg-card p-3 shadow-soft"
        @submit.prevent="filter"
    >
        <div class="relative min-w-60 flex-1">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="q"
                class="pl-9"
                placeholder="Search order, name, or email…"
            />
        </div>
        <select
            v-model="status"
            class="h-9 rounded-control border bg-background px-3 text-sm"
            @change="filter"
        >
            <option value="">All statuses</option>
            <option
                v-for="(label, value) in statuses"
                :key="value"
                :value="value"
            >
                {{ label }}
            </option>
        </select>
        <Button type="submit" variant="outline">Filter</Button>
    </form>

    <div class="mt-6 overflow-hidden rounded-card border bg-card shadow-soft">
        <div v-if="orders.data.length" class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead
                    class="border-b bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"
                >
                    <tr>
                        <th class="px-5 py-3 font-medium">Order</th>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Course</th>
                        <th class="px-5 py-3 font-medium">Total</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Placed</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="order in orders.data"
                        :key="order.order_number"
                        class="border-b last:border-0 hover:bg-muted/20"
                    >
                        <td class="px-5 py-4">
                            <Link
                                :href="`/admin/orders/${order.order_number}`"
                                class="font-mono text-xs font-semibold hover:text-primary"
                                >{{ order.order_number }}</Link
                            >
                        </td>
                        <td class="px-5 py-4">
                            <p class="font-medium">{{ order.customer }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ order.email }}
                            </p>
                        </td>
                        <td
                            class="max-w-64 truncate px-5 py-4 text-muted-foreground"
                        >
                            {{ order.course }}
                        </td>
                        <td class="px-5 py-4 font-medium">
                            {{ order.amount }}
                        </td>
                        <td class="px-5 py-4">
                            <StatusPill
                                :status="order.status"
                                :label="order.status_label"
                            />
                        </td>
                        <td class="px-5 py-4 text-muted-foreground">
                            {{ order.created_at }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="px-5 py-16 text-center text-sm text-muted-foreground">
            No orders match those filters.
        </p>
    </div>

    <Pagination :paginator="orders" />
</template>
