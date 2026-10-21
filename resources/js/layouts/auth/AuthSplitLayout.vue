<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="grid min-h-dvh lg:grid-cols-[1.05fr_1fr]">
        <aside
            class="relative hidden overflow-hidden bg-primary p-12 text-primary-foreground lg:flex lg:flex-col"
        >
            <div
                class="absolute -top-24 -left-24 size-80 rounded-full border-[48px] border-white/10"
                aria-hidden="true"
            />
            <div
                class="absolute right-[-6rem] bottom-[-8rem] size-96 rotate-12 rounded-[5rem] bg-accent/80"
                aria-hidden="true"
            />
            <div
                class="absolute top-1/3 right-16 size-16 rounded-full bg-warning"
                aria-hidden="true"
            />

            <Link :href="home()" class="relative z-10 w-fit">
                <span
                    class="flex items-center gap-2.5 font-display text-2xl font-bold tracking-[-0.04em]"
                >
                    <span
                        class="flex size-9 items-center justify-center rounded-xl bg-white text-primary"
                    >
                        <svg
                            viewBox="0 0 32 32"
                            class="size-5.5"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M7 7.5c3.6-1.4 6.6-.9 9 1.1 2.4-2 5.4-2.5 9-1.1v16c-3.6-1.4-6.6-.9-9 1.1-2.4-2-5.4-2.5-9-1.1v-16Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M16 8.6v15.5"
                                stroke="currentColor"
                                stroke-width="2"
                            />
                        </svg>
                    </span>
                    {{ name }}.
                </span>
            </Link>

            <div class="relative z-10 mt-auto max-w-md">
                <p class="eyebrow text-primary-foreground/70">
                    A classroom for the curious
                </p>
                <p
                    class="mt-5 font-display text-5xl leading-[1.02] font-semibold tracking-[-0.03em]"
                >
                    Learn something good. Keep it for life.
                </p>
                <p
                    class="mt-6 text-[1.05rem] leading-7 text-primary-foreground/80"
                >
                    Practical, friendly courses made to fit around real life.
                    Pick a path, go at your pace, and actually finish.
                </p>
                <div class="mt-8 flex items-center gap-3 text-sm font-medium">
                    <span class="flex -space-x-2">
                        <span
                            class="size-8 rounded-full border-2 border-primary bg-warning"
                        />
                        <span
                            class="size-8 rounded-full border-2 border-primary bg-success"
                        />
                        <span
                            class="size-8 rounded-full border-2 border-primary bg-accent"
                        />
                    </span>
                    Join a small, thoughtful learning community.
                </div>
            </div>
        </aside>

        <main class="flex flex-col px-5 py-8 sm:px-10">
            <div class="lg:hidden">
                <AppLogo />
            </div>
            <div class="m-auto w-full max-w-[390px] py-10">
                <div class="mb-8">
                    <h1
                        v-if="title"
                        class="font-display text-3xl font-semibold tracking-tight"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-2 text-sm leading-6 text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </main>
    </div>
</template>
