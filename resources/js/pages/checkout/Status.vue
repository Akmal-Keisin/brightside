<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowRight,
    CheckCircle2,
    Clock3,
    CreditCard,
    XCircle,
} from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    order: {
        order_number: string;
        status: string;
        status_label: string;
        course_title: string;
        course_slug: string | null;
        amount: string;
        payment_url: string | null;
        is_payable: boolean;
        is_paid: boolean;
        expires_at: string | null;
    };
    canSimulate: boolean;
}>();

const simulating = ref(false);

const simulate = () => {
    simulating.value = true;
    router.post(
        `/dev/orders/${props.order.order_number}/pay`,
        {},
        { onFinish: () => (simulating.value = false) },
    );
};
</script>

<template>
    <Head title="Order status" />

    <div class="container-page max-w-xl py-14 lg:py-20">
        <div class="rounded-[1.75rem] border-2 bg-card p-8 text-center">
            <div
                class="mx-auto flex size-16 items-center justify-center rounded-full"
                :class="order.is_paid ? 'bg-success/25' : 'bg-warning/25'"
            >
                <CheckCircle2
                    v-if="order.is_paid"
                    class="size-8 text-success"
                />
                <Clock3 v-else class="size-8 text-warning" />
            </div>
            <p class="mt-5 eyebrow">Order {{ order.order_number }}</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
                {{
                    order.is_paid
                        ? "You're in. Happy learning."
                        : 'Waiting for payment.'
                }}
            </h1>
            <p class="mt-3 text-sm leading-6 text-muted-foreground">
                <template v-if="order.is_paid">
                    {{ order.course_title }} is now in your library with
                    lifetime access.
                </template>
                <template v-else>
                    Complete the payment to unlock {{ order.course_title }}.
                    This page will update when you return.
                </template>
            </p>

            <div class="mt-6 rounded-2xl bg-muted/60 p-4">
                <p class="text-xs text-muted-foreground">Total</p>
                <p class="font-display text-3xl font-semibold">
                    {{ order.amount }}
                </p>
                <span
                    class="mt-2 inline-block rounded-full px-3 py-1 text-xs font-semibold"
                    :class="order.is_paid ? 'bg-success/30' : 'bg-warning/40'"
                >
                    {{ order.status_label }}
                </span>
            </div>

            <div class="mt-6 flex flex-col gap-3">
                <Button
                    v-if="order.is_paid && order.course_slug"
                    size="lg"
                    as-child
                >
                    <Link :href="`/learning/${order.course_slug}`"
                        >Start learning <ArrowRight class="size-4"
                    /></Link>
                </Button>
                <template v-else>
                    <Button v-if="order.payment_url" size="lg" as-child>
                        <a :href="order.payment_url"
                            ><CreditCard class="size-4" /> Continue payment</a
                        >
                    </Button>
                    <Button
                        v-if="canSimulate"
                        variant="outline"
                        :disabled="simulating"
                        @click="simulate"
                    >
                        <Spinner v-if="simulating" />
                        Simulate successful payment (local)
                    </Button>
                </template>
                <Button variant="ghost" as-child>
                    <Link href="/orders">View all orders</Link>
                </Button>
            </div>
        </div>

        <div
            v-if="!order.is_paid"
            class="mt-6 flex items-start gap-3 rounded-2xl border bg-card p-4 text-sm text-muted-foreground"
        >
            <XCircle class="mt-0.5 size-4 shrink-0" />
            <p>
                If you closed the payment window, use “Continue payment”. Unpaid
                orders expire automatically.
            </p>
        </div>
    </div>
</template>
