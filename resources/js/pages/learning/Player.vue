<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    Check,
    CheckCircle2,
    ChevronDown,
    Circle,
    Clock3,
    FileText,
    HelpCircle,
    Lock,
    PlayCircle,
} from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import ProgressBar from '@/components/ProgressBar.vue';
import { Button } from '@/components/ui/button';

type ContentSummary = {
    id: number;
    title: string;
    type: string;
    estimated_minutes: number;
    is_free_preview: boolean;
    status: string | null;
    best_score: number | null;
    quiz_questions_count: number;
};
type Chapter = { id: number; title: string; contents: ContentSummary[] };

const props = defineProps<{
    course: { id: number; title: string; slug: string; contents_count: number };
    chapters: Chapter[];
    content: {
        id: number;
        chapter_id: number;
        chapter_title: string;
        title: string;
        type: string;
        estimated_minutes: number;
        body: string | null;
        video_provider: string | null;
        video_reference: string | null;
        video_duration_seconds: number | null;
        transcript: string | null;
        is_completed: boolean;
    };
    navigation: {
        previous: { id: number; title: string } | null;
        next: { id: number; title: string } | null;
    };
    progress: { percent: number; completed: number; total: number };
    quiz: {
        id: number;
        passing_score: number;
        max_attempts: number | null;
        time_limit_seconds: number | null;
        questions_count: number;
        attempts_used: number;
        remaining_attempts: number | null;
        best_score: number | null;
        passed: boolean;
        active_attempt: { id: number } | null;
    } | null;
    isPreview: boolean;
}>();

const openChapters = ref<number[]>(props.chapters.map((chapter) => chapter.id));
const watched = ref(0);
const marking = ref(false);
const video = ref<HTMLVideoElement | null>(null);
let lastSent = 0;

const isDone = (item: ContentSummary) => item.status === 'Completed';

const iconFor = (type: string) =>
    type === 'Video' ? PlayCircle : type === 'Quiz' ? HelpCircle : FileText;

const embedUrl = computed(() => {
    const reference = props.content.video_reference;
    if (!reference) return null;
    if (props.content.video_provider === 'YouTube') {
        const id =
            reference.match(/(?:youtu\.be\/|v=|embed\/)([\w-]{6,})/)?.[1] ??
            reference;
        return `https://www.youtube-nocookie.com/embed/${id}`;
    }
    if (props.content.video_provider === 'Vimeo') {
        const id = reference.match(/(\d+)/)?.[1] ?? reference;
        return `https://player.vimeo.com/video/${id}`;
    }
    return reference.startsWith('http') ? reference : null;
});

const canComplete = computed(() => {
    return !props.content.is_completed && props.content.type !== 'Quiz';
});

const toggleChapter = (id: number) => {
    openChapters.value = openChapters.value.includes(id)
        ? openChapters.value.filter((item) => item !== id)
        : [...openChapters.value, id];
};

const csrfToken = () => {
    const entry = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='));
    return entry ? decodeURIComponent(entry.split('=')[1]) : '';
};

const sendProgress = (seconds: number) => {
    if (props.isPreview || !props.content.video_duration_seconds) return;
    watched.value = seconds;
    if (Math.floor(seconds) - lastSent < 10) return;
    lastSent = Math.floor(seconds);
    void fetch(`/learning/${props.course.slug}/${props.content.id}/progress`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-XSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify({ seconds: Math.floor(seconds) }),
    });
};

const complete = () => {
    marking.value = true;
    router.post(
        `/learning/${props.course.slug}/${props.content.id}/complete`,
        {},
        { preserveScroll: true, onFinish: () => (marking.value = false) },
    );
};

const startQuiz = () => {
    if (!props.quiz) return;
    router.post(
        `/learning/${props.course.slug}/${props.content.id}/quiz/attempts`,
    );
};

onBeforeUnmount(() => {
    if (video.value && watched.value > 0) {
        sendProgress(video.value.currentTime);
    }
});
</script>

