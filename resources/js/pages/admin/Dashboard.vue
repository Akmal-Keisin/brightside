<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    BookOpen,
    CircleDollarSign,
    ClipboardList,
    Layers,
    Plus,
    ReceiptText,
    Users,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import StatusPill from '@/components/StatusPill.vue';
import { Button } from '@/components/ui/button';

type OrderRow = {
    order_number: string;
    customer: string;
    course: string;
    amount: string;
    status: string;
    status_label: string;
};

type CourseRow = {
    title: string;
    slug: string;
    status: string;
    enrollments_count: number;
    category: string | null;
};

const props = defineProps<{
    range: { from: string; to: string };
    stats: {
        revenue: string;
        paid_orders: number;
        orders: number;
        pending_orders: number;
        members: number;
        enrollments: number;
        completions: number;
        completion_rate: number;
        published_courses: number;
        draft_courses: number;
    };
    recent_orders: OrderRow[];
    top_courses: CourseRow[];
    enrollments_by_day: { day: string; total: number }[];
}>();

const from = ref(props.range.from.slice(0, 10));
const to = ref(props.range.to.slice(0, 10));

const applyRange = () => {
    router.get(
        '/admin',
        { from: from.value || undefined, to: to.value || undefined },
        { preserveState: true, replace: true },
    );
};

const chartMax = computed(() =>
    Math.max(1, ...props.enrollments_by_day.map((day) => day.total)),
);

const statCards = computed(() => [
    {
        label: 'Revenue',
        value: props.stats.revenue,
        note: `${props.stats.paid_orders.toLocaleString()} paid orders`,
        icon: CircleDollarSign,
    },
    {
        label: 'Paid orders',
        value: props.stats.paid_orders.toLocaleString(),
        note: `${props.stats.orders.toLocaleString()} orders placed`,
        icon: ReceiptText,
    },
    {
        label: 'Active learners',
        value: props.stats.enrollments.toLocaleString(),
        note: `${props.stats.members.toLocaleString()} members all time`,
        icon: Users,
    },
    {
        label: 'Completion rate',
        value: `${props.stats.completion_rate}%`,
        note: `${props.stats.completions.toLocaleString()} courses finished`,
        icon: BookOpen,
    },
]);

const dayLabel = (day: string) =>
    new Date(`${day}T00:00:00`).toLocaleDateString(undefined, {
        month: 'short',
        day: 'numeric',
    });
</script>

