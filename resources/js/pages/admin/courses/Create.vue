<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import CourseDetailsForm from '@/components/admin/CourseDetailsForm.vue';
import { Button } from '@/components/ui/button';

defineProps<{
    categories: { id: number; name: string }[];
    levels: Record<string, string>;
}>();
</script>

<template>
    <Head title="Create course" />

    <div class="mx-auto max-w-4xl">
        <Button as-child variant="ghost" size="sm" class="-ml-3">
            <Link href="/admin/courses"
                ><ArrowLeft class="size-4" /> Back to courses</Link
            >
        </Button>

        <div class="mt-4">
            <p class="eyebrow">New course</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
                Create the outline.
            </h1>
            <p class="mt-1 max-w-2xl text-sm text-muted-foreground">
                Start with the promise. You will add chapters, lessons, and
                quizzes on the next screen before publishing.
            </p>
        </div>

        <div class="mt-8">
            <CourseDetailsForm
                action="/admin/courses"
                method="post"
                :categories="categories"
                :levels="levels"
                submit-label="Create course"
            >
                <template #secondary>
                    <Button as-child variant="ghost">
                        <Link href="/admin/courses">Cancel</Link>
                    </Button>
                </template>
            </CourseDetailsForm>
        </div>
    </div>
</template>
