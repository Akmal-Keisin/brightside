<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, Mail, RotateCcw } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import StatusPill from '@/components/StatusPill.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Order = {
    order_number: string;
    status: string;
    status_label: string;
    customer_name: string;
    customer_email: string;
    course_title: string;
    course_slug: string | null;
    subtotal: string;
    total: string;
    payment_provider: string | null;
    created_at: string;
    paid_at: string | null;
    notes: string | null;
    is_paid: boolean;
    is_refunded: boolean;
    user: { id: number; name: string; email: string } | null;
    enrollment: {
        id: number;
        progress_percent: number;
        revoked_at: string | null;
    } | null;
    payments: {
        provider: string;
        reference: string;
        status: string;
        method: string | null;
        amount: string;
        paid_at: string | null;
    }[];
};

const props = defineProps<{ order: Order }>();

const refunding = ref(false);
const refundForm = useForm({ reason: '' });
const receiptForm = useForm({});

const refund = () => {
    refundForm.post(`/admin/orders/${props.order.order_number}/refund`, {
        preserveScroll: true,
        onSuccess: () => {
            refunding.value = false;
            refundForm.reset();
        },
    });
};

const resend = () => {
    if (confirm(`Send the receipt to ${props.order.customer_email}?`)) {
        receiptForm.post(
            `/admin/orders/${props.order.order_number}/resend-receipt`,
            { preserveScroll: true },
        );
    }
};
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <div class="mx-auto max-w-4xl">
        <Button as-child variant="ghost" size="sm" class="-ml-3">
            <Link href="/admin/orders"
                ><ArrowLeft class="size-4" /> Back to orders</Link
            >
        </Button>

        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Order</p>
                <h1
                    class="mt-2 font-mono text-2xl font-semibold tracking-tight"
                >
                    {{ order.order_number }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Placed {{ order.created_at }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <StatusPill
                    :status="order.status"
                    :label="order.status_label"
                />
                <Button variant="outline" size="sm" @click="resend"
                    ><Mail class="size-3.5" /> Receipt</Button
                >
                <Button
                    v-if="order.is_paid"
                    variant="outline"
                    size="sm"
                    class="text-destructive"
                    @click="refunding = !refunding"
                >
                    <RotateCcw class="size-3.5" /> Refund
                </Button>
            </div>
        </div>

        <div
            v-if="refunding"
            class="mt-5 rounded-card border border-destructive/30 bg-destructive/5 p-4"
        >
            <form
                class="grid gap-4 sm:grid-cols-[1fr_auto]"
                @submit.prevent="refund"
            >
                <div class="grid gap-2">
                    <Label for="reason">Refund reason</Label>
                    <Input
                        id="reason"
                        v-model="refundForm.reason"
                        required
                        maxlength="500"
                        placeholder="Why is this being refunded?"
                    />
                    <InputError :message="refundForm.errors.reason" />
                </div>
                <div class="flex items-end">
                    <Button
                        type="submit"
                        variant="destructive"
                        :disabled="refundForm.processing"
                        >Refund order</Button
                    >
                </div>
            </form>
            <p class="mt-2 text-xs text-muted-foreground">
                Refunding revokes the learner's enrollment immediately.
            </p>
        </div>

        <div class="mt-6 grid gap-6 md:grid-cols-[1.4fr_1fr]">
            <section class="rounded-card border bg-card p-5 shadow-soft">
                <h2 class="font-display text-lg font-semibold">Purchase</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Course</dt>
                        <dd class="text-right font-medium">
                            <Link
                                v-if="order.course_slug"
                                :href="`/courses/${order.course_slug}`"
                                class="inline-flex items-center gap-1 hover:text-primary"
                            >
                                {{ order.course_title }}
                                <ExternalLink class="size-3" />
                            </Link>
                            <span v-else>{{ order.course_title }}</span>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Subtotal</dt>
                        <dd class="font-medium">{{ order.subtotal }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t pt-3">
                        <dt class="font-semibold">Total</dt>
                        <dd class="font-display text-lg font-semibold">
                            {{ order.total }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Paid at</dt>
                        <dd>{{ order.paid_at ?? 'Not paid' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Provider</dt>
                        <dd class="capitalize">
                            {{ order.payment_provider ?? '—' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Enrollment</dt>
                        <dd>
                            <span
                                v-if="
                                    order.enrollment &&
                                    !order.enrollment.revoked_at
                                "
                                >{{ order.enrollment.progress_percent }}%
                                complete</span
                            >
                            <span
                                v-else-if="order.enrollment"
                                class="text-destructive"
                                >Revoked</span
                            >
                            <span v-else>None</span>
                        </dd>
                    </div>
                </dl>
                <p
                    v-if="order.notes"
                    class="mt-4 rounded-control bg-muted p-3 text-sm text-muted-foreground"
                >
                    {{ order.notes }}
                </p>
            </section>

            <section class="rounded-card border bg-card p-5 shadow-soft">
                <h2 class="font-display text-lg font-semibold">Customer</h2>
                <p class="mt-3 font-medium">{{ order.customer_name }}</p>
                <p class="text-sm text-muted-foreground">
                    {{ order.customer_email }}
                </p>
                <Button
                    v-if="order.user"
                    as-child
                    variant="outline"
                    size="sm"
                    class="mt-4"
                >
                    <Link :href="`/admin/members/${order.user.id}`"
                        >View member profile</Link
                    >
                </Button>
                <p v-else class="mt-4 text-xs text-muted-foreground">
                    No registered account attached.
                </p>
            </section>
        </div>

        <section
            class="mt-6 overflow-hidden rounded-card border bg-card shadow-soft"
        >
            <div class="p-5">
                <h2 class="font-display text-lg font-semibold">
                    Payment attempts
                </h2>
            </div>
            <div v-if="order.payments.length" class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead
                        class="border-y bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"
                    >
                        <tr>
                            <th class="px-5 py-3 font-medium">Provider</th>
                            <th class="px-5 py-3 font-medium">Reference</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Method</th>
                            <th class="px-5 py-3 font-medium">Amount</th>
                            <th class="px-5 py-3 font-medium">Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(payment, index) in order.payments"
                            :key="index"
                            class="border-b last:border-0"
                        >
                            <td class="px-5 py-3 capitalize">
                                {{ payment.provider }}
                            </td>
                            <td class="px-5 py-3 font-mono text-xs">
                                {{ payment.reference }}
                            </td>
                            <td class="px-5 py-3">
                                <StatusPill
                                    :status="payment.status"
                                    :label="payment.status"
                                />
                            </td>
                            <td class="px-5 py-3">
                                {{ payment.method ?? '—' }}
                            </td>
                            <td class="px-5 py-3 font-medium">
                                {{ payment.amount }}
                            </td>
                            <td class="px-5 py-3 text-muted-foreground">
                                {{ payment.paid_at ?? '—' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p
                v-else
                class="border-t px-5 py-10 text-center text-sm text-muted-foreground"
            >
                No payment attempts recorded.
            </p>
        </section>
    </div>
</template>
