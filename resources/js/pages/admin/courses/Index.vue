<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Search } from '@lucide/vue';
import { ref } from 'vue';
import Pagination from '@/components/Pagination.vue';
import StatusPill from '@/components/StatusPill.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type CourseRow = {
    id: number;
    title: string;
    slug: string;
    status: string;
    level: string;
    price: string;
    category: string | null;
    enrollments_count: number;
    contents_count: number;
    chapters_count: number;
    updated_at: string;
};

const props = defineProps<{
    courses: {
        data: CourseRow[];
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    filters: { q: string; status: string };
    statuses: Record<string, string>;
}>();

const q = ref(props.filters.q);
const status = ref(props.filters.status);

const filter = () => {
    router.get(
        '/admin/courses',
        { q: q.value || undefined, status: status.value || undefined },
        { preserveState: true, replace: true },
    );
};
</script>

<template>
    <Head title="Courses" />

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Admin studio</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
                Courses
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Build, publish, and keep the catalogue tidy.
            </p>
        </div>
        <Button as-child>
            <Link href="/admin/courses/create"
                ><Plus class="size-4" /> New course</Link
            >
        </Button>
    </div>

    <form
        class="mt-6 flex flex-wrap items-center gap-3 rounded-card border bg-card p-3 shadow-soft"
        @submit.prevent="filter"
    >
        <div class="relative min-w-56 flex-1">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input v-model="q" class="pl-9" placeholder="Search courses…" />
        </div>
        <select
            v-model="status"
            class="h-9 rounded-control border bg-background px-3 text-sm"
            @change="filter"
        >
            <option value="">All statuses</option>
            <option
                v-for="(label, value) in statuses"
                :key="value"
                :value="value"
            >
                {{ label }}
            </option>
        </select>
        <Button type="submit" variant="outline">Filter</Button>
    </form>

    <div class="mt-6 overflow-hidden rounded-card border bg-card shadow-soft">
        <div v-if="courses.data.length" class="overflow-x-auto">
            <table class="w-full min-w-[840px] text-left text-sm">
                <thead
                    class="border-b bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"
                >
                    <tr>
                        <th class="px-5 py-3 font-medium">Course</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Curriculum</th>
                        <th class="px-5 py-3 font-medium">Price</th>
                        <th class="px-5 py-3 font-medium">Learners</th>
                        <th class="px-5 py-3 font-medium">Updated</th>
                        <th class="px-5 py-3 text-right font-medium">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="course in courses.data"
                        :key="course.id"
                        class="border-b last:border-0 hover:bg-muted/20"
                    >
                        <td class="px-5 py-4">
                            <Link
                                :href="`/admin/courses/${course.slug}/edit`"
                                class="font-medium hover:text-primary"
                                >{{ course.title }}</Link
                            >
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ course.category ?? 'Uncategorised' }} ·
                                {{ course.level }}
                            </p>
                        </td>
                        <td class="px-5 py-4">
                            <StatusPill :status="course.status" />
                        </td>
                        <td class="px-5 py-4 text-muted-foreground">
                            {{ course.chapters_count }} chapters ·
                            {{ course.contents_count }} lessons
                        </td>
                        <td class="px-5 py-4 font-medium">
                            {{ course.price }}
                        </td>
                        <td class="px-5 py-4">
                            {{ course.enrollments_count.toLocaleString() }}
                        </td>
                        <td class="px-5 py-4 text-muted-foreground">
                            {{ course.updated_at }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex justify-end gap-1">
                                <Button as-child variant="ghost" size="sm">
                                    <Link :href="`/courses/${course.slug}`"
                                        ><Eye class="size-4" /><span
                                            class="sr-only"
                                            >View</span
                                        ></Link
                                    >
                                </Button>
                                <Button as-child variant="ghost" size="sm">
                                    <Link
                                        :href="`/admin/courses/${course.slug}/edit`"
                                        ><Pencil class="size-4" /><span
                                            class="sr-only"
                                            >Edit</span
                                        ></Link
                                    >
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="px-5 py-16 text-center text-sm text-muted-foreground">
            No courses match those filters.
        </p>
    </div>

    <Pagination :paginator="courses" />
</template>
