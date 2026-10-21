<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    Check,
    ChevronDown,
    Clock3,
    FileText,
    HelpCircle,
    Lock,
    PlayCircle,
    Sparkles,
    Star,
    Users,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import CourseCard from '@/components/CourseCard.vue';
import ProgressBar from '@/components/ProgressBar.vue';
import { Button } from '@/components/ui/button';
import type { CourseSummary } from '@/types/course';

type Content = {
    id: number;
    title: string;
    type: string;
    estimated_minutes: number;
    is_free_preview: boolean;
    quiz_questions: number | null;
};
type Chapter = {
    id: number;
    title: string;
    description?: string | null;
    contents: Content[];
};
type Course = {
    title: string;
    slug: string;
    subtitle?: string | null;
    description: string;
    thumbnail_url?: string | null;
    thumbnail_alt?: string | null;
    price: string;
    price_display: string;
    level?: string;
    contents_count: number;
    duration_minutes: number;
    enrollments_count: number;
    rating_avg: number | null;
    rating_count: number;
    category?: { name: string; color?: string | null } | null;
    author?: { name: string } | null;
    chapters: Chapter[];
    learning_outcomes: string[];
    requirements: string[];
    trailer_url?: string | null;
    updated_at?: string | null;
    language?: string;
};

const props = defineProps<{
    course: Course;
    seo: {
        title: string;
        description: string;
        canonical: string;
        image: string | null;
        json_ld: Record<string, unknown>;
    };
    enrollment: { id: number; progress_percent: number } | null;
    isEnrolled: boolean;
    canCheckout: boolean;
    related: CourseSummary[];
}>();

let jsonLd: HTMLScriptElement | null = null;

onMounted(() => {
    document.getElementById('course-json-ld')?.remove();

    jsonLd = document.createElement('script');
    jsonLd.type = 'application/ld+json';
    jsonLd.id = 'course-json-ld';
    jsonLd.textContent = JSON.stringify(props.seo.json_ld);
    document.head.appendChild(jsonLd);
});

onBeforeUnmount(() => {
    jsonLd?.remove();
});

const page = usePage();
const user = computed(
    () => page.props.auth?.user as { is_admin?: boolean } | null,
);
const open = ref<number[]>(props.course.chapters.map((chapter) => chapter.id));
const totalLessons = computed(() =>
    props.course.chapters.reduce(
        (total, chapter) => total + chapter.contents.length,
        0,
    ),
);
const hours = computed(() => {
    const minutes = props.course.duration_minutes;
    return minutes >= 60
        ? `${Math.floor(minutes / 60)}h ${minutes % 60}m`
        : `${minutes}m`;
});

const iconFor = (type: string) =>
    type === 'Video' ? PlayCircle : type === 'Quiz' ? HelpCircle : FileText;

const toggle = (id: number) => {
    open.value = open.value.includes(id)
        ? open.value.filter((item) => item !== id)
        : [...open.value, id];
};

const enrollFree = () => {
    router.post(`/courses/${props.course.slug}/enroll`);
};
</script>

