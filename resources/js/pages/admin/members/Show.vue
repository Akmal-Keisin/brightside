<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    CalendarDays,
    Mail,
    ShieldMinus,
} from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import StatusPill from '@/components/StatusPill.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

type EnrollmentRow = {
    id: number;
    course_title: string;
    course_slug: string;
    source: string;
    progress_percent: number;
    is_active: boolean;
    enrolled_at: string;
};

type Member = {
    id: number;
    name: string;
    email: string;
    headline: string | null;
    bio: string | null;
    joined_at: string;
    last_login_at: string | null;
    orders_count: number;
    enrollments: EnrollmentRow[];
};

const props = defineProps<{
    member: Member;
    courses: { id: number; title: string }[];
}>();

const grantForm = useForm({
    course_id: null as number | null,
    reason: '',
});

const grant = () => {
    grantForm.post(`/admin/members/${props.member.id}/enrollments`, {
        preserveScroll: true,
        onSuccess: () => grantForm.reset(),
    });
};

const revoke = (enrollment: EnrollmentRow) => {
    const reason = prompt(
        `Why are you revoking access to “${enrollment.course_title}”?`,
    );

    if (!reason?.trim()) {
        return;
    }

    router.delete(
        `/admin/enrollments/${enrollment.id}?reason=${encodeURIComponent(reason.trim())}`,
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head :title="member.name" />

    <div class="mx-auto max-w-4xl">
        <Button as-child variant="ghost" size="sm" class="-ml-3">
            <Link href="/admin/members"
                ><ArrowLeft class="size-4" /> Back to members</Link
            >
        </Button>

        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <span
                    class="flex size-14 items-center justify-center rounded-full bg-accent font-display text-lg font-semibold text-accent-foreground"
                >
                    {{
                        member.name
                            .split(' ')
                            .map((part) => part[0])
                            .slice(0, 2)
                            .join('')
                            .toUpperCase()
                    }}
                </span>
                <div>
                    <p class="eyebrow">Member</p>
                    <h1
                        class="mt-1 font-display text-3xl font-semibold tracking-tight"
                    >
                        {{ member.name }}
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ member.headline ?? 'No headline yet.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-card border bg-card p-4 shadow-soft">
                <Mail class="size-4 text-muted-foreground" />
                <p class="mt-3 text-sm font-medium break-all">
                    {{ member.email }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">Email address</p>
            </div>
            <div class="rounded-card border bg-card p-4 shadow-soft">
                <CalendarDays class="size-4 text-muted-foreground" />
                <p class="mt-3 text-sm font-medium">{{ member.joined_at }}</p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Joined · {{ member.orders_count }} orders
                </p>
            </div>
            <div class="rounded-card border bg-card p-4 shadow-soft">
                <BookOpen class="size-4 text-muted-foreground" />
                <p class="mt-3 text-sm font-medium">
                    {{
                        member.enrollments.filter(
                            (enrollment) => enrollment.is_active,
                        ).length
                    }}
                    active courses
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    Last seen {{ member.last_login_at ?? 'never' }}
                </p>
            </div>
        </div>

        <section class="mt-6 rounded-card border bg-card p-5 shadow-soft">
            <h2 class="font-display text-lg font-semibold">Enrollments</h2>
            <div v-if="member.enrollments.length" class="mt-4 divide-y">
                <div
                    v-for="enrollment in member.enrollments"
                    :key="enrollment.id"
                    class="flex flex-wrap items-center gap-3 py-3"
                >
                    <div class="min-w-0 flex-1">
                        <Link
                            :href="`/courses/${enrollment.course_slug}`"
                            class="font-medium hover:text-primary"
                            >{{ enrollment.course_title }}</Link
                        >
                        <p class="text-xs text-muted-foreground">
                            Enrolled {{ enrollment.enrolled_at }} ·
                            {{ enrollment.source }}
                        </p>
                    </div>
                    <div class="w-28">
                        <div
                            class="h-1.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-primary"
                                :style="{
                                    width: `${enrollment.progress_percent}%`,
                                }"
                            />
                        </div>
                        <p
                            class="mt-1 text-right text-xs text-muted-foreground"
                        >
                            {{ enrollment.progress_percent }}%
                        </p>
                    </div>
                    <StatusPill
                        :status="enrollment.is_active ? 'active' : 'revoked'"
                        :label="enrollment.is_active ? 'Active' : 'Revoked'"
                    />
                    <Button
                        v-if="enrollment.is_active"
                        variant="ghost"
                        size="sm"
                        class="text-destructive"
                        @click="revoke(enrollment)"
                    >
                        <ShieldMinus class="size-4" /> Revoke
                    </Button>
                </div>
            </div>
            <p v-else class="mt-4 text-sm text-muted-foreground">
                No enrollments yet.
            </p>
        </section>

        <section class="mt-6 rounded-card border bg-card p-5 shadow-soft">
            <h2 class="font-display text-lg font-semibold">Grant access</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Give this member a course without a purchase. This is recorded
                in the audit log.
            </p>
            <form
                class="mt-4 flex flex-wrap items-end gap-3"
                @submit.prevent="grant"
            >
                <div class="grid min-w-60 flex-1 gap-2">
                    <Label for="course_id">Course</Label>
                    <select
                        id="course_id"
                        v-model="grantForm.course_id"
                        required
                        class="h-9 rounded-control border bg-background px-3 text-sm"
                    >
                        <option :value="null" disabled>Choose a course</option>
                        <option
                            v-for="course in courses"
                            :key="course.id"
                            :value="course.id"
                        >
                            {{ course.title }}
                        </option>
                    </select>
                    <InputError :message="grantForm.errors.course_id" />
                </div>
                <div class="grid min-w-60 flex-1 gap-2">
                    <Label for="reason">Reason</Label>
                    <Input
                        id="reason"
                        v-model="grantForm.reason"
                        required
                        maxlength="500"
                        placeholder="Why does this member get access?"
                    />
                    <InputError :message="grantForm.errors.reason" />
                </div>
                <Button type="submit" :disabled="grantForm.processing"
                    >Grant enrollment</Button
                >
            </form>
        </section>

        <section
            v-if="member.bio"
            class="mt-6 rounded-card border bg-card p-5 shadow-soft"
        >
            <h2 class="font-display text-lg font-semibold">About</h2>
            <p class="mt-2 text-sm whitespace-pre-line text-muted-foreground">
                {{ member.bio }}
            </p>
        </section>
    </div>
</template>
