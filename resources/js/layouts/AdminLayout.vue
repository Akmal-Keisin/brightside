<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';

const page = usePage();
const isAdmin = computed(() => Boolean(page.props.auth?.user?.is_admin));
const { isCurrentOrParentUrl } = useCurrentUrl();

const links = [
    { label: 'Dashboard', href: '/admin' },
    { label: 'Courses', href: '/admin/courses' },
    { label: 'Categories', href: '/admin/categories' },
    { label: 'Orders', href: '/admin/orders' },
    { label: 'Members', href: '/admin/members' },
];
</script>

<template>
    <div class="border-b bg-card">
        <div
            class="container-page flex items-center gap-2 overflow-x-auto py-3"
        >
            <span class="mr-3 shrink-0 eyebrow">Studio</span>
            <Link
                v-for="link in links"
                :key="link.href"
                :href="link.href"
                class="shrink-0 rounded-full px-3 py-1.5 text-sm font-medium transition hover:bg-muted"
                :class="
                    isCurrentOrParentUrl(link.href)
                        ? 'bg-foreground text-background hover:bg-foreground'
                        : 'text-muted-foreground'
                "
            >
                {{ link.label }}
            </Link>
            <Link
                v-if="isAdmin"
                href="/"
                class="ml-auto hidden shrink-0 text-sm font-medium text-primary sm:block"
            >
                View storefront →
            </Link>
        </div>
    </div>
    <div class="container-page py-8 lg:py-10">
        <slot />
    </div>
</template>