<template>
    <Head title="Admin studio" />

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Admin studio</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
                A clear view of the studio.
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Sales, learners, and course health at a glance.
            </p>
        </div>
        <Button as-child>
            <Link href="/admin/courses/create"
                ><Plus class="size-4" /> New course</Link
            >
        </Button>
    </div>

    <form
        class="mt-6 flex flex-wrap items-end gap-3 rounded-card border bg-card p-4 shadow-soft"
        @submit.prevent="applyRange"
    >
        <div class="grid gap-1.5">
            <label
                for="range-from"
                class="text-xs font-medium text-muted-foreground"
                >From</label
            >
            <input
                id="range-from"
                v-model="from"
                type="date"
                class="h-9 rounded-control border bg-background px-3 text-sm"
            />
        </div>
        <div class="grid gap-1.5">
            <label
                for="range-to"
                class="text-xs font-medium text-muted-foreground"
                >To</label
            >
            <input
                id="range-to"
                v-model="to"
                type="date"
                class="h-9 rounded-control border bg-background px-3 text-sm"
            />
        </div>
        <Button type="submit" variant="outline">Apply range</Button>
        <p class="ml-auto text-xs text-muted-foreground">
            Showing {{ range.from.slice(0, 10) }} → {{ range.to.slice(0, 10) }}
        </p>
    </form>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div
            v-for="card in statCards"
            :key="card.label"
            class="rounded-card border bg-card p-5 shadow-soft"
        >
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-medium text-muted-foreground">
                    {{ card.label }}
                </p>
                <span
                    class="flex size-9 items-center justify-center rounded-control bg-secondary text-secondary-foreground"
                >
                    <component :is="card.icon" class="size-4" />
                </span>
            </div>
            <p class="mt-4 font-display text-2xl font-semibold tracking-tight">
                {{ card.value }}
            </p>
            <p class="mt-1 text-xs text-muted-foreground">{{ card.note }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <section class="rounded-card border bg-card p-2 shadow-soft">
            <div class="flex items-center justify-between gap-4 px-4 pt-4">
                <div>
                    <h2 class="font-display text-lg font-semibold">
                        Recent orders
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        The latest activity from learners.
                    </p>
                </div>
                <Link
                    href="/admin/orders"
                    class="inline-flex items-center gap-1 text-sm font-medium text-primary"
                >
                    All orders <ArrowUpRight class="size-3.5" />
                </Link>
            </div>

            <div v-if="recent_orders.length" class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead
                        class="border-y bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-4 py-3 font-medium">Order</th>
                            <th class="px-4 py-3 font-medium">Learner</th>
                            <th class="px-4 py-3 font-medium">Course</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in recent_orders"
                            :key="order.order_number"
                            class="border-b last:border-0"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    :href="`/admin/orders/${order.order_number}`"
                                    class="font-mono text-xs font-medium hover:text-primary"
                                >
                                    {{ order.order_number }}
                                </Link>
                            </td>
                            <td class="px-4 py-3">{{ order.customer }}</td>
                            <td
                                class="max-w-56 truncate px-4 py-3 text-muted-foreground"
                            >
                                {{ order.course }}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ order.amount }}
                            </td>
                            <td class="px-4 py-3">
                                <StatusPill
                                    :status="order.status"
                                    :label="order.status_label"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p
                v-else
                class="px-4 py-10 text-center text-sm text-muted-foreground"
            >
                No orders yet.
            </p>
        </section>

        <section class="rounded-card border bg-card p-5 shadow-soft">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-lg font-semibold">
                        Enrollments
                    </h2>
                    <p class="text-sm text-muted-foreground">Last 14 days</p>
                </div>
                <Layers class="size-5 text-muted-foreground" />
            </div>

            <div class="mt-6 flex h-40 items-end gap-1.5">
                <div
                    v-for="day in enrollments_by_day"
                    :key="day.day"
                    class="group flex flex-1 flex-col items-center justify-end gap-2"
                >
                    <div class="relative flex w-full justify-center">
                        <span
                            class="pointer-events-none absolute -top-6 rounded bg-foreground px-1.5 py-0.5 text-[10px] font-medium text-background opacity-0 transition group-hover:opacity-100"
                            >{{ day.total }}</span
                        >
                        <div
                            class="w-full max-w-6 rounded-t bg-primary/80 transition group-hover:bg-primary"
                            :style="{
                                height: `${Math.max(4, (day.total / chartMax) * 120)}px`,
                            }"
                        />
                    </div>
                    <span class="text-[10px] text-muted-foreground">{{
                        dayLabel(day.day)
                    }}</span>
                </div>
            </div>
            <p
                v-if="!enrollments_by_day.length"
                class="mt-6 text-sm text-muted-foreground"
            >
                No enrollments in this window yet.
            </p>
        </section>
    </div>

    <section class="mt-6 rounded-card border bg-card p-2 shadow-soft">
        <div class="px-4 pt-4">
            <h2 class="font-display text-lg font-semibold">Top courses</h2>
            <p class="text-sm text-muted-foreground">
                Sorted by lifetime enrollments.
            </p>
        </div>

        <div v-if="top_courses.length" class="mt-4 divide-y">
            <div
                v-for="course in top_courses"
                :key="course.slug"
                class="flex flex-wrap items-center gap-4 px-4 py-3"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-control bg-secondary text-secondary-foreground"
                >
                    <ClipboardList class="size-4" />
                </span>
                <div class="min-w-0 flex-1">
                    <Link
                        :href="`/admin/courses/${course.slug}/edit`"
                        class="truncate text-sm font-medium hover:text-primary"
                        >{{ course.title }}</Link
                    >
                    <p class="text-xs text-muted-foreground">
                        {{ course.category ?? 'Uncategorised' }}
                    </p>
                </div>
                <StatusPill :status="course.status" />
                <span class="w-24 text-right text-sm font-medium"
                    >{{ course.enrollments_count }} learners</span
                >
            </div>
        </div>
        <p v-else class="px-4 py-10 text-center text-sm text-muted-foreground">
            Create your first course to see it here.
        </p>
    </section>
</template>
