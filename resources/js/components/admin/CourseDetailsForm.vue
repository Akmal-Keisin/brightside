<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { ImagePlus, Save, Trash2 } from '@lucide/vue';
import { onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { EditableCourse } from '@/types/admin';

type CategoryOption = { id: number; name: string };

const props = defineProps<{
    action: string;
    method: 'post' | 'put';
    categories: CategoryOption[];
    levels: Record<string, string>;
    course?: EditableCourse;
    submitLabel?: string;
}>();

const form = useForm({
    title: props.course?.title ?? '',
    subtitle: props.course?.subtitle ?? '',
    description: props.course?.description ?? '',
    category_id: props.course?.category_id ?? null,
    price_minor: props.course?.price_minor ?? 0,
    level: props.course?.level ?? 'Beginner',
    trailer_url: props.course?.trailer_url ?? '',
    thumbnail: null as File | null,
    thumbnail_alt: props.course?.thumbnail_alt ?? '',
    learning_outcomes: props.course?.learning_outcomes ?? '',
    requirements: props.course?.requirements ?? '',
    is_featured: props.course?.is_featured ?? false,
});

const preview = ref<string | null>(props.course?.thumbnail_url ?? null);
let objectUrl: string | null = null;
const saved = ref(false);

const beforeUnload = (event: BeforeUnloadEvent) => {
    if (!form.isDirty || saved.value) {
        return;
    }

    event.preventDefault();
    event.returnValue = '';
};

let removeBeforeListener: (() => void) | undefined;

if (typeof window !== 'undefined') {
    window.addEventListener('beforeunload', beforeUnload);
    removeBeforeListener = router.on('before', () => {
        if (!form.isDirty || saved.value) {
            return true;
        }

        return window.confirm(
            'You have unsaved course changes. Leave without saving?',
        );
    });
}

const pickThumbnail = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.thumbnail = file;

    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }

    objectUrl = file ? URL.createObjectURL(file) : null;
    preview.value = objectUrl ?? props.course?.thumbnail_url ?? null;
};

onBeforeUnmount(() => {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }

    if (typeof window !== 'undefined') {
        window.removeEventListener('beforeunload', beforeUnload);
    }

    removeBeforeListener?.();
});

const submit = () => {
    const options = {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            saved.value = true;
        },
    };

    if (props.method === 'put') {
        form.put(props.action, options);
    } else {
        form.post(props.action, options);
    }
};
</script>

<template>
    <form class="space-y-8" @submit.prevent="submit">
        <section class="rounded-card border bg-card p-6 shadow-soft">
            <h2 class="font-display text-lg font-semibold">Basics</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                The title and subtitle are what learners see first.
            </p>

            <div class="mt-6 grid gap-5">
                <div class="grid gap-2">
                    <Label for="title">Course title</Label>
                    <Input
                        id="title"
                        v-model="form.title"
                        required
                        maxlength="160"
                        placeholder="e.g. Playful Product Design"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="subtitle">Subtitle</Label>
                    <Input
                        id="subtitle"
                        v-model="form.subtitle"
                        maxlength="240"
                        placeholder="A short, motivating promise"
                    />
                    <InputError :message="form.errors.subtitle" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="7"
                        required
                        class="min-h-40 w-full rounded-control border bg-background px-3 py-2 text-sm shadow-xs transition outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/30"
                        placeholder="What will learners walk away with?"
                    />
                    <p class="text-xs text-muted-foreground">
                        Markdown is supported. Include what the course covers
                        and who it is for.
                    </p>
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="category_id">Category</Label>
                        <select
                            id="category_id"
                            v-model="form.category_id"
                            class="h-10 rounded-control border bg-background px-3 text-sm"
                        >
                            <option :value="null">Uncategorised</option>
                            <option
                                v-for="category in categories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.category_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="level">Level</Label>
                        <select
                            id="level"
                            v-model="form.level"
                            class="h-10 rounded-control border bg-background px-3 text-sm"
                        >
                            <option
                                v-for="(label, value) in levels"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.level" />
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="price_minor">Price (IDR)</Label>
                        <Input
                            id="price_minor"
                            v-model.number="form.price_minor"
                            type="number"
                            min="0"
                            step="1000"
                        />
                        <p class="text-xs text-muted-foreground">
                            Use whole rupiah. Set to 0 for a free course.
                        </p>
                        <InputError :message="form.errors.price_minor" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="trailer_url">Trailer URL</Label>
                        <Input
                            id="trailer_url"
                            v-model="form.trailer_url"
                            type="url"
                            placeholder="https://youtube.com/watch?v=..."
                        />
                        <InputError :message="form.errors.trailer_url" />
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-card border bg-card p-6 shadow-soft">
            <h2 class="font-display text-lg font-semibold">Cover image</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                A bold, warm image works best. JPG or PNG up to 4 MB.
            </p>

            <div class="mt-6 flex flex-col gap-5 sm:flex-row">
                <div
                    class="relative flex aspect-video w-full items-center justify-center overflow-hidden rounded-control bg-muted sm:w-64"
                >
                    <img
                        v-if="preview"
                        :src="preview"
                        alt=""
                        class="size-full object-cover"
                    />
                    <ImagePlus v-else class="size-8 text-muted-foreground" />
                </div>

                <div class="flex-1 space-y-4">
                    <div class="grid gap-2">
                        <Label for="thumbnail">Upload image</Label>
                        <Input
                            id="thumbnail"
                            type="file"
                            accept="image/*"
                            @change="pickThumbnail"
                        />
                        <InputError :message="form.errors.thumbnail" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="thumbnail_alt">Alt text</Label>
                        <Input
                            id="thumbnail_alt"
                            v-model="form.thumbnail_alt"
                            maxlength="180"
                            placeholder="Describe the image"
                        />
                        <InputError :message="form.errors.thumbnail_alt" />
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-card border bg-card p-6 shadow-soft">
            <h2 class="font-display text-lg font-semibold">
                Outcomes & requirements
            </h2>
            <p class="mt-1 text-sm text-muted-foreground">
                One item per line. Keep outcomes concrete and action-led.
            </p>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="learning_outcomes">Learning outcomes</Label>
                    <textarea
                        id="learning_outcomes"
                        v-model="form.learning_outcomes"
                        rows="6"
                        class="min-h-36 w-full rounded-control border bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/30"
                        placeholder="Build a complete design system&#10;Run a confident user interview"
                    />
                    <InputError :message="form.errors.learning_outcomes" />
                </div>

                <div class="grid gap-2">
                    <Label for="requirements">Requirements</Label>
                    <textarea
                        id="requirements"
                        v-model="form.requirements"
                        rows="6"
                        class="min-h-36 w-full rounded-control border bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/30"
                        placeholder="No prior experience needed&#10;A computer and internet connection"
                    />
                    <InputError :message="form.errors.requirements" />
                </div>
            </div>

            <label
                class="mt-6 flex items-start gap-3 rounded-control border bg-background p-4"
            >
                <input
                    v-model="form.is_featured"
                    type="checkbox"
                    class="mt-0.5 size-4 rounded border"
                />
                <span>
                    <span class="block text-sm font-medium"
                        >Feature this course</span
                    >
                    <span class="block text-xs text-muted-foreground"
                        >Featured courses appear on the homepage.</span
                    >
                </span>
            </label>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                <Save class="size-4" />
                {{ submitLabel ?? 'Save course' }}
            </Button>
            <slot name="secondary" />
        </div>
    </form>
</template>
