<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ClipboardList, Target, Trophy } from '@lucide/vue';
import { computed } from 'vue';
import StatusPill from '@/components/StatusPill.vue';
import { Button } from '@/components/ui/button';

type AttemptRow = {
    id: number;
    member: string;
    email: string;
    attempt_number: number;
    score: number | null;
    status: string;
    status_label: string;
    passed: boolean | null;
    completed_at: string | null;
    started_at: string;
};

const props = defineProps<{
    content: { id: number; title: string };
    course: { title: string; slug: string };
    quiz: { passing_score: number; max_attempts: number | null };
    stats: {
        attempts_count: number;
        completed_count: number;
        passed_count: number;
        pass_rate: number;
        average_score: number;
    };
    attempts: AttemptRow[];
}>();

const cards = computed(() => [
    {
        label: 'Attempts',
        value: props.stats.attempts_count.toLocaleString(),
        note: `${props.stats.completed_count} completed`,
        icon: ClipboardList,
    },
    {
        label: 'Pass rate',
        value: `${props.stats.pass_rate}%`,
        note: `${props.stats.passed_count} passed`,
        icon: Trophy,
    },
    {
        label: 'Average score',
        value: `${props.stats.average_score}%`,
        note: `Pass mark ${props.quiz.passing_score}%`,
        icon: Target,
    },
]);
</script>

<template>
    <Head :title="`Attempts — ${content.title}`" />

    <Button as-child variant="ghost" size="sm" class="-ml-3">
        <Link :href="`/admin/courses/${course.slug}/edit`">
            <ArrowLeft class="size-4" /> Back to course
        </Link>
    </Button>

    <div class="mt-4">
        <p class="eyebrow">Quiz report</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
            {{ content.title }}
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">
            {{ course.title }} ·
            {{
                quiz.max_attempts
                    ? `${quiz.max_attempts} attempts allowed`
                    : 'Unlimited attempts'
            }}
        </p>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div
            v-for="card in cards"
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

    <div class="mt-6 overflow-hidden rounded-card border bg-card shadow-soft">
        <div v-if="attempts.length" class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead
                    class="border-b bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"
                >
                    <tr>
                        <th class="px-5 py-3 font-medium">Member</th>
                        <th class="px-5 py-3 font-medium">Attempt</th>
                        <th class="px-5 py-3 font-medium">Score</th>
                        <th class="px-5 py-3 font-medium">Result</th>
                        <th class="px-5 py-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="attempt in attempts"
                        :key="attempt.id"
                        class="border-b last:border-0"
                    >
                        <td class="px-5 py-4">
                            <p class="font-medium">{{ attempt.member }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ attempt.email }}
                            </p>
                        </td>
                        <td class="px-5 py-4">#{{ attempt.attempt_number }}</td>
                        <td class="px-5 py-4 font-mono font-semibold">
                            {{
                                attempt.score === null
                                    ? '—'
                                    : `${attempt.score}%`
                            }}
                        </td>
                        <td class="px-5 py-4">
                            <StatusPill
                                :status="attempt.status"
                                :label="attempt.status_label"
                            />
                        </td>
                        <td class="px-5 py-4 text-muted-foreground">
                            {{ attempt.completed_at ?? attempt.started_at }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="px-6 py-16 text-center">
            <Trophy class="mx-auto size-8 text-muted-foreground" />
            <h2 class="mt-3 font-display text-lg font-semibold">
                No attempts yet
            </h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Learner attempts will appear here once this quiz is taken.
            </p>
        </div>
    </div>
</template>