<template>
    <Head :title="seo.title">
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.canonical" />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="Brightside" />
        <meta property="og:title" :content="seo.title" />
        <meta property="og:description" :content="seo.description" />
        <meta property="og:url" :content="seo.canonical" />
        <meta v-if="seo.image" property="og:image" :content="seo.image" />
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="seo.title" />
        <meta name="twitter:description" :content="seo.description" />
        <meta v-if="seo.image" name="twitter:image" :content="seo.image" />
    </Head>

    <section class="border-b bg-card">
        <div
            class="container-page grid gap-10 py-12 lg:grid-cols-[1.15fr_0.85fr] lg:py-16"
        >
            <div>
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span
                        v-if="course.category"
                        class="rounded-full px-3 py-1 text-xs font-semibold text-white"
                        :style="{
                            backgroundColor: course.category.color || '#3A4DFF',
                        }"
                    >
                        {{ course.category.name }}
                    </span>
                    <span class="text-muted-foreground capitalize">{{
                        course.level
                    }}</span>
                    <span class="text-muted-foreground">·</span>
                    <span class="text-muted-foreground"
                        >Updated
                        {{
                            new Date(
                                course.updated_at || '',
                            ).toLocaleDateString('en', {
                                month: 'short',
                                year: 'numeric',
                            })
                        }}</span
                    >
                </div>

                <h1
                    class="mt-6 max-w-3xl font-display text-4xl leading-[1.08] font-semibold tracking-tight sm:text-5xl"
                >
                    {{ course.title }}
                </h1>
                <p
                    v-if="course.subtitle"
                    class="mt-4 max-w-2xl text-lg leading-8 text-muted-foreground"
                >
                    {{ course.subtitle }}
                </p>

                <div
                    class="mt-7 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-muted-foreground"
                >
                    <span class="inline-flex items-center gap-2">
                        <span
                            class="flex size-8 items-center justify-center rounded-full bg-primary/10 font-semibold text-primary"
                        >
                            {{ course.author?.name?.charAt(0) || 'B' }}
                        </span>
                        {{ course.author?.name || 'Brightside Studio' }}
                    </span>
                    <span
                        v-if="course.enrollments_count"
                        class="inline-flex items-center gap-1.5"
                    >
                        <Users class="size-4" />
                        {{ course.enrollments_count }} learners
                    </span>
                    <span
                        v-if="course.rating_avg"
                        class="inline-flex items-center gap-1.5"
                    >
                        <Star class="size-4 fill-accent text-accent" />
                        {{ course.rating_avg }} ({{ course.rating_count }})
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <Clock3 class="size-4" /> {{ hours }}
                    </span>
                </div>

                <div
                    v-if="isEnrolled && enrollment"
                    class="mt-8 max-w-md rounded-2xl border bg-background p-5"
                >
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium">Your progress</span>
                        <span class="font-mono"
                            >{{ enrollment.progress_percent }}%</span
                        >
                    </div>
                    <ProgressBar
                        :percent="enrollment.progress_percent"
                        class="mt-3"
                    />
                    <Button class="mt-4 w-full" as-child>
                        <Link :href="`/learning/${course.slug}`"
                            >Continue learning <ArrowRight class="size-4"
                        /></Link>
                    </Button>
                </div>
                <div v-else class="mt-8 flex flex-wrap gap-3">
                    <template v-if="canCheckout">
                        <Button size="lg" as-child>
                            <Link :href="`/checkout/${course.slug}`"
                                >Enrol for {{ course.price }}
                                <ArrowRight class="size-4"
                            /></Link>
                        </Button>
                    </template>
                    <template v-else-if="!user">
                        <Button size="lg" as-child>
                            <Link :href="`/checkout/${course.slug}`"
                                >Log in to enrol</Link
                            >
                        </Button>
                        <Button size="lg" variant="outline" as-child>
                            <Link
                                :href="`/register?redirect=/checkout/${course.slug}`"
                                >Create an account</Link
                            >
                        </Button>
                    </template>
                    <template v-else-if="course.price === 'Free'">
                        <Button size="lg" @click="enrollFree"
                            >Enrol free <ArrowRight class="size-4"
                        /></Button>
                    </template>
                    <Button v-else-if="user?.is_admin" size="lg" as-child>
                        <Link :href="`/admin/courses/${course.slug}/edit`"
                            >Manage this course</Link
                        >
                    </Button>
                </div>
                <p
                    v-if="!isEnrolled && course.price !== 'Free'"
                    class="mt-3 text-xs text-muted-foreground"
                >
                    One-time payment · lifetime access · 7-day refund window
                </p>
            </div>

            <div class="lg:pt-4">
                <div
                    class="overflow-hidden rounded-[1.5rem] border-2 bg-foreground shadow-[6px_6px_0_0_var(--accent)]"
                >
                    <div
                        v-if="course.thumbnail_url"
                        class="aspect-[16/10] bg-muted"
                    >
                        <img
                            :src="course.thumbnail_url"
                            :alt="course.thumbnail_alt || course.title"
                            class="size-full object-cover"
                        />
                    </div>
                    <div
                        v-else
                        class="aspect-[16/10] bg-primary p-8 text-primary-foreground"
                    >
                        <p class="eyebrow text-primary-foreground/70">
                            {{ course.category?.name }}
                        </p>
                        <p
                            class="mt-3 font-display text-3xl leading-tight font-semibold"
                        >
                            {{ course.title }}
                        </p>
                    </div>
                    <div
                        class="flex items-center justify-between bg-card px-5 py-4"
                    >
                        <div>
                            <p class="font-display text-2xl font-semibold">
                                {{ course.price_display || course.price }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                One payment, yours for life
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-success/30 px-3 py-1 text-xs font-semibold"
                            >No subscription</span
                        >
                    </div>
                </div>
                <ul class="mt-5 space-y-2.5 text-sm">
                    <li class="flex gap-2.5">
                        <Check class="mt-0.5 size-4 shrink-0 text-primary" />
                        {{ totalLessons }} lessons across
                        {{ course.chapters.length }} chapters
                    </li>
                    <li class="flex gap-2.5">
                        <Check class="mt-0.5 size-4 shrink-0 text-primary" />
                        Learn at your own pace, on any device
                    </li>
                    <li class="flex gap-2.5">
                        <Check class="mt-0.5 size-4 shrink-0 text-primary" />
                        Progress tracking and quizzes
                    </li>
                    <li class="flex gap-2.5">
                        <Check class="mt-0.5 size-4 shrink-0 text-primary" />
                        Lifetime access, including updates
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <div class="container-page grid gap-12 py-14 lg:grid-cols-[1fr_340px]">
        <div class="min-w-0">
            <section v-if="course.learning_outcomes.length">
                <h2 class="font-display text-2xl font-semibold">
                    What you'll learn
                </h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                    <li
                        v-for="outcome in course.learning_outcomes"
                        :key="outcome"
                        class="flex gap-3 rounded-2xl border bg-card p-4 text-sm leading-6"
                    >
                        <Check class="mt-0.5 size-4 shrink-0 text-primary" />
                        {{ outcome }}
                    </li>
                </ul>
            </section>

            <section class="mt-12">
                <h2 class="font-display text-2xl font-semibold">
                    About this course
                </h2>
                <div class="prose-bright mt-4 whitespace-pre-line">
                    {{ course.description }}
                </div>
            </section>

            <section class="mt-12">
                <div class="flex items-end justify-between">
                    <h2 class="font-display text-2xl font-semibold">
                        Course outline
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        {{ course.chapters.length }} chapters ·
                        {{ totalLessons }} lessons
                    </p>
                </div>
                <div
                    class="mt-5 divide-y overflow-hidden rounded-2xl border bg-card"
                >
                    <div
                        v-for="(chapter, index) in course.chapters"
                        :key="chapter.id"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center gap-4 px-5 py-4 text-left transition hover:bg-muted/50"
                            :aria-expanded="open.includes(chapter.id)"
                            @click="toggle(chapter.id)"
                        >
                            <span
                                class="font-mono text-xs text-muted-foreground"
                                >{{ String(index + 1).padStart(2, '0') }}</span
                            >
                            <span class="flex-1 font-semibold">{{
                                chapter.title
                            }}</span>
                            <span class="text-xs text-muted-foreground"
                                >{{ chapter.contents.length }} lessons</span
                            >
                            <ChevronDown
                                class="size-4 transition"
                                :class="
                                    open.includes(chapter.id)
                                        ? 'rotate-180'
                                        : ''
                                "
                            />
                        </button>
                        <ul
                            v-if="open.includes(chapter.id)"
                            class="border-t bg-background/60 px-5 py-2"
                        >
                            <li
                                v-for="content in chapter.contents"
                                :key="content.id"
                                class="flex items-center gap-3 py-2.5 text-sm"
                            >
                                <component
                                    :is="iconFor(content.type)"
                                    class="size-4 shrink-0 text-muted-foreground"
                                />
                                <span class="flex-1">{{ content.title }}</span>
                                <Link
                                    v-if="content.is_free_preview || isEnrolled"
                                    :href="
                                        isEnrolled
                                            ? `/learning/${course.slug}/${content.id}`
                                            : `/courses/${course.slug}/preview/${content.id}`
                                    "
                                    class="text-xs font-semibold text-primary"
                                >
                                    {{
                                        content.is_free_preview && !isEnrolled
                                            ? 'Preview'
                                            : 'Open'
                                    }}
                                </Link>
                                <span
                                    v-else
                                    class="text-xs text-muted-foreground"
                                    >{{ content.estimated_minutes }} min</span
                                >
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section v-if="course.requirements.length" class="mt-12">
                <h2 class="font-display text-2xl font-semibold">
                    Before you start
                </h2>
                <ul
                    class="mt-4 space-y-2 text-sm leading-6 text-muted-foreground"
                >
                    <li
                        v-for="requirement in course.requirements"
                        :key="requirement"
                        class="flex gap-2.5"
                    >
                        <span
                            class="mt-2 size-1.5 shrink-0 rounded-full bg-accent"
                        />
                        {{ requirement }}
                    </li>
                </ul>
            </section>

            <section
                v-if="course.author"
                class="mt-12 rounded-[1.5rem] border bg-card p-6"
            >
                <p class="eyebrow">Your guide</p>
                <div class="mt-4 flex items-start gap-4">
                    <span
                        class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-primary text-xl font-semibold text-primary-foreground"
                    >
                        {{ course.author.name.charAt(0) }}
                    </span>
                    <div>
                        <h2 class="font-display text-xl font-semibold">
                            {{ course.author.name }}
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-muted-foreground">
                            Part of the Brightside studio — making practical,
                            thoughtful courses that respect your time.
                        </p>
                    </div>
                </div>
            </section>
        </div>

        <aside class="hidden lg:block">
            <div class="sticky top-24 rounded-[1.25rem] border bg-card p-5">
                <p class="eyebrow">The essentials</p>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Level</dt>
                        <dd class="font-medium capitalize">
                            {{ course.level }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Lessons</dt>
                        <dd class="font-medium">{{ totalLessons }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Total time</dt>
                        <dd class="font-medium">{{ hours }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Language</dt>
                        <dd class="font-medium uppercase">
                            {{ course.language || 'EN' }}
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Access</dt>
                        <dd class="font-medium">Lifetime</dd>
                    </div>
                </dl>
                <div class="mt-5 border-t pt-5">
                    <p class="font-display text-3xl font-semibold">
                        {{ course.price }}
                    </p>
                    <Button class="mt-3 w-full" size="lg" as-child>
                        <Link
                            v-if="isEnrolled"
                            :href="`/learning/${course.slug}`"
                            >Continue learning</Link
                        >
                        <Link
                            v-else-if="canCheckout"
                            :href="`/checkout/${course.slug}`"
                            >Enrol now</Link
                        >
                        <Link
                            v-else-if="!user"
                            :href="`/register?redirect=/checkout/${course.slug}`"
                            >Create account</Link
                        >
                        <Link v-else :href="`/learning/${course.slug}`"
                            >Start learning</Link
                        >
                    </Button>
                    <p
                        class="mt-3 flex items-center justify-center gap-1.5 text-xs text-muted-foreground"
                    >
                        <Lock class="size-3.5" /> Secure checkout
                    </p>
                </div>
            </div>
        </aside>
    </div>

    <section v-if="related.length" class="border-t bg-card py-16">
        <div class="container-page">
            <div class="flex items-end justify-between">
                <h2 class="font-display text-3xl font-semibold">
                    You might also like
                </h2>
                <Link href="/courses" class="text-sm font-semibold text-primary"
                    >All courses →</Link
                >
            </div>
            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <CourseCard
                    v-for="item in related"
                    :key="item.id"
                    :course="item"
                />
            </div>
        </div>
    </section>
</template>
