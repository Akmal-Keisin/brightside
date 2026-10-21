<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Check, Pencil, Plus, Trash2, X } from '@lucide/vue';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Category = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    color: string | null;
    position: number;
    courses_count: number;
};

defineProps<{ categories: Category[] }>();

const editing = ref<number | null>(null);
const adding = ref(false);

const createForm = useForm({ name: '', description: '', color: '#3A4DFF' });
const editForm = useForm({ name: '', description: '', color: '#3A4DFF' });

const startEdit = (category: Category) => {
    editing.value = category.id;
    editForm.name = category.name;
    editForm.description = category.description ?? '';
    editForm.color = category.color ?? '#3A4DFF';
    editForm.clearErrors();
};

const create = () => {
    createForm.post('/admin/categories', {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            adding.value = false;
        },
    });
};

const save = (category: Category) => {
    editForm.put(`/admin/categories/${category.slug}`, {
        preserveScroll: true,
        onSuccess: () => {
            editing.value = null;
        },
    });
};

const remove = (category: Category) => {
    if (
        confirm(
            `Delete “${category.name}”? Reassign its courses first — categories with courses cannot be deleted.`,
        )
    ) {
        router.delete(`/admin/categories/${category.slug}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Categories" />

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Admin studio</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
                Categories
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Keep the catalogue easy to browse.
            </p>
        </div>
        <Button v-if="!adding" @click="adding = true"
            ><Plus class="size-4" /> New category</Button
        >
    </div>

    <form
        v-if="adding"
        class="mt-6 grid gap-4 rounded-card border bg-card p-4 shadow-soft sm:grid-cols-[1fr_1.4fr_120px_auto]"
        @submit.prevent="create"
    >
        <div class="grid gap-2">
            <Label for="new-category-name">Name</Label>
            <Input
                id="new-category-name"
                v-model="createForm.name"
                required
                maxlength="80"
                placeholder="Design"
            />
            <InputError :message="createForm.errors.name" />
        </div>
        <div class="grid gap-2">
            <Label for="new-category-description">Description</Label>
            <Input
                id="new-category-description"
                v-model="createForm.description"
                maxlength="500"
                placeholder="Short helper text"
            />
            <InputError :message="createForm.errors.description" />
        </div>
        <div class="grid gap-2">
            <Label for="new-category-color">Color</Label>
            <input
                id="new-category-color"
                v-model="createForm.color"
                type="color"
                class="h-9 w-full rounded-control border bg-background p-1"
            />
            <InputError :message="createForm.errors.color" />
        </div>
        <div class="flex items-end gap-2">
            <Button type="submit" size="sm" :disabled="createForm.processing"
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

    <div class="mt-6 overflow-hidden rounded-card border bg-card shadow-soft">
        <div v-if="categories.length" class="divide-y">
            <div v-for="category in categories" :key="category.id" class="p-4">
                <form
                    v-if="editing === category.id"
                    class="grid gap-4 sm:grid-cols-[1fr_1.4fr_100px_auto]"
                    @submit.prevent="save(category)"
                >
                    <div class="grid gap-1.5">
                        <Label :for="`name-${category.id}`">Name</Label>
                        <Input
                            :id="`name-${category.id}`"
                            v-model="editForm.name"
                            required
                        />
                        <InputError :message="editForm.errors.name" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label :for="`description-${category.id}`"
                            >Description</Label
                        >
                        <Input
                            :id="`description-${category.id}`"
                            v-model="editForm.description"
                        />
                        <InputError :message="editForm.errors.description" />
                    </div>
                    <div class="grid gap-1.5">
                        <Label :for="`color-${category.id}`">Color</Label>
                        <input
                            :id="`color-${category.id}`"
                            v-model="editForm.color"
                            type="color"
                            class="h-9 w-full rounded-control border bg-background p-1"
                        />
                    </div>
                    <div class="flex items-end gap-1">
                        <Button
                            type="submit"
                            size="sm"
                            :disabled="editForm.processing"
                            ><Check class="size-4"
                        /></Button>
                        <Button
                            type="button"
                            size="sm"
                            variant="ghost"
                            @click="editing = null"
                            ><X class="size-4"
                        /></Button>
                    </div>
                </form>

                <div v-else class="flex flex-wrap items-center gap-4">
                    <span
                        class="size-10 shrink-0 rounded-xl border"
                        :style="{
                            backgroundColor: category.color ?? '#e7e5e4',
                        }"
                    />
                    <div class="min-w-0 flex-1">
                        <p class="font-medium">{{ category.name }}</p>
                        <p class="truncate text-sm text-muted-foreground">
                            {{ category.description ?? 'No description yet.' }}
                        </p>
                    </div>
                    <span class="text-sm text-muted-foreground"
                        >{{ category.courses_count }} courses</span
                    >
                    <div class="flex gap-1">
                        <Button
                            variant="ghost"
                            size="sm"
                            @click="startEdit(category)"
                            ><Pencil class="size-4"
                        /></Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            class="text-destructive"
                            @click="remove(category)"
                            ><Trash2 class="size-4"
                        /></Button>
                    </div>
                </div>
            </div>
        </div>
        <p v-else class="px-5 py-16 text-center text-sm text-muted-foreground">
            No categories yet. Add the first one above.
        </p>
    </div>
</template>
