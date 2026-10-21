<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, Compass } from '@lucide/vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import ProgressBar from '@/components/ProgressBar.vue';
import { Button } from '@/components/ui/button';

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

defineProps<{
    enrollments: {
        data: Enrollment[];
        links: never[];
        from: number;
        to: number;
        total: number;
    };
}>();
</script>

<template>
    <Head title="My courses" />

    <div class="px-4 py-6 lg:px-8">
        <div class="mx-auto max-w-5xl">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Your library</p>
                    <h1
                        class="mt-2 font-display text-3xl font-semibold tracking-tight"
                    >
                        My courses
                    </h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Everything you're learning, all in one place.
                    </p>
                </div>
                <Button variant="outline" as-child>
                    <Link href="/courses"
                        ><Compass class="size-4" /> Find a course</Link
                    >
                </Button>
            </div>

            <div v-if="enrollments.data.length" class="mt-8 space-y-3">
                <Link
                    v-for="item in enrollments.data"
                    :key="item.id"
                    :href="`/learning/${item.course.slug}`"
                    class="group flex flex-col gap-4 rounded-2xl border bg-card p-5 transition hover:border-primary/40 hover:shadow-soft sm:flex-row sm:items-center"
                >
                    <div
                        class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-primary/10"
                    >
                        <img
                            v-if="item.course.thumbnail_url"
                            :src="item.course.thumbnail_url"
                            :alt="item.course.title"
                            class="size-full object-cover"
                        />
                        <BookOpen v-else class="size-6 text-primary" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold group-hover:text-primary">
                            {{ item.course.title }}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ item.course.category || 'Course' }} ·
                            {{ item.course.level }}
                            <template v-if="item.last_accessed_at">
                                · last opened
                                {{ item.last_accessed_at }}</template
                            >
                        </p>
                    </div>
                    <div class="w-full sm:w-48">
                        <div class="flex justify-between text-xs">
                            <span class="text-muted-foreground"
                                >{{ item.completed_contents_count }}/{{
                                    item.total_contents_count
                                }}
                                lessons</span
                            >
                            <span class="font-mono"
                                >{{ item.progress_percent }}%</span
                            >
                        </div>
                        <ProgressBar
                            :percent="item.progress_percent"
                            class="mt-2"
                        />
                    </div>
                </Link>
            </div>
            <EmptyState
                v-else
                class="mt-8"
                title="Nothing here yet."
                message="When you enrol in a course, it will appear here with your progress."
                action="Browse the catalogue"
                href="/courses"
            />

            <Pagination :paginator="enrollments" />
        </div>
    </div>
</template>
