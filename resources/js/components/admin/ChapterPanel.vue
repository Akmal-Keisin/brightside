<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ChevronDown, CirclePlus, Trash2 } from '@lucide/vue';
import { ref } from 'vue';
import ContentEditor from '@/components/admin/ContentEditor.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EditableChapter } from '@/types/admin';

type Chapter = EditableChapter;

const props = defineProps<{
    chapter: Chapter;
    courseSlug: string;
    contentTypes: Record<string, string>;
    videoProviders: Record<string, string>;
}>();

const open = ref(true);
const adding = ref(false);
const editing = ref(false);

const chapterForm = useForm({
    title: props.chapter.title,
    description: props.chapter.description ?? '',
});

const contentForm = useForm({
    title: '',
    type: 'Article',
    estimated_minutes: 5,
    is_free_preview: false,
    body: '',
    video_provider: '',
    video_reference: '',
    video_duration_seconds: 0,
    transcript: '',
});

const saveChapter = () => {
    chapterForm.put(`/admin/chapters/${props.chapter.id}`, {
        preserveScroll: true,
    });
};

const deleteChapter = () => {
    if (confirm(`Delete “${props.chapter.title}” and all of its lessons?`)) {
        router.delete(`/admin/chapters/${props.chapter.id}`, {
            preserveScroll: true,
        });
    }
};

const addContent = () => {
    contentForm.post(`/admin/chapters/${props.chapter.id}/contents`, {
        preserveScroll: true,
        onSuccess: () => {
            contentForm.reset();
            adding.value = false;
        },
    });
};
</script>

<template>
    <div class="rounded-card border bg-card shadow-soft">
        <div class="flex flex-wrap items-center gap-3 border-b px-4 py-3">
            <button
                type="button"
                class="flex min-w-0 flex-1 items-center gap-2 text-left"
                @click="open = !open"
            >
                <ChevronDown
                    class="size-4 shrink-0 text-muted-foreground transition"
                    :class="{ 'rotate-180': open }"
                />
                <span class="min-w-0">
                    <span class="block truncate font-display font-semibold"
                        >{{ chapter.position }}. {{ chapter.title }}</span
                    >
                    <span class="text-xs text-muted-foreground"
                        >{{ chapter.contents.length }} lessons</span
                    >
                </span>
            </button>
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="editing = !editing"
                >Rename</Button
            >
            <Button
                type="button"
                variant="ghost"
                size="sm"
                class="text-destructive"
                @click="deleteChapter"
            >
                <Trash2 class="size-3.5" />
            </Button>
        </div>

        <div v-if="editing" class="border-b bg-muted/20 p-4">
            <form
                class="grid gap-3 sm:grid-cols-[1fr_1fr_auto]"
                @submit.prevent="saveChapter"
            >
                <div class="grid gap-1.5">
                    <Label :for="`chapter-title-${chapter.id}`"
                        >Chapter title</Label
                    >
                    <Input
                        :id="`chapter-title-${chapter.id}`"
                        v-model="chapterForm.title"
                        required
                    />
                    <InputError :message="chapterForm.errors.title" />
                </div>
                <div class="grid gap-1.5">
                    <Label :for="`chapter-description-${chapter.id}`"
                        >Description</Label
                    >
                    <Input
                        :id="`chapter-description-${chapter.id}`"
                        v-model="chapterForm.description"
                    />
                    <InputError :message="chapterForm.errors.description" />
                </div>
                <div class="flex items-end">
                    <Button
                        type="submit"
                        size="sm"
                        :disabled="chapterForm.processing"
                        >Save</Button
                    >
                </div>
            </form>
        </div>

        <div v-if="open" class="space-y-2 p-3">
            <ContentEditor
                v-for="content in chapter.contents"
                :key="content.id"
                :content="content"
                :video-providers="videoProviders"
            />

            <div
                v-if="adding"
                class="rounded-control border border-dashed bg-muted/20 p-4"
            >
                <form class="grid gap-4" @submit.prevent="addContent">
                    <div class="grid gap-4 sm:grid-cols-[1fr_160px_120px]">
                        <div class="grid gap-2">
                            <Label :for="`new-title-${chapter.id}`"
                                >Lesson title</Label
                            >
                            <Input
                                :id="`new-title-${chapter.id}`"
                                v-model="contentForm.title"
                                required
                                maxlength="180"
                                placeholder="What is this lesson called?"
                            />
                            <InputError :message="contentForm.errors.title" />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="`new-type-${chapter.id}`">Type</Label>
                            <select
                                :id="`new-type-${chapter.id}`"
                                v-model="contentForm.type"
                                class="h-9 rounded-control border bg-background px-3 text-sm"
                            >
                                <option
                                    v-for="(label, value) in contentTypes"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <InputError :message="contentForm.errors.type" />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="`new-minutes-${chapter.id}`"
                                >Minutes</Label
                            >
                            <Input
                                :id="`new-minutes-${chapter.id}`"
                                v-model.number="contentForm.estimated_minutes"
                                type="number"
                                min="1"
                                max="600"
                            />
                            <InputError
                                :message="contentForm.errors.estimated_minutes"
                            />
                        </div>
                    </div>

                    <label
                        v-if="contentForm.type !== 'Quiz'"
                        class="flex items-center gap-2 text-sm"
                    >
                        <input
                            v-model="contentForm.is_free_preview"
                            type="checkbox"
                            class="size-4 rounded border"
                        />
                        Free preview
                    </label>

                    <div
                        v-if="contentForm.type === 'Article'"
                        class="grid gap-2"
                    >
                        <Label :for="`new-body-${chapter.id}`"
                            >Body (Markdown)</Label
                        >
                        <textarea
                            :id="`new-body-${chapter.id}`"
                            v-model="contentForm.body"
                            rows="6"
                            class="min-h-32 w-full rounded-control border bg-background px-3 py-2 font-mono text-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/30"
                        />
                        <InputError :message="contentForm.errors.body" />
                    </div>

                    <template v-if="contentForm.type === 'Video'">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label :for="`new-provider-${chapter.id}`"
                                    >Provider</Label
                                >
                                <select
                                    :id="`new-provider-${chapter.id}`"
                                    v-model="contentForm.video_provider"
                                    class="h-9 rounded-control border bg-background px-3 text-sm"
                                >
                                    <option value="">Choose…</option>
                                    <option
                                        v-for="(label, value) in videoProviders"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <InputError
                                    :message="contentForm.errors.video_provider"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label :for="`new-reference-${chapter.id}`"
                                    >Video reference</Label
                                >
                                <Input
                                    :id="`new-reference-${chapter.id}`"
                                    v-model="contentForm.video_reference"
                                />
                                <InputError
                                    :message="
                                        contentForm.errors.video_reference
                                    "
                                />
                            </div>
                        </div>
                    </template>

                    <div class="flex gap-2">
                        <Button
                            type="submit"
                            size="sm"
                            :disabled="contentForm.processing"
                            >Add lesson</Button
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
            </div>

            <Button
                v-else
                type="button"
                variant="ghost"
                size="sm"
                class="w-full border border-dashed"
                @click="adding = true"
            >
                <CirclePlus class="size-4" /> Add lesson to this chapter
            </Button>
        </div>
    </div>
</template>
