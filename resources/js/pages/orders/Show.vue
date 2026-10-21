<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, CreditCard, Printer } from '@lucide/vue';
import { Button } from '@/components/ui/button';

defineProps<{
    order: {
        order_number: string;
        status: string;
        status_label: string;
        course_title: string;
        course_slug: string | null;
        customer_name: string;
        customer_email: string;
        subtotal: string;
        total: string;
        amount: string;
        created_at: string;
        paid_at: string | null;
        expires_at: string | null;
        payment_url: string | null;
        is_payable: boolean;
        payments: {
            status: string;
            method: string | null;
            amount: string;
            paid_at: string | null;
        }[];
    };
}>();

const printReceipt = () => window.print();
</script>

<template>
    <Head :title="`Order ${order.order_number}`" />

    <div class="container-page max-w-3xl py-10 lg:py-14">
        <Link
            href="/orders"
            class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-primary print:hidden"
        >
            <ArrowLeft class="size-4" /> All orders
        </Link>

        <div class="mt-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="eyebrow">Order details</p>
                <h1
                    class="mt-2 font-display text-3xl font-semibold tracking-tight"
                >
                    {{ order.course_title }}
                </h1>
                <p class="mt-2 font-mono text-xs text-muted-foreground">
                    {{ order.order_number }} · {{ order.created_at }}
                </p>
            </div>
            <span
                class="rounded-full bg-muted px-3 py-1.5 text-sm font-semibold"
                >{{ order.status_label }}</span
            >
        </div>

        <div class="mt-8 grid gap-5 sm:grid-cols-2">
            <div class="rounded-2xl border bg-card p-5">
                <p class="eyebrow">Customer</p>
                <p class="mt-3 font-medium">{{ order.customer_name }}</p>
                <p class="text-sm text-muted-foreground">
                    {{ order.customer_email }}
                </p>
            </div>
            <div class="rounded-2xl border bg-card p-5">
                <p class="eyebrow">Payment</p>
                <p class="mt-3 font-medium">{{ order.status_label }}</p>
                <p v-if="order.paid_at" class="text-sm text-muted-foreground">
                    Paid {{ order.paid_at }}
                </p>
                <p v-else class="text-sm text-muted-foreground">Not paid yet</p>
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border bg-card">
            <div class="border-b p-5">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Subtotal</span>
                    <span class="font-mono">{{ order.subtotal }}</span>
                </div>
                <div
                    class="mt-3 flex items-center justify-between border-t pt-3"
                >
                    <span class="font-semibold">Total</span>
                    <span class="font-display text-2xl font-semibold">{{
                        order.total
                    }}</span>
                </div>
            </div>
            <div v-if="order.payments.length" class="p-5">
                <p class="mb-3 eyebrow">Payment attempts</p>
                <div
                    v-for="(payment, index) in order.payments"
                    :key="index"
                    class="flex items-center justify-between gap-3 border-b py-2 text-sm last:border-0"
                >
                    <span class="capitalize">{{
                        payment.method || 'Payment'
                    }}</span>
                    <span class="text-muted-foreground">{{
                        payment.status
                    }}</span>
                    <span class="font-mono">{{ payment.amount }}</span>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3 print:hidden">
            <Button v-if="order.is_payable && order.payment_url" as-child>
                <a :href="order.payment_url"
                    ><CreditCard class="size-4" /> Continue payment</a
                >
            </Button>
            <Button
                v-if="order.status === 'Paid' && order.course_slug"
                as-child
            >
                <Link :href="`/learning/${order.course_slug}`"
                    >Go to course <ArrowRight class="size-4"
                /></Link>
            </Button>
            <Button variant="outline" @click="printReceipt">
                <Printer class="size-4" /> Print receipt
            </Button>
        </div>
    </div>
</template>
