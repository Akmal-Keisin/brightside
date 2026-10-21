<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Clock3, Lock } from '@lucide/vue';
import { Button } from '@/components/ui/button';

defineProps<{
    course: { title: string; slug: string };
    content: {
        id: number;
        title: string;
        type: string;
        body: string | null;
        video_provider: string | null;
        video_reference: string | null;
        estimated_minutes: number;
    };
}>();
</script>

<template>
    <Head :title="`${content.title} — ${course.title}`" />

    <div class="container-page py-10 lg:py-14">
        <Link
            :href="`/courses/${course.slug}`"
            class="inline-flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-primary"
        >
            <ArrowLeft class="size-4" /> Back to {{ course.title }}
        </Link>

        <div class="mx-auto mt-8 max-w-3xl">
            <div class="flex flex-wrap items-center gap-3">
                <span
                    class="rounded-full bg-accent/20 px-3 py-1 text-xs font-semibold text-accent-foreground"
                    >Free preview</span
                >
                <span
                    class="inline-flex items-center gap-1.5 text-xs text-muted-foreground"
                >
                    <Clock3 class="size-3.5" />
                    {{ content.estimated_minutes }} min
                </span>
            </div>
            <h1 class="mt-4 font-display text-4xl font-semibold tracking-tight">
                {{ content.title }}
            </h1>

            <div
                v-if="content.video_reference"
                class="mt-8 overflow-hidden rounded-2xl border bg-foreground"
            >
                <div
                    class="flex aspect-video items-center justify-center bg-foreground text-center text-background"
                >
                    <div class="px-6">
                        <Lock class="mx-auto size-7 opacity-60" />
                        <p class="mt-3 font-display text-xl font-semibold">
                            Video preview
                        </p>
                        <p class="mt-1 text-sm opacity-60">
                            {{ content.video_provider }} ·
                            {{ content.video_reference }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="content.body"
                class="prose-bright mt-8"
                v-html="content.body"
            />

            <div class="mt-10 rounded-[1.5rem] border bg-card p-7 text-center">
                <p class="font-display text-2xl font-semibold">
                    Enjoying this lesson?
                </p>
                <p class="mt-2 text-sm text-muted-foreground">
                    Enrol in {{ course.title }} to unlock every chapter, keep
                    your progress, and take the quizzes.
                </p>
                <Button class="mt-5" size="lg" as-child>
                    <Link :href="`/courses/${course.slug}`"
                        >See the full course</Link
                    >
                </Button>
            </div>
        </div>
    </div>
</template>
