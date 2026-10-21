<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Clock3, PlayCircle, Star, Users } from '@lucide/vue';
import { computed } from 'vue';

type CourseCard = {
    title: string;
    slug: string;
    subtitle?: string | null;
    thumbnail_url?: string | null;
    price?: string;
    is_free?: boolean;
    level?: string;
    contents_count?: number;
    duration_minutes?: number;
    enrollments_count?: number;
    rating_avg?: number | null;
    category?: { name: string; color?: string | null } | null;
    author?: { name: string } | null;
};

const props = defineProps<{
    course: CourseCard;
    featured?: boolean;
}>();

const tint = computed(() => props.course.category?.color || '#3A4DFF');
</script>

<template>
    <article
        class="group relative flex h-full flex-col overflow-hidden rounded-[1.25rem] border bg-card transition duration-200 hover:-translate-y-1 hover:shadow-[0_18px_40px_-24px_rgb(23_21_28/0.35)]"
    >
        <Link
            :href="`/courses/${course.slug}`"
            class="relative block aspect-[16/10] overflow-hidden"
            :style="{ backgroundColor: tint }"
        >
            <img
                v-if="course.thumbnail_url"
                :src="course.thumbnail_url"
                :alt="course.title"
                class="size-full object-cover transition duration-500 group-hover:scale-[1.03]"
                loading="lazy"
            />
            <div
                v-else
                class="relative flex size-full flex-col justify-between overflow-hidden p-5 text-white"
            >
                <span
                    class="absolute -top-8 -right-6 size-32 rounded-full border-[14px] border-white/20"
                    aria-hidden="true"
                />
                <span
                    class="absolute right-14 -bottom-12 size-28 rotate-12 rounded-3xl bg-white/15"
                    aria-hidden="true"
                />
                <span class="eyebrow text-white/80">{{
                    course.category?.name || 'Course'
                }}</span>
                <span
                    class="max-w-[15ch] font-display text-2xl leading-tight font-semibold"
                >
                    {{ course.title }}
                </span>
            </div>
            <span
                v-if="featured"
                class="absolute top-3 left-3 rounded-full bg-warning px-2.5 py-1 text-xs font-semibold text-foreground"
                >Featured</span
            >
        </Link>

        <div class="flex flex-1 flex-col p-5">
            <div
                class="mb-2 flex items-center gap-2 text-xs font-medium text-muted-foreground"
            >
                <span v-if="course.category">{{ course.category.name }}</span>
                <span v-if="course.category" aria-hidden="true">·</span>
                <span class="capitalize">{{
                    course.level || 'all levels'
                }}</span>
            </div>

            <h3 class="text-lg leading-snug font-semibold">
                <Link
                    :href="`/courses/${course.slug}`"
                    class="transition group-hover:text-primary"
                >
                    {{ course.title }}
                </Link>
            </h3>
            <p
                v-if="course.subtitle"
                class="mt-2 line-clamp-2 text-sm leading-6 text-muted-foreground"
            >
                {{ course.subtitle }}
            </p>

            <div
                class="mt-auto flex flex-wrap items-center gap-x-4 gap-y-2 pt-5 text-xs text-muted-foreground"
            >
                <span
                    v-if="course.contents_count"
                    class="inline-flex items-center gap-1.5"
                >
                    <PlayCircle class="size-3.5" />
                    {{ course.contents_count }} lessons
                </span>
                <span
                    v-if="course.duration_minutes"
                    class="inline-flex items-center gap-1.5"
                >
                    <Clock3 class="size-3.5" />
                    {{ course.duration_minutes }} min
                </span>
                <span
                    v-if="course.enrollments_count"
                    class="inline-flex items-center gap-1.5"
                >
                    <Users class="size-3.5" />
                    {{ course.enrollments_count }}
                </span>
                <span
                    v-if="course.rating_avg"
                    class="inline-flex items-center gap-1 text-foreground"
                >
                    <Star class="size-3.5 fill-accent text-accent" />
                    {{ course.rating_avg }}
                </span>
            </div>

            <div class="mt-4 flex items-center justify-between border-t pt-4">
                <span class="font-mono text-sm font-semibold tabular-nums">{{
                    course.price || 'Free'
                }}</span>
                <span class="text-sm font-medium text-primary"
                    >View course →</span
                >
            </div>
        </div>
    </article>
</template>
