<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    CheckCircle2,
    Clock3,
    RotateCcw,
    XCircle,
} from '@lucide/vue';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';

type Question = {
    id: number;
    question: string;
    points: number;
    options: { id: number; label: string }[];
};

const props = defineProps<{
    attempt: {
        id: number;
        status: string;
        score: number | null;
        passed: boolean | null;
        attempt_number: number;
        completed_at: string | null;
        deadline_at: string | null;
    };
    quiz: {
        passing_score: number;
        show_answers_after_submit: boolean;
        remaining_attempts: number | null;
        max_attempts: number | null;
    };
    content: { id: number; title: string };
    course: { title: string; slug: string };
    review: {
        question: string;
        selected: string | null;
        correct: string | null;
        is_correct: boolean;
    }[];
    inProgress: boolean;
    questions: Question[];
    answers: Record<number, number>;
}>();

const form = useForm<{
    answers: Record<number, number | null>;
    auto_submitted: boolean;
}>({
    answers: Object.fromEntries(
        props.questions.map((question) => [
            question.id,
            props.answers[question.id] ?? null,
        ]),
    ),
    auto_submitted: false,
});

const now = ref(Date.now());
let interval: ReturnType<typeof setInterval> | undefined;

const remaining = computed(() => {
    if (!props.attempt.deadline_at) return null;
    return Math.max(
        0,
        Math.floor(
            (new Date(props.attempt.deadline_at).getTime() - now.value) / 1000,
        ),
    );
});

const clock = computed(() => {
    if (remaining.value === null) return null;
    const minutes = Math.floor(remaining.value / 60);
    const seconds = remaining.value % 60;
    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
});

const submit = (auto = false) => {
    if (form.processing) return;
    form.auto_submitted = auto;
    form.post(`/learning/quiz-attempts/${props.attempt.id}/submit`, {
        preserveScroll: true,
    });
};

const answered = computed(
    () =>
        Object.values(form.answers).filter(
            (value) => value !== null && value !== undefined,
        ).length,
);

watch(remaining, (value) => {
    if (value === 0 && props.inProgress && !form.processing) {
        submit(true);
    }
});

if (props.inProgress && props.attempt.deadline_at) {
    interval = setInterval(() => (now.value = Date.now()), 1000);
}

onBeforeUnmount(() => interval && clearInterval(interval));
</script>

