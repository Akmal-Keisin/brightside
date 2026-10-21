<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Camera, UserRound } from '@lucide/vue';
import { computed, onBeforeUnmount, ref } from 'vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/profile';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Profile settings', href: edit() }],
    },
});

const page = usePage();
const user = computed(
    () =>
        page.props.auth.user as {
            name: string;
            email: string;
            avatar: string | null;
            headline: string | null;
            bio: string | null;
        },
);

const form = useForm({
    name: user.value.name,
    email: user.value.email,
    headline: user.value.headline ?? '',
    bio: user.value.bio ?? '',
    avatar: null as File | null,
});

const preview = ref<string | null>(user.value.avatar);
let objectUrl: string | null = null;

const pickAvatar = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null;
    form.avatar = file;

    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }

    objectUrl = file ? URL.createObjectURL(file) : null;
    preview.value = objectUrl ?? user.value.avatar;
};

onBeforeUnmount(() => {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }
});

const submit = () => {
    form.patch('/settings/profile', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.avatar = null;
        },
    });
};

const initials = computed(() =>
    user.value.name
        .split(' ')
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase(),
);
</script>

<template>
    <Head title="Profile settings" />

    <h1 class="sr-only">Profile settings</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            title="Your profile"
            description="This is how you appear across Brightside."
        />

        <form class="space-y-6" @submit.prevent="submit">
            <div class="flex flex-wrap items-center gap-5">
                <Avatar class="size-20 border">
                    <AvatarImage
                        v-if="preview"
                        :src="preview"
                        :alt="user.name"
                    />
                    <AvatarFallback
                        class="bg-accent font-display text-xl font-semibold text-accent-foreground"
                    >
                        {{ initials || 'BS' }}
                    </AvatarFallback>
                </Avatar>
                <div class="grid gap-2">
                    <Label
                        for="avatar"
                        class="inline-flex cursor-pointer items-center gap-2 text-sm font-medium text-primary"
                    >
                        <Camera class="size-4" /> Change photo
                    </Label>
                    <input
                        id="avatar"
                        type="file"
                        accept="image/*"
                        class="sr-only"
                        @change="pickAvatar"
                    />
                    <p class="text-xs text-muted-foreground">
                        JPG or PNG, up to 2 MB.
                    </p>
                    <InputError :message="form.errors.avatar" />
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        required
                        autocomplete="name"
                    />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                    />
                    <InputError :message="form.errors.email" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="headline">Headline</Label>
                <Input
                    id="headline"
                    v-model="form.headline"
                    maxlength="120"
                    placeholder="Product designer & curious learner"
                />
                <InputError :message="form.errors.headline" />
            </div>

            <div class="grid gap-2">
                <Label for="bio">Bio</Label>
                <textarea
                    id="bio"
                    v-model="form.bio"
                    rows="4"
                    maxlength="1000"
                    class="w-full rounded-control border bg-background px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/30"
                    placeholder="A few lines about what you are learning and making."
                />
                <InputError :message="form.errors.bio" />
            </div>

            <Button
                :disabled="form.processing"
                data-test="update-profile-button"
                >Save profile</Button
            >
        </form>
    </div>

    <div class="mt-10 flex items-center gap-2 text-sm text-muted-foreground">
        <UserRound class="size-4" />
        Your email controls sign-in and receipts.
    </div>

    <DeleteUser />
</template>
