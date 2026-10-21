<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Search, SlidersHorizontal } from '@lucide/vue';
import { ref } from 'vue';
import CourseCard from '@/components/CourseCard.vue';
import EmptyState from '@/components/EmptyState.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { CourseSummary } from '@/types/course';

type Category = {
    name: string;
    slug: string;
    color: string;
    courses_count: number;
};

const props = defineProps<{
    courses: {
        data: CourseSummary[];
        links: never[];
        from: number;
        to: number;
        total: number;
    };
    filters: {
        q: string;
        category: string;
        level: string;
        price: string;
        sort: string;
    };
    categories: Category[];
    levels: Record<string, string>;
}>();

const q = ref(props.filters.q);
const category = ref(props.filters.category);
const level = ref(props.filters.level);
const price = ref(props.filters.price);
const sort = ref(props.filters.sort);

const apply = () => {
    router.get(
        '/courses',
        {
            q: q.value || undefined,
            category: category.value || undefined,
            level: level.value || undefined,
            price: price.value || undefined,
            sort: sort.value !== 'newest' ? sort.value : undefined,
        },
        { preserveState: true, replace: true },
    );
};

const clear = () => {
    q.value = '';
    category.value = '';
    level.value = '';
    price.value = '';
    sort.value = 'newest';
    router.get('/courses');
};
</script>

<template>
    <Head title="Browse courses">
        <meta
            name="description"
            content="Browse practical, friendly courses in design, development, product and business. Learn at your own pace."
        />
        <link rel="canonical" href="/courses" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="Browse courses — Brightside" />
        <meta
            property="og:description"
            content="Browse practical, friendly courses in design, development, product and business."
        />
        <meta property="og:url" content="/courses" />
    </Head>

    <section class="border-b bg-card">
        <div class="container-page py-14 lg:py-18">
            <p class="eyebrow">The catalogue</p>
            <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
                <h1
                    class="font-display text-4xl font-semibold tracking-tight sm:text-5xl"
                >
                    Find your next good thing.
                </h1>
                <p class="max-w-sm text-sm leading-6 text-muted-foreground">
                    {{ courses.total }}
                    {{ courses.total === 1 ? 'course' : 'courses' }} to explore.
                    No rush — the right one will find you.
                </p>
            </div>
        </div>
    </section>

    <div class="container-page py-10">
        <form
            class="grid gap-3 rounded-[1.25rem] border bg-card p-4 md:grid-cols-[1fr_auto_auto_auto_auto]"
            @submit.prevent="apply"
        >
            <div class="relative">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="q"
                    class="pl-9"
                    placeholder="Search courses…"
                    aria-label="Search courses"
                />
            </div>
            <select
                v-model="category"
                class="h-9 rounded-xl border border-input bg-background px-3 text-sm"
                aria-label="Category"
            >
                <option value="">All categories</option>
                <option
                    v-for="item in categories"
                    :key="item.slug"
                    :value="item.slug"
                >
                    {{ item.name }}
                </option>
            </select>
            <select
                v-model="level"
                class="h-9 rounded-xl border border-input bg-background px-3 text-sm"
                aria-label="Level"
            >
                <option value="">All levels</option>
                <option
                    v-for="(label, value) in levels"
                    :key="value"
                    :value="value"
                >
                    {{ label }}
                </option>
            </select>
            <select
                v-model="price"
                class="h-9 rounded-xl border border-input bg-background px-3 text-sm"
                aria-label="Price"
            >
                <option value="">All prices</option>
                <option value="free">Free</option>
                <option value="paid">Paid</option>
            </select>
            <div class="flex gap-2">
                <select
                    v-model="sort"
                    class="h-9 flex-1 rounded-xl border border-input bg-background px-3 text-sm"
                    aria-label="Sort"
                >
                    <option value="newest">Newest</option>
                    <option value="popular">Most popular</option>
                    <option value="price_low">Price: low to high</option>
                    <option value="price_high">Price: high to low</option>
                </select>
                <Button type="submit">
                    <SlidersHorizontal class="size-4" /> Apply
                </Button>
            </div>
        </form>

        <div class="mt-8 flex items-center justify-between">
            <p class="text-sm text-muted-foreground">
                <template v-if="filters.q"
                    >Results for “{{ filters.q }}”</template
                >
                <template v-else>All courses</template>
            </p>
            <button
                v-if="
                    filters.q ||
                    filters.category ||
                    filters.level ||
                    filters.price ||
                    filters.sort !== 'newest'
                "
                type="button"
                class="text-sm font-medium text-primary underline underline-offset-4"
                @click="clear"
            >
                Clear filters
            </button>
        </div>

        <div
            v-if="courses.data.length"
            class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
            <CourseCard
                v-for="course in courses.data"
                :key="course.id"
                :course="course"
            />
        </div>
        <EmptyState
            v-else
            class="mt-6"
            title="No courses matched that."
            message="Try a different search or clear the filters — there are plenty of other good things to learn."
            action="Clear filters"
            href="/courses"
        />

        <Pagination :paginator="courses" />
    </div>
</template>
