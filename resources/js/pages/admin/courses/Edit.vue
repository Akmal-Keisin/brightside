<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Archive,
    ArrowLeft,
    ExternalLink,
    Eye,
    Rocket,
    Trash2,
} from '@lucide/vue';
import { computed } from 'vue';
import CourseDetailsForm from '@/components/admin/CourseDetailsForm.vue';
import CurriculumBuilder from '@/components/admin/CurriculumBuilder.vue';
import StatusPill from '@/components/StatusPill.vue';
import { Button } from '@/components/ui/button';
import type { EditableChapter, EditableCourse } from '@/types/admin';

type Course = EditableCourse & {
    slug: string;
    status: string;
    currency: string;
    contents_count: number;
    enrollments_count: number;
    chapters: EditableChapter[];
};

const props = defineProps<{
    course: Course;
    categories: { id: number; name: string }[];
    levels: Record<string, string>;
    contentTypes: Record<string, string>;
    videoProviders: Record<string, string>;
}>();

const canPublish = computed(
    () =>
        props.course.chapters.length > 0 &&
        props.course.chapters.some((chapter) => chapter.contents.length > 0),
);

const action = (path: string) => {
    router.post(path, {}, { preserveScroll: true });
};

const archive = () => {
    if (
        confirm(
            'Archive this course? Learners keep access, but it disappears from the catalogue.',
        )
    ) {
        action(`/admin/courses/${props.course.slug}/archive`);
    }
};

const remove = () => {
    if (
        confirm(
            'Move this course to the trash? Courses with learners or paid orders cannot be deleted; archive them instead.',
        )
    ) {
        router.delete(`/admin/courses/${props.course.slug}`);
    }
};
</script>

<template>
    <Head :title="`Edit ${course.title}`" />

    <div class="mx-auto max-w-5xl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <Button as-child variant="ghost" size="sm" class="-ml-3">
                <Link href="/admin/courses"
                    ><ArrowLeft class="size-4" /> Back to courses</Link
                >
            </Button>
            <div class="flex flex-wrap items-center gap-2">
                <StatusPill :status="course.status" />
                <Button as-child variant="outline" size="sm">
                    <Link :href="`/admin/courses/${course.slug}/preview`"
                        ><Eye class="size-3.5" /> Preview</Link
                    >
                </Button>
                <Button
                    v-if="course.status !== 'Published'"
                    size="sm"
                    :disabled="!canPublish"
                    @click="action(`/admin/courses/${course.slug}/publish`)"
                >
                    <Rocket class="size-3.5" /> Publish
                </Button>
                <Button
                    v-else
                    size="sm"
                    variant="outline"
                    @click="action(`/admin/courses/${course.slug}/unpublish`)"
                >
                    Unpublish
                </Button>
                <Button
                    v-if="course.status !== 'Archived'"
                    variant="ghost"
                    size="sm"
                    @click="archive"
                >
                    <Archive class="size-3.5" />
                </Button>
            </div>
        </div>

        <div class="mt-4">
            <p class="eyebrow">Course studio</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
                {{ course.title }}
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ course.chapters.length }} chapters ·
                {{ course.contents_count }} lessons ·
                {{ course.enrollments_count }} learners
            </p>
        </div>

        <div
            v-if="course.status !== 'Published' && !canPublish"
            class="text-warning-foreground mt-5 rounded-control border border-warning/30 bg-warning/10 px-4 py-3 text-sm"
        >
            Add at least one chapter with one lesson before publishing.
        </div>

        <div class="mt-8">
            <CourseDetailsForm
                :action="`/admin/courses/${course.slug}`"
                method="put"
                :course="course"
                :categories="categories"
                :levels="levels"
                submit-label="Save details"
            >
                <template #secondary>
                    <Button as-child variant="outline">
                        <a
                            :href="`/courses/${course.slug}`"
                            target="_blank"
                            rel="noopener"
                        >
                            <ExternalLink class="size-4" /> Open storefront page
                        </a>
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        class="text-destructive"
                        @click="remove"
                    >
                        <Trash2 class="size-4" /> Move to trash
                    </Button>
                </template>
            </CourseDetailsForm>
        </div>

        <div class="my-10 h-px bg-border" />

        <CurriculumBuilder
            :course-slug="course.slug"
            :chapters="course.chapters"
            :content-types="contentTypes"
            :video-providers="videoProviders"
        />
    </div>
</template>
