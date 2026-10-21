<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import {
    ChevronDown,
    Clock,
    Eye,
    EyeOff,
    FileText,
    ListChecks,
    PlayCircle,
    Trash2,
} from '@lucide/vue';
import { ref } from 'vue';
import QuizEditor from '@/components/admin/QuizEditor.vue';
import InputError from '@/components/InputError.vue';
import StatusPill from '@/components/StatusPill.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EditableContent } from '@/types/admin';

const props = defineProps<{
    content: EditableContent;
    videoProviders: Record<string, string>;
}>();

const form = useForm({
    title: props.content.title,
    type: props.content.type,
    estimated_minutes: props.content.estimated_minutes,
    is_free_preview: props.content.is_free_preview,
    body: props.content.body ?? '',
    video_provider: props.content.video_provider ?? '',
    video_reference: props.content.video_reference ?? '',
    video_duration_seconds: props.content.video_duration_seconds ?? 0,
    transcript: '',
});

const open = ref(false);
const previewHtml = ref<string | null>(null);
const previewing = ref(false);

const previewMarkdown = async () => {
    previewing.value = true;

    try {
        const token = document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];

        const response = await fetch('/admin/markdown/preview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': token ? decodeURIComponent(token) : '',
            },
            body: JSON.stringify({ markdown: form.body }),
        });

        if (response.ok) {
            const data = await response.json();
            previewHtml.value = data.html;
        }
    } finally {
        previewing.value = false;
    }
};

const icon = () => {
    if (props.content.type === 'Video') return PlayCircle;
    if (props.content.type === 'Quiz') return ListChecks;
    return FileText;
};

const save = () => {
    form.put(`/admin/contents/${props.content.id}`, { preserveScroll: true });
};

const remove = () => {
    if (confirm(`Delete “${props.content.title}”? This cannot be undone.`)) {
        router.delete(`/admin/contents/${props.content.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="rounded-control border bg-background">
        <button
            type="button"
            class="flex w-full items-center gap-3 px-3 py-2.5 text-left"
            @click="open = !open"
        >
            <span
                class="flex size-7 shrink-0 items-center justify-center rounded-md bg-secondary text-secondary-foreground"
            >
                <component :is="icon()" class="size-3.5" />
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-medium"
                    >{{ content.position }}. {{ content.title }}</span
                >
                <span
                    class="flex items-center gap-2 text-xs text-muted-foreground"
                >
                    {{ content.type }}
                    <span class="inline-flex items-center gap-0.5"
                        ><Clock class="size-3" />
                        {{ content.estimated_minutes }} min</span
                    >
                    <StatusPill
                        v-if="content.is_free_preview"
                        status="published"
                        label="Free preview"
                        class="px-1.5 py-0 text-[10px]"
                    />
                </span>
            </span>
            <ChevronDown
                class="size-4 shrink-0 text-muted-foreground transition"
                :class="{ 'rotate-180': open }"
            />
        </button>

        <div v-if="open" class="space-y-5 border-t p-4">
            <form class="grid gap-4" @submit.prevent="save">
                <div class="grid gap-2">
                    <Label :for="`title-${content.id}`">Lesson title</Label>
                    <Input
                        :id="`title-${content.id}`"
                        v-model="form.title"
                        required
                        maxlength="180"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="grid gap-2">
                        <Label :for="`type-${content.id}`">Type</Label>
                        <select
                            :id="`type-${content.id}`"
                            v-model="form.type"
                            disabled
                            class="h-9 rounded-control border bg-muted px-3 text-sm text-muted-foreground"
                        >
                            <option value="Article">Article</option>
                            <option value="Video">Video</option>
                            <option value="Quiz">Quiz</option>
                        </select>
                        <p class="text-xs text-muted-foreground">
                            Type is locked. Delete and recreate to change it.
                        </p>
                        <InputError :message="form.errors.type" />
                    </div>
                    <div class="grid gap-2">
                        <Label :for="`minutes-${content.id}`"
                            >Estimated minutes</Label
                        >
                        <Input
                            :id="`minutes-${content.id}`"
                            v-model.number="form.estimated_minutes"
                            type="number"
                            min="1"
                            max="600"
                        />
                        <InputError :message="form.errors.estimated_minutes" />
                    </div>
                    <label
                        v-if="form.type !== 'Quiz'"
                        class="flex items-end gap-2 pb-2 text-sm"
                    >
                        <input
                            v-model="form.is_free_preview"
                            type="checkbox"
                            class="size-4 rounded border"
                        />
                        Free preview
                    </label>
                </div>

                <div v-if="form.type === 'Article'" class="grid gap-2">
                    <Label :for="`body-${content.id}`">Article body</Label>
                    <textarea
                        :id="`body-${content.id}`"
                        v-model="form.body"
                        rows="8"
                        class="min-h-40 w-full rounded-control border bg-background px-3 py-2 font-mono text-xs shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/30"
                        placeholder="Write in Markdown…"
                    />
                    <InputError :message="form.errors.body" />
                    <div class="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            :disabled="previewing"
                            @click="previewMarkdown"
                        >
                            <Eye class="size-3.5" /> Preview Markdown
                        </Button>
                        <Button
                            v-if="previewHtml"
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="previewHtml = null"
                        >
                            <EyeOff class="size-3.5" /> Hide preview
                        </Button>
                    </div>
                    <div
                        v-if="previewHtml"
                        class="prose-bright max-h-80 overflow-y-auto rounded-control border bg-card p-4"
                        v-html="previewHtml"
                    />
                </div>

                <template v-if="form.type === 'Video'">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label :for="`provider-${content.id}`"
                                >Video provider</Label
                            >
                            <select
                                :id="`provider-${content.id}`"
                                v-model="form.video_provider"
                                class="h-9 rounded-control border bg-background px-3 text-sm"
                            >
                                <option value="">Choose a provider</option>
                                <option
                                    v-for="(label, value) in videoProviders"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.video_provider" />
                        </div>
                        <div class="grid gap-2">
                            <Label :for="`reference-${content.id}`"
                                >Video reference</Label
                            >
                            <Input
                                :id="`reference-${content.id}`"
                                v-model="form.video_reference"
                                placeholder="Video ID or embed URL"
                            />
                            <InputError
                                :message="form.errors.video_reference"
                            />
                        </div>
                    </div>
                    <div class="grid gap-2 sm:w-56">
                        <Label :for="`duration-${content.id}`"
                            >Duration (seconds)</Label
                        >
                        <Input
                            :id="`duration-${content.id}`"
                            v-model.number="form.video_duration_seconds"
                            type="number"
                            min="0"
                        />
                        <InputError
                            :message="form.errors.video_duration_seconds"
                        />
                    </div>
                </template>

                <div class="flex flex-wrap gap-2">
                    <Button type="submit" size="sm" :disabled="form.processing"
                        >Save lesson</Button
                    >
                    <Button
                        type="button"
                        size="sm"
                        variant="ghost"
                        class="text-destructive"
                        @click="remove"
                    >
                        <Trash2 class="size-3.5" /> Delete
                    </Button>
                </div>
            </form>

            <QuizEditor v-if="content.type === 'Quiz'" :content="content" />
        </div>
    </div>
</template>
