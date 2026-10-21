<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, BookOpen, Layers3, Sparkles, Users } from '@lucide/vue';
import CourseCard from '@/components/CourseCard.vue';
import { Button } from '@/components/ui/button';
import type { CourseSummary } from '@/types/course';
import { index as courses } from '@/routes/courses';

type Category = {
    name: string;
    slug: string;
    color: string;
    description?: string | null;
    courses_count: number;
};

defineProps<{
    featured: CourseSummary[];
    popular: CourseSummary[];
    categories: Category[];
    stats: { courses: number; learners: number; lessons: number };
}>();
</script>

<template>
    <Head title="Learn something good">
        <meta
            name="description"
            content="Friendly, practical courses for curious people. Learn at your own pace and keep every lesson for life."
        />
        <link rel="canonical" href="/" />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="Brightside" />
        <meta property="og:title" content="Brightside — Learn something good" />
        <meta
            property="og:description"
            content="Friendly, practical courses for curious people. Learn at your own pace and keep every lesson for life."
        />
        <meta property="og:url" content="/" />
        <meta name="twitter:card" content="summary_large_image" />
    </Head>

    <section class="relative overflow-hidden border-b">
        <div
            class="absolute inset-0 [background-image:radial-gradient(oklch(21%_0.02_285/0.16)_1px,transparent_1px)] [background-size:24px_24px] opacity-[0.35]"
            aria-hidden="true"
        />
        <div
            class="relative container-page grid gap-14 py-20 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:py-28"
        >
            <div>
                <p class="inline-flex items-center gap-2 eyebrow">
                    <Sparkles class="size-4 text-accent" />
                    A classroom for the curious
                </p>
                <h1
                    class="mt-6 max-w-[13ch] font-display text-[clamp(3rem,7vw,5.25rem)] leading-[0.98] font-semibold tracking-[-0.04em]"
                >
                    Learn something
                    <span class="relative inline-block text-primary">
                        good.
                        <svg
                            class="absolute -bottom-2 left-0 w-full text-accent"
                            viewBox="0 0 200 14"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M3 10c42-7 99-9 194-4"
                                stroke="currentColor"
                                stroke-width="6"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                </h1>
                <p
                    class="mt-7 max-w-lg text-lg leading-8 text-muted-foreground"
                >
                    Practical, friendly courses made to fit around real life.
                    Pick a path, go at your own pace, and actually finish.
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-3">
                    <Button size="lg" as-child>
                        <Link :href="courses()">
                            Browse courses
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                    <Button size="lg" variant="outline" as-child>
                        <Link href="/register">Start learning free</Link>
                    </Button>
                </div>
                <div
                    class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm text-muted-foreground"
                >
                    <span class="inline-flex items-center gap-2">
                        <BookOpen class="size-4 text-primary" />
                        {{ stats.courses }} courses
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <Users class="size-4 text-primary" />
                        {{ stats.learners }} learners
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <Layers3 class="size-4 text-primary" />
                        {{ stats.lessons }} lessons
                    </span>
                </div>
            </div>

            <div
                class="relative mx-auto w-full max-w-md lg:mx-0 lg:justify-self-end"
                aria-hidden="true"
            >
                <div
                    class="absolute -top-6 -left-6 size-24 rotate-[-8deg] rounded-3xl bg-warning"
                />
                <div
                    class="absolute -right-5 -bottom-7 size-32 rounded-full bg-success/80"
                />
                <div
                    class="relative -rotate-2 rounded-[1.75rem] border-2 bg-card p-4 shadow-[8px_8px_0_0_var(--foreground)]"
                >
                    <div
                        class="rounded-2xl bg-primary p-7 text-primary-foreground"
                    >
                        <p class="eyebrow text-primary-foreground/70">
                            Now playing
                        </p>
                        <p
                            class="mt-4 font-display text-3xl leading-tight font-semibold"
                        >
                            How ideas get<br />made.
                        </p>
                        <div
                            class="mt-8 flex items-center justify-between rounded-xl bg-white/15 p-3 text-xs font-medium"
                        >
                            <span>Lesson 04 · 12 min</span>
                            <span
                                class="flex size-8 items-center justify-center rounded-full bg-white text-primary"
                            >
                                ▸
                            </span>
                        </div>
                    </div>
                    <div
                        class="mt-4 flex items-center justify-between px-1 pb-1"
                    >
                        <div>
                            <p class="text-sm font-semibold">
                                Creative Foundations
                            </p>
                            <p class="text-xs text-muted-foreground">
                                8 lessons · 1h 40m
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-success/35 px-3 py-1 text-xs font-semibold"
                            >42%</span
                        >
                    </div>
                </div>
                <div
                    class="absolute -bottom-10 -left-8 rotate-3 rounded-2xl border-2 bg-accent px-5 py-4 text-accent-foreground shadow-[4px_4px_0_0_var(--foreground)]"
                >
                    <p class="font-display text-2xl font-semibold">
                        Nice work!
                    </p>
                    <p class="text-xs font-medium">You finished a chapter.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="container-page py-20">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="eyebrow">Find your thing</p>
                <h2
                    class="mt-3 font-display text-3xl font-semibold tracking-tight sm:text-4xl"
                >
                    A path for every kind of curious.
                </h2>
            </div>
            <Link
                :href="courses()"
                class="text-sm font-semibold text-primary underline underline-offset-4"
                >See all courses →</Link
            >
        </div>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Link
                v-for="category in categories"
                :key="category.slug"
                :href="`/courses?category=${category.slug}`"
                class="group rounded-[1.25rem] border bg-card p-6 transition hover:-translate-y-1 hover:shadow-soft"
            >
                <span
                    class="mb-6 flex size-11 items-center justify-center rounded-2xl text-lg font-semibold text-white"
                    :style="{ backgroundColor: category.color || '#3A4DFF' }"
                >
                    {{ category.name.charAt(0).toUpperCase() }}
                </span>
                <h3
                    class="font-display text-xl font-semibold group-hover:text-primary"
                >
                    {{ category.name }}
                </h3>
                <p
                    class="mt-2 line-clamp-2 text-sm leading-6 text-muted-foreground"
                >
                    {{
                        category.description ||
                        'Thoughtful lessons, made simple.'
                    }}
                </p>
                <p class="mt-5 font-mono text-xs text-muted-foreground">
                    {{ category.courses_count }}
                    {{ category.courses_count === 1 ? 'course' : 'courses' }}
                </p>
            </Link>
        </div>
    </section>

    <section v-if="featured.length" class="border-y bg-card py-20">
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <p class="eyebrow">Start here</p>
                    <h2
                        class="mt-3 font-display text-3xl font-semibold tracking-tight sm:text-4xl"
                    >
                        Featured courses, picked with care.
                    </h2>
                </div>
            </div>
            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <CourseCard
                    v-for="course in featured"
                    :key="course.id"
                    :course="course"
                    featured
                />
            </div>
        </div>
    </section>

    <section class="container-page py-20">
        <div class="flex flex-wrap items-end justify-between gap-6">
            <div>
                <p class="eyebrow">Popular right now</p>
                <h2
                    class="mt-3 font-display text-3xl font-semibold tracking-tight sm:text-4xl"
                >
                    What everyone's learning.
                </h2>
            </div>
            <Link
                :href="courses({ query: { sort: 'popular' } })"
                class="text-sm font-semibold text-primary underline underline-offset-4"
                >Browse popular →</Link
            >
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <CourseCard
                v-for="course in popular"
                :key="course.id"
                :course="course"
            />
        </div>
    </section>

    <section class="container-page pb-20">
        <div
            class="relative overflow-hidden rounded-[2rem] bg-primary px-8 py-14 text-primary-foreground sm:px-14 sm:py-16"
        >
            <div
                class="absolute -top-20 -right-16 size-72 rounded-full border-[40px] border-white/10"
                aria-hidden="true"
            />
            <div class="relative max-w-2xl">
                <p class="eyebrow text-primary-foreground/70">
                    No pressure, no rush
                </p>
                <h2
                    class="mt-4 font-display text-4xl font-semibold tracking-[-0.03em] sm:text-5xl"
                >
                    A little learning goes a long way.
                </h2>
                <p class="mt-5 text-lg leading-8 text-primary-foreground/80">
                    Every course is yours for life. Come back when you have ten
                    minutes or a whole afternoon.
                </p>
                <Button size="lg" variant="secondary" class="mt-8" as-child>
                    <Link href="/register">Create your free account</Link>
                </Button>
            </div>
        </div>
    </section>
</template>
