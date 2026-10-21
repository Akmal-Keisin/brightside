<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

type MemberRow = {
    id: number;
    name: string;
    email: string;
    headline: string | null;
    enrollments_count: number;
    joined_at: string;
    last_login_at: string | null;
};

const props = defineProps<{
    members: {
        data: MemberRow[];
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    filters: { q: string };
}>();

const q = ref(props.filters.q);

const filter = () => {
    router.get(
        '/admin/members',
        { q: q.value || undefined },
        { preserveState: true, replace: true },
    );
};
</script>

<template>
    <Head title="Members" />

    <div>
        <p class="eyebrow">Admin studio</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight">
            Members
        </h1>
        <p class="mt-1 text-sm text-muted-foreground">
            Everyone learning on Brightside.
        </p>
    </div>

    <form
        class="mt-6 flex flex-wrap items-center gap-3 rounded-card border bg-card p-3 shadow-soft"
        @submit.prevent="filter"
    >
        <div class="relative min-w-60 flex-1">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="q"
                class="pl-9"
                placeholder="Search by name or email…"
            />
        </div>
        <Button type="submit" variant="outline">Search</Button>
    </form>

    <div class="mt-6 overflow-hidden rounded-card border bg-card shadow-soft">
        <div v-if="members.data.length" class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead
                    class="border-b bg-muted/40 text-xs tracking-wide text-muted-foreground uppercase"
                >
                    <tr>
                        <th class="px-5 py-3 font-medium">Member</th>
                        <th class="px-5 py-3 font-medium">Headline</th>
                        <th class="px-5 py-3 font-medium">Courses</th>
                        <th class="px-5 py-3 font-medium">Joined</th>
                        <th class="px-5 py-3 font-medium">Last seen</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="member in members.data"
                        :key="member.id"
                        class="border-b last:border-0 hover:bg-muted/20"
                    >
                        <td class="px-5 py-4">
                            <Link
                                :href="`/admin/members/${member.id}`"
                                class="font-medium hover:text-primary"
                                >{{ member.name }}</Link
                            >
                            <p class="text-xs text-muted-foreground">
                                {{ member.email }}
                            </p>
                        </td>
                        <td
                            class="max-w-64 truncate px-5 py-4 text-muted-foreground"
                        >
                            {{ member.headline ?? '—' }}
                        </td>
                        <td class="px-5 py-4 font-medium">
                            {{ member.enrollments_count }}
                        </td>
                        <td class="px-5 py-4 text-muted-foreground">
                            {{ member.joined_at }}
                        </td>
                        <td class="px-5 py-4 text-muted-foreground">
                            {{ member.last_login_at ?? 'Never' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="px-5 py-16 text-center text-sm text-muted-foreground">
            No members match that search.
        </p>
    </div>

    <Pagination :paginator="members" />
</template>