<template>
    <Head
        :title="
            inProgress
                ? `Quiz — ${content.title}`
                : `Quiz result — ${content.title}`
        "
    />

    <div class="container-page max-w-3xl py-10 lg:py-14">
        <Link
            :href="`/learning/${course.slug}/${content.id}`"
            class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-primary"
        >
            <ArrowLeft class="size-4" /> Back to {{ course.title }}
        </Link>

        <template v-if="inProgress">
            <div class="mt-7 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="eyebrow">
                        Quiz · attempt {{ attempt.attempt_number }}
                    </p>
                    <h1
                        class="mt-2 font-display text-3xl font-semibold tracking-tight sm:text-4xl"
                    >
                        {{ content.title }}
                    </h1>
                </div>
                <div
                    v-if="clock"
                    class="rounded-xl border bg-card px-4 py-2 text-center"
                >
                    <p class="eyebrow">Time left</p>
                    <p
                        class="font-mono text-xl font-semibold tabular-nums"
                        :class="
                            remaining !== null && remaining < 60
                                ? 'text-destructive'
                                : ''
                        "
                    >
                        {{ clock }}
                    </p>
                </div>
            </div>

            <div
                class="mt-4 flex items-center gap-3 text-sm text-muted-foreground"
            >
                <span>{{ answered }} of {{ questions.length }} answered</span>
                <span>·</span>
                <span>Pass at {{ quiz.passing_score }}%</span>
            </div>

            <form class="mt-8 space-y-5" @submit.prevent="submit(false)">
                <fieldset
                    v-for="(question, index) in questions"
                    :key="question.id"
                    class="rounded-[1.25rem] border bg-card p-5"
                >
                    <legend class="sr-only">{{ question.question }}</legend>
                    <div class="flex items-start gap-3">
                        <span
                            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-foreground font-mono text-xs text-background"
                            >{{ index + 1 }}</span
                        >
                        <div class="flex-1">
                            <p class="leading-6 font-medium">
                                {{ question.question }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ question.points }}
                                {{ question.points === 1 ? 'point' : 'points' }}
                            </p>
                            <div class="mt-4 space-y-2">
                                <label
                                    v-for="option in question.options"
                                    :key="option.id"
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 text-sm transition hover:bg-muted/60"
                                    :class="
                                        form.answers[question.id] === option.id
                                            ? 'border-primary bg-primary/5'
                                            : ''
                                    "
                                >
                                    <input
                                        v-model="form.answers[question.id]"
                                        type="radio"
                                        :name="`question-${question.id}`"
                                        :value="option.id"
                                        class="size-4 accent-primary"
                                    />
                                    {{ option.label }}
                                </label>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <p v-if="form.errors.answers" class="text-sm text-destructive">
                    {{ form.errors.answers }}
                </p>

                <div class="flex items-center justify-between gap-4 pt-2">
                    <p class="text-xs text-muted-foreground">
                        You can change answers until you submit.
                    </p>
                    <Button type="submit" size="lg" :disabled="form.processing">
                        <Clock3 class="size-4" /> Submit quiz
                    </Button>
                </div>
            </form>
        </template>

        <template v-else>
            <div class="mt-7 rounded-[1.5rem] border-2 bg-card p-8 text-center">
                <div
                    class="mx-auto flex size-20 items-center justify-center rounded-full"
                    :class="
                        attempt.passed ? 'bg-success/25' : 'bg-destructive/10'
                    "
                >
                    <CheckCircle2
                        v-if="attempt.passed"
                        class="size-10 text-success"
                    />
                    <XCircle v-else class="size-10 text-destructive" />
                </div>
                <p class="mt-5 eyebrow">
                    {{ attempt.passed ? 'Nice work' : 'Not quite yet' }}
                </p>
                <h1
                    class="mt-2 font-display text-3xl font-semibold tracking-tight"
                >
                    {{ attempt.passed ? 'You passed.' : 'Give it another go.' }}
                </h1>
                <p
                    class="mt-3 text-5xl font-semibold tabular-nums"
                    :class="
                        attempt.passed ? 'text-success' : 'text-destructive'
                    "
                >
                    {{ attempt.score }}%
                </p>
                <p class="mt-2 text-sm text-muted-foreground">
                    Pass mark: {{ quiz.passing_score }}% · attempt
                    {{ attempt.attempt_number }}
                </p>
                <div class="mt-7 flex flex-wrap justify-center gap-3">
                    <Button variant="outline" as-child>
                        <Link :href="`/learning/${course.slug}/${content.id}`"
                            >Back to lesson</Link
                        >
                    </Button>
                    <Button
                        v-if="
                            !attempt.passed &&
                            (quiz.remaining_attempts === null ||
                                quiz.remaining_attempts > 0)
                        "
                        as-child
                    >
                        <Link :href="`/learning/${course.slug}/${content.id}`"
                            ><RotateCcw class="size-4" /> Try again from the
                            lesson</Link
                        >
                    </Button>
                    <Button v-else-if="attempt.passed" as-child>
                        <Link :href="`/learning/${course.slug}`"
                            >Continue course</Link
                        >
                    </Button>
                </div>
            </div>

            <section
                v-if="
                    review.length &&
                    (attempt.passed || quiz.show_answers_after_submit)
                "
                class="mt-8"
            >
                <h2 class="font-display text-2xl font-semibold">
                    Your answers
                </h2>
                <div class="mt-4 space-y-3">
                    <div
                        v-for="(item, index) in review"
                        :key="index"
                        class="rounded-2xl border bg-card p-5"
                    >
                        <div class="flex gap-3">
                            <CheckCircle2
                                v-if="item.is_correct"
                                class="mt-0.5 size-5 shrink-0 text-success"
                            />
                            <XCircle
                                v-else
                                class="mt-0.5 size-5 shrink-0 text-destructive"
                            />
                            <div class="flex-1">
                                <p class="font-medium">{{ item.question }}</p>
                                <p class="mt-2 text-sm text-muted-foreground">
                                    Your answer:
                                    {{ item.selected || 'No answer' }}
                                </p>
                                <p
                                    v-if="item.correct && !item.is_correct"
                                    class="mt-1 text-sm font-medium text-success"
                                >
                                    Correct: {{ item.correct }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>
