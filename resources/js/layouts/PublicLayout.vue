<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Compass, LayoutGrid, LogIn, Menu, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import FlashNotice from '@/components/FlashNotice.vue';
import { Button } from '@/components/ui/button';
import { Toaster } from '@/components/ui/sonner';
import { dashboard, login, register } from '@/routes';
import { index as courses } from '@/routes/courses';

const page = usePage();
const user = computed(() => page.props.auth?.user as { name?: string } | null);
const open = ref(false);
</script>

<template>
    <div class="min-h-dvh bg-background">
        <header
            class="sticky top-0 z-40 border-b border-border/70 bg-background/90 backdrop-blur-xl print:hidden"
        >
            <div class="container-page flex h-18 items-center gap-6">
                <Link href="/" class="shrink-0" aria-label="Brightside home">
                    <AppLogo />
                </Link>

                <nav
                    class="hidden items-center gap-1 md:flex"
                    aria-label="Main"
                >
                    <Button variant="ghost" as-child>
                        <Link :href="courses()">
                            <Compass class="size-4" />
                            Browse courses
                        </Link>
                    </Button>
                    <Button v-if="user" variant="ghost" as-child>
                        <Link :href="dashboard()">
                            <LayoutGrid class="size-4" />
                            My learning
                        </Link>
                    </Button>
                </nav>

                <div class="ml-auto hidden items-center gap-2 md:flex">
                    <template v-if="user">
                        <Button variant="outline" as-child>
                            <Link :href="dashboard()">
                                <BookOpen class="size-4" />
                                Continue learning
                            </Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="ghost" as-child>
                            <Link :href="login()">
                                <LogIn class="size-4" />
                                Log in
                            </Link>
                        </Button>
                        <Button as-child>
                            <Link :href="register()">Join free</Link>
                        </Button>
                    </template>
                </div>

                <button
                    type="button"
                    class="ml-auto inline-flex size-10 items-center justify-center rounded-xl border md:hidden"
                    :aria-expanded="open"
                    aria-label="Toggle navigation"
                    @click="open = !open"
                >
                    <X v-if="open" class="size-5" />
                    <Menu v-else class="size-5" />
                </button>
            </div>

            <div v-if="open" class="border-t bg-background px-5 py-4 md:hidden">
                <div class="flex flex-col gap-2">
                    <Button variant="ghost" class="justify-start" as-child>
                        <Link :href="courses()" @click="open = false">
                            <Compass class="size-4" /> Browse courses
                        </Link>
                    </Button>
                    <template v-if="user">
                        <Button variant="ghost" class="justify-start" as-child>
                            <Link :href="dashboard()" @click="open = false">
                                <LayoutGrid class="size-4" /> My learning
                            </Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="ghost" class="justify-start" as-child>
                            <Link :href="login()" @click="open = false">
                                Log in
                            </Link>
                        </Button>
                        <Button class="justify-start" as-child>
                            <Link :href="register()" @click="open = false">
                                Join free
                            </Link>
                        </Button>
                    </template>
                </div>
            </div>
        </header>

        <FlashNotice class="container-page pt-6 print:hidden" />

        <main>
            <slot />
        </main>

        <footer class="mt-24 border-t bg-card print:hidden">
            <div
                class="container-page grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div class="space-y-4">
                    <AppLogo />
                    <p class="max-w-xs text-sm leading-6 text-muted-foreground">
                        Friendly, practical courses for curious people. Learn at
                        your own pace and keep every lesson for life.
                    </p>
                </div>
                <div>
                    <p class="mb-4 eyebrow">Learn</p>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <Link class="hover:text-primary" :href="courses()"
                                >All courses</Link
                            >
                        </li>
                        <li v-if="user">
                            <Link class="hover:text-primary" :href="dashboard()"
                                >My learning</Link
                            >
                        </li>
                    </ul>
                </div>
                <div>
                    <p class="mb-4 eyebrow">Company</p>
                    <ul class="space-y-2 text-sm">
                        <li>
                            <Link class="hover:text-primary" href="/terms"
                                >Terms</Link
                            >
                        </li>
                        <li>
                            <Link class="hover:text-primary" href="/privacy"
                                >Privacy</Link
                            >
                        </li>
                        <li>
                            <Link
                                class="hover:text-primary"
                                href="/refund-policy"
                                >Refund policy</Link
                            >
                        </li>
                    </ul>
                </div>
                <div>
                    <p class="mb-4 eyebrow">Say hello</p>
                    <a
                        class="text-sm font-medium text-primary underline underline-offset-4"
                        href="mailto:hello@brightside.test"
                        >hello@brightside.test</a
                    >
                    <p class="mt-4 text-xs leading-5 text-muted-foreground">
                        Made for people who like learning things properly.
                    </p>
                </div>
            </div>
            <div class="border-t">
                <div
                    class="container-page flex flex-col gap-2 py-5 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
                >
                    <p>© {{ new Date().getFullYear() }} Brightside.</p>
                    <p class="font-mono">Learn something good.</p>
                </div>
            </div>
        </footer>

        <Toaster />
    </div>
</template>