<template>
    <Head :title="`${content.title} — ${course.title}`" />

    <div class="border-b bg-card">
        <div
            class="flex flex-wrap items-center gap-3 px-4 py-3 text-sm lg:px-8"
        >
            <Link
                :href="`/courses/${course.slug}`"
                class="text-muted-foreground hover:text-primary"
            >
                {{ course.title }}
            </Link>
            <span class="text-muted-foreground">/</span>
            <span class="font-medium">{{ content.title }}</span>
            <div class="ml-auto flex items-center gap-3">
                <span class="font-mono text-xs text-muted-foreground"
                    >{{ progress.completed }}/{{ progress.total }}</span
                >
                <ProgressBar :percent="progress.percent" class="w-28" />
                <span class="font-mono text-xs">{{ progress.percent }}%</span>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-[320px_1fr]">
        <aside
            class="order-2 border-t bg-card lg:order-1 lg:min-h-[calc(100dvh-8rem)] lg:border-t-0 lg:border-r"
        >
            <div class="p-4">
                <p class="px-2 eyebrow">Course contents</p>
                <div class="mt-3 space-y-1">
                    <div v-for="(chapter, index) in chapters" :key="chapter.id">
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 rounded-xl px-2 py-2 text-left text-sm font-semibold hover:bg-muted"
                            :aria-expanded="openChapters.includes(chapter.id)"
                            @click="toggleChapter(chapter.id)"
                        >
                            <span
                                class="font-mono text-xs text-muted-foreground"
                                >{{ String(index + 1).padStart(2, '0') }}</span
                            >
                            <span class="flex-1">{{ chapter.title }}</span>
                            <ChevronDown
                                class="size-4 transition"
                                :class="
                                    openChapters.includes(chapter.id)
                                        ? 'rotate-180'
                                        : ''
                                "
                            />
                        </button>
                        <ul
                            v-if="openChapters.includes(chapter.id)"
                            class="mb-2 ml-2 space-y-0.5 border-l pl-2"
                        >
                            <li v-for="item in chapter.contents" :key="item.id">
                                <Link
                                    :href="`/learning/${course.slug}/${item.id}`"
                                    class="flex items-start gap-2 rounded-lg px-2 py-2 text-sm transition"
                                    :class="
                                        item.id === content.id
                                            ? 'bg-primary/10 font-medium text-primary'
                                            : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                    "
                                >
                                    <CheckCircle2
                                        v-if="isDone(item)"
                                        class="mt-0.5 size-4 shrink-0 text-success"
                                    />
                                    <Circle
                                        v-else
                                        class="mt-0.5 size-4 shrink-0 opacity-40"
                                    />
                                    <span class="flex-1 leading-5">{{
                                        item.title
                                    }}</span>
                                    <component
                                        :is="iconFor(item.type)"
                                        class="mt-0.5 size-3.5 shrink-0 opacity-50"
                                    />
                                </Link>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </aside>

        <main class="order-1 min-w-0 lg:order-2">
            <div class="mx-auto max-w-3xl px-5 py-8 lg:px-10 lg:py-12">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span
                        class="rounded-full bg-muted px-2.5 py-1 font-semibold"
                        >{{ content.type }}</span
                    >
                    <span class="text-muted-foreground">{{
                        content.chapter_title
                    }}</span>
                    <span class="text-muted-foreground">·</span>
                    <span
                        class="inline-flex items-center gap-1 text-muted-foreground"
                        ><Clock3 class="size-3.5" />
                        {{ content.estimated_minutes }} min</span
                    >
                </div>

                <h1
                    class="mt-4 font-display text-3xl font-semibold tracking-tight sm:text-4xl"
                >
                    {{ content.title }}
                </h1>

                <div
                    v-if="content.type === 'Video'"
                    class="mt-7 overflow-hidden rounded-2xl border bg-foreground"
                >
                    <div v-if="embedUrl" class="aspect-video">
                        <iframe
                            :src="embedUrl"
                            class="size-full"
                            allow="
                                accelerometer;
                                autoplay;
                                clipboard-write;
                                encrypted-media;
                                gyroscope;
                                picture-in-picture;
                            "
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                            :title="content.title"
                        />
                    </div>
                    <div
                        v-else
                        class="flex aspect-video flex-col items-center justify-center text-background"
                    >
                        <PlayCircle class="size-10 opacity-50" />
                        <p class="mt-3 font-display text-xl font-semibold">
                            Video coming soon
                        </p>
                        <p class="mt-1 text-sm opacity-60">
                            The lesson text below covers the same ground.
                        </p>
                    </div>
                </div>

                <div
                    v-if="content.body"
                    class="prose-bright mt-8"
                    v-html="content.body"
                />

                <div
                    v-if="content.transcript"
                    class="mt-8 rounded-2xl border bg-card p-5"
                >
                    <p class="eyebrow">Transcript</p>
                    <p
                        class="mt-3 text-sm leading-7 whitespace-pre-line text-muted-foreground"
                    >
                        {{ content.transcript }}
                    </p>
                </div>

                <section
                    v-if="quiz"
                    class="mt-10 rounded-[1.5rem] border-2 bg-card p-6"
                >
                    <div class="flex items-start gap-4">
                        <span
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-success/25"
                        >
                            <HelpCircle class="size-6" />
                        </span>
                        <div class="flex-1">
                            <p class="eyebrow">Quick check</p>
                            <h2
                                class="mt-1 font-display text-2xl font-semibold"
                            >
                                {{ content.title }}
                            </h2>
                            <p class="mt-2 text-sm text-muted-foreground">
                                {{ quiz.questions_count }} questions · pass at
                                {{ quiz.passing_score }}%
                                <template v-if="quiz.max_attempts">
                                    · {{ quiz.remaining_attempts }} of
                                    {{ quiz.max_attempts }} attempts
                                    left</template
                                >
                            </p>
                            <div
                                v-if="quiz.passed"
                                class="mt-4 inline-flex items-center gap-2 rounded-full bg-success/25 px-3 py-1.5 text-sm font-semibold"
                            >
                                <Check class="size-4" /> Passed{{
                                    quiz.best_score
                                        ? ` · ${quiz.best_score}%`
                                        : ''
                                }}
                            </div>
                            <div
                                v-if="!isPreview"
                                class="mt-5 flex flex-wrap gap-3"
                            >
                                <Button v-if="quiz.active_attempt" as-child>
                                    <Link
                                        :href="`/learning/quiz-attempts/${quiz.active_attempt.id}`"
                                        >Resume quiz <ArrowRight class="size-4"
                                    /></Link>
                                </Button>
                                <Button
                                    v-else-if="
                                        quiz.passed ||
                                        (quiz.remaining_attempts !== null &&
                                            quiz.remaining_attempts === 0)
                                    "
                                    variant="outline"
                                    disabled
                                >
                                    <Lock class="size-4" /> No attempts left
                                </Button>
                                <Button v-else @click="startQuiz">
                                    {{
                                        quiz.attempts_used
                                            ? 'Try again'
                                            : 'Start quiz'
                                    }}
                                    <ArrowRight class="size-4" />
                                </Button>
                            </div>
                            <p
                                v-if="isPreview"
                                class="mt-5 text-sm text-muted-foreground"
                            >
                                Quiz attempts are disabled in admin preview.
                            </p>
                        </div>
                    </div>
                </section>

                <div
                    v-if="!isPreview && content.type !== 'Quiz'"
                    class="mt-8 flex flex-wrap items-center gap-3 border-t pt-6"
                >
                    <Button
                        v-if="canComplete"
                        :disabled="marking"
                        @click="complete"
                    >
                        <Check class="size-4" /> Mark lesson complete
                    </Button>
                    <span
                        v-else-if="!isPreview"
                        class="inline-flex items-center gap-2 text-sm font-medium text-success"
                    >
                        <CheckCircle2 class="size-4" /> Lesson complete
                    </span>
                </div>

                <div
                    v-if="isPreview"
                    class="mt-8 rounded-2xl border border-accent/50 bg-accent/10 p-4 text-sm"
                >
                    You're viewing an admin preview. Progress is not recorded.
                </div>

                <nav
                    class="mt-10 flex items-center justify-between gap-4 border-t pt-6"
                >
                    <Button
                        v-if="navigation.previous"
                        variant="outline"
                        as-child
                    >
                        <Link
                            :href="`/learning/${course.slug}/${navigation.previous.id}`"
                        >
                            <ArrowLeft class="size-4" />
                            <span class="hidden sm:inline">{{
                                navigation.previous.title
                            }}</span
                            ><span class="sm:hidden">Previous</span>
                        </Link>
                    </Button>
                    <span v-else />
                    <Button v-if="navigation.next" as-child>
                        <Link
                            :href="`/learning/${course.slug}/${navigation.next.id}`"
                        >
                            <span class="hidden sm:inline">{{
                                navigation.next.title
                            }}</span
                            ><span class="sm:hidden">Next</span>
                            <ArrowRight class="size-4" />
                        </Link>
                    </Button>
                    <Button v-else variant="secondary" as-child>
                        <Link :href="`/courses/${course.slug}`"
                            >Back to course</Link
                        >
                    </Button>
                </nav>
            </div>
        </main>
    </div>
</template>
