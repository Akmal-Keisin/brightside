<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    CheckCircle2,
    Compass,
    Flame,
    PlayCircle,
    Sparkles,
    Trophy,
} from '@lucide/vue';
import CourseCard from '@/components/CourseCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import ProgressBar from '@/components/ProgressBar.vue';
import { Button } from '@/components/ui/button';
import type { CourseSummary } from '@/types/course';

type Enrollment = {
    id: number;
    progress_percent: number;
    completed_contents_count: number;
    total_contents_count: number;
    last_accessed_at: string | null;
    course: {
        title: string;
        slug: string;
        thumbnail_url: string | null;
        level: string;
        category: string | null;
    };
};

const props = defineProps<{
    stats: {
        active_courses: number;
        completed_courses: number;
        completed_lessons: number;
    };
    continueLearning: {
        enrollment_id: number;
        progress_percent: number;
        course: {
            title: string;
            slug: string;
            category: string | null;
            thumbnail_url: string | null;
        };
        next_content: { id: number; title: string } | null;
    } | null;
    enrollments: Enrollment[];
    recommended: CourseSummary[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="px-4 py-6 lg:px-8">
        <div class="mx-auto max-w-6xl">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Your learning</p>
                    <h1
                        class="mt-2 font-display text-3xl font-semibold tracking-tight sm:text-4xl"
                    >
                        Welcome back. Let's learn something.
                    </h1>
                </div>
                <Button variant="outline" as-child>
                    <Link :href="'/courses'"
                        ><Compass class="size-4" /> Browse courses</Link
                    >
                </Button>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border bg-card p-5">
                    <p class="eyebrow">In progress</p>
                    <p class="mt-2 font-display text-4xl font-semibold">
                        {{ stats.active_courses }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        active courses
                    </p>
                </div>
                <div class="rounded-2xl border bg-card p-5">
                    <p class="eyebrow">Finished</p>
                    <p class="mt-2 font-display text-4xl font-semibold">
                        {{ stats.completed_courses }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        courses completed
                    </p>
                </div>
                <div class="rounded-2xl border bg-card p-5">
                    <p class="eyebrow">Lessons done</p>
                    <p class="mt-2 font-display text-4xl font-semibold">
                        {{ stats.completed_lessons }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">all time</p>
                </div>
            </div>

            <section
                v-if="continueLearning"
                class="mt-8 overflow-hidden rounded-[1.5rem] border bg-foreground text-background"
            >
                <div
                    class="grid gap-8 p-6 sm:p-8 lg:grid-cols-[1fr_280px] lg:items-center"
                >
                    <div>
                        <p
                            class="flex items-center gap-2 eyebrow text-background/60"
                        >
                            <Flame class="size-4 text-accent" /> Pick up where
                            you left off
                        </p>
                        <h2
                            class="mt-4 font-display text-3xl font-semibold tracking-tight"
                        >
                            {{ continueLearning.course.title }}
                        </h2>
                        <p
                            v-if="continueLearning.next_content"
                            class="mt-2 text-sm text-background/70"
                        >
                            Next up: {{ continueLearning.next_content.title }}
                        </p>
                        <div class="mt-6 flex items-center gap-4">
                            <ProgressBar
                                :percent="continueLearning.progress_percent"
                                tone="success"
                                class="max-w-xs"
                            />
                            <span class="font-mono text-sm"
                                >{{ continueLearning.progress_percent }}%</span
                            >
                        </div>
                        <Button
                            class="mt-6"
                            size="lg"
                            variant="secondary"
                            as-child
                        >
                            <Link
                                :href="`/learning/${continueLearning.course.slug}`"
                            >
                                <PlayCircle class="size-4" /> Continue learning
                            </Link>
                        </Button>
                    </div>
                    <div class="hidden lg:block" aria-hidden="true">
                        <div class="rotate-3 rounded-2xl bg-background/10 p-4">
                            <div
                                class="flex aspect-[4/3] flex-col justify-between rounded-xl bg-accent p-5 text-accent-foreground"
                            >
                                <Sparkles class="size-6" />
                                <p
                                    class="font-display text-2xl leading-tight font-semibold"
                                >
                                    Small steps<br />count.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mt-10">
                <div class="flex items-end justify-between">
                    <h2 class="font-display text-2xl font-semibold">
                        Your courses
                    </h2>
                    <Link
                        href="/my-courses"
                        class="text-sm font-semibold text-primary"
                        >View all →</Link
                    >
                </div>

                <div
                    v-if="enrollments.length"
                    class="mt-5 grid gap-4 md:grid-cols-2"
                >
                    <Link
                        v-for="item in enrollments"
                        :key="item.id"
                        :href="`/learning/${item.course.slug}`"
                        class="group flex gap-4 rounded-2xl border bg-card p-4 transition hover:-translate-y-0.5 hover:shadow-soft"
                    >
                        <div
                            class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-primary/10"
                        >
                            <img
                                v-if="item.course.thumbnail_url"
                                :src="item.course.thumbnail_url"
                                :alt="item.course.title"
                                class="size-full object-cover"
                            />
                            <BookOpen v-else class="size-7 text-primary" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate font-semibold group-hover:text-primary"
                            >
                                {{ item.course.title }}
                            </p>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ item.completed_contents_count }}/{{
                                    item.total_contents_count
                                }}
                                lessons
                                <span v-if="item.course.category">
                                    · {{ item.course.category }}</span
                                >
                            </p>
                            <div class="mt-3 flex items-center gap-3">
                                <ProgressBar
                                    :percent="item.progress_percent"
                                    class="flex-1"
                                />
                                <span class="font-mono text-xs"
                                    >{{ item.progress_percent }}%</span
                                >
                            </div>
                        </div>
                    </Link>
                </div>
                <EmptyState
                    v-else
                    class="mt-5"
                    title="Your first course is waiting."
                    message="Browse the catalogue and pick something that sounds interesting. You can start with a free preview."
                    action="Browse courses"
                    href="/courses"
                >
                    <template #icon
                        ><Trophy class="size-6 text-accent-foreground"
                    /></template>
                </EmptyState>
            </section>

            <section v-if="recommended.length" class="mt-12">
                <div class="flex items-end justify-between">
                    <div>
                        <p class="eyebrow">A gentle nudge</p>
                        <h2 class="mt-2 font-display text-2xl font-semibold">
                            Something new to try
                        </h2>
                    </div>
                </div>
                <div class="mt-5 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    <CourseCard
                        v-for="course in recommended"
                        :key="course.id"
                        :course="course"
                    />
                </div>
            </section>
        </div>
    </div>
</template>
