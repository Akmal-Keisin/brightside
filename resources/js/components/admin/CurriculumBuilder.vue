<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { CirclePlus, Layers } from '@lucide/vue';
import { ref } from 'vue';
import ChapterPanel from '@/components/admin/ChapterPanel.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EditableChapter } from '@/types/admin';

type Chapter = EditableChapter;

const props = defineProps<{
    courseSlug: string;
    chapters: Chapter[];
    contentTypes: Record<string, string>;
    videoProviders: Record<string, string>;
}>();

const adding = ref(false);
const form = useForm({ title: '', description: '' });

const submit = () => {
    form.post(`/admin/courses/${props.courseSlug}/chapters`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            adding.value = false;
        },
    });
};
</script>

<template>
    <section>
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="eyebrow">Curriculum</p>
                <h2
                    class="mt-1 font-display text-2xl font-semibold tracking-tight"
                >
                    Chapters & lessons
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Order the journey, then add articles, videos, and quizzes.
                </p>
            </div>
            <Button
                v-if="!adding"
                type="button"
                variant="outline"
                size="sm"
                @click="adding = true"
            >
                <CirclePlus class="size-4" /> Add chapter
            </Button>
        </div>

        <form
            v-if="adding"
            class="mt-5 grid gap-4 rounded-card border bg-card p-4 shadow-soft sm:grid-cols-[1fr_1.4fr_auto]"
            @submit.prevent="submit"
        >
            <div class="grid gap-2">
                <Label for="chapter-title">Chapter title</Label>
                <Input
                    id="chapter-title"
                    v-model="form.title"
                    required
                    maxlength="160"
                    placeholder="Getting oriented"
                />
                <InputError :message="form.errors.title" />
            </div>
            <div class="grid gap-2">
                <Label for="chapter-description">Description (optional)</Label>
                <Input
                    id="chapter-description"
                    v-model="form.description"
                    placeholder="What this chapter covers"
                />
                <InputError :message="form.errors.description" />
            </div>
            <div class="flex items-end gap-2">
                <Button type="submit" size="sm" :disabled="form.processing"
                    >Add</Button
                >
                <Button
                    type="button"
                    size="sm"
                    variant="ghost"
                    @click="adding = false"
                    >Cancel</Button
                >
            </div>
        </form>

        <div v-if="chapters.length" class="mt-5 space-y-3">
            <ChapterPanel
                v-for="chapter in chapters"
                :key="chapter.id"
                :chapter="chapter"
                :course-slug="courseSlug"
                :content-types="contentTypes"
                :video-providers="videoProviders"
            />
        </div>

        <div
            v-else
            class="mt-5 rounded-card border border-dashed bg-card px-6 py-14 text-center"
        >
            <span
                class="mx-auto flex size-12 items-center justify-center rounded-full bg-secondary text-secondary-foreground"
            >
                <Layers class="size-5" />
            </span>
            <h3 class="mt-4 font-display text-lg font-semibold">
                Start with one chapter
            </h3>
            <p class="mx-auto mt-1 max-w-sm text-sm text-muted-foreground">
                Break the course into a few clear milestones. You can add
                lessons inside each chapter.
            </p>
            <Button
                v-if="!adding"
                type="button"
                class="mt-5"
                variant="outline"
                @click="adding = true"
            >
                <CirclePlus class="size-4" /> Add first chapter
            </Button>
        </div>
    </section>
</template>
