<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Check, Lock, ShieldCheck } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

defineProps<{
    course: {
        title: string;
        slug: string;
        subtitle: string | null;
        price: string;
        thumbnail_url: string | null;
        contents_count: number;
        duration_minutes: number;
        level: string;
    };
    customer: { name: string; email: string };
    gatewayConfigured: boolean;
}>();
</script>

<template>
    <Head title="Checkout" />

    <div class="container-page py-10 lg:py-14">
        <Link
            :href="`/courses/${course.slug}`"
            class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-primary"
        >
            <ArrowLeft class="size-4" /> Back to course
        </Link>

        <div class="mt-6 grid gap-8 lg:grid-cols-[1fr_400px]">
            <div>
                <p class="eyebrow">Checkout</p>
                <h1
                    class="mt-2 font-display text-3xl font-semibold tracking-tight sm:text-4xl"
                >
                    One last step.
                </h1>
                <p
                    class="mt-2 max-w-lg text-sm leading-6 text-muted-foreground"
                >
                    You'll be taken to our secure payment provider to finish.
                    Your course unlocks as soon as payment is confirmed.
                </p>

                <Form
                    :action="`/checkout/${course.slug}`"
                    method="post"
                    class="mt-8 space-y-6 rounded-[1.5rem] border bg-card p-6"
                    v-slot="{ processing }"
                >
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name">Name</Label>
                            <Input
                                id="name"
                                :default-value="customer.name"
                                disabled
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="email">Email</Label>
                            <Input
                                id="email"
                                type="email"
                                :default-value="customer.email"
                                disabled
                            />
                        </div>
                    </div>
                    <p class="text-xs leading-5 text-muted-foreground">
                        Receipt and course access are tied to this email. You
                        can change it later in your profile settings.
                    </p>

                    <div
                        v-if="!gatewayConfigured"
                        class="rounded-xl border border-warning/60 bg-warning/15 p-3 text-sm"
                    >
                        Payments are not configured on this environment yet.
                    </div>

                    <p class="text-xs leading-5 text-muted-foreground">
                        By completing this purchase you agree to our
                        <Link
                            href="/terms"
                            class="font-medium text-foreground underline underline-offset-4"
                            >terms of service</Link
                        >
                        and
                        <Link
                            href="/refund-policy"
                            class="font-medium text-foreground underline underline-offset-4"
                            >refund policy</Link
                        >. Courses include lifetime access. Refunds are
                        available within 7 days when less than 20% of the course
                        has been completed.
                    </p>

                    <Button
                        type="submit"
                        size="lg"
                        class="w-full"
                        :disabled="processing || !gatewayConfigured"
                    >
                        <Spinner v-if="processing" />
                        <Lock class="size-4" />
                        Pay {{ course.price }}
                    </Button>
                    <p
                        class="flex items-center justify-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <ShieldCheck class="size-3.5" /> Secure payment · no
                        card details stored
                    </p>
                </Form>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="overflow-hidden rounded-[1.5rem] border bg-card">
                    <div class="aspect-[16/9] bg-primary">
                        <img
                            v-if="course.thumbnail_url"
                            :src="course.thumbnail_url"
                            :alt="course.title"
                            class="size-full object-cover"
                        />
                        <div
                            v-else
                            class="flex size-full items-center justify-center p-6"
                        >
                            <p
                                class="text-center font-display text-2xl font-semibold text-primary-foreground"
                            >
                                {{ course.title }}
                            </p>
                        </div>
                    </div>
                    <div class="p-5">
                        <h2 class="font-display text-xl font-semibold">
                            {{ course.title }}
                        </h2>
                        <p
                            v-if="course.subtitle"
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            {{ course.subtitle }}
                        </p>
                        <ul class="mt-4 space-y-2 text-sm">
                            <li class="flex gap-2">
                                <Check class="mt-0.5 size-4 text-primary" />
                                {{ course.contents_count }} lessons
                            </li>
                            <li class="flex gap-2">
                                <Check class="mt-0.5 size-4 text-primary" />
                                {{ course.duration_minutes }} minutes of
                                learning
                            </li>
                            <li class="flex gap-2">
                                <Check class="mt-0.5 size-4 text-primary" />
                                Lifetime access
                            </li>
                        </ul>
                        <div class="mt-5 border-t pt-4">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="text-muted-foreground">Total</span>
                                <span
                                    class="font-display text-2xl font-semibold"
                                    >{{ course.price }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
