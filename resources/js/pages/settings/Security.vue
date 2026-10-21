<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    Check,
    Copy,
    KeyRound,
    ShieldCheck,
    ShieldOff,
    Smartphone,
    RefreshCw,
} from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/security';

const props = defineProps<{ passwordRules: string }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Security settings', href: edit() }],
    },
});

type SharedUser = {
    two_factor_enabled: boolean;
    two_factor_pending: boolean;
};

const page = usePage();
const user = computed(() => page.props.auth.user as SharedUser);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put('/settings/password', {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};

const qrSvg = ref<string | null>(null);
const secret = ref<string | null>(null);
const recoveryCodes = ref<string[]>([]);
const showCodes = ref(false);
const loading = ref(false);

const confirmForm = useForm({ code: '' });

const fetchSetup = async () => {
    loading.value = true;

    try {
        const [qrResponse, secretResponse] = await Promise.all([
            fetch('/user/two-factor-qr-code', {
                headers: { Accept: 'application/json' },
            }),
            fetch('/user/two-factor-secret-key', {
                headers: { Accept: 'application/json' },
            }),
        ]);

        if (qrResponse.ok) {
            const data = await qrResponse.json();
            qrSvg.value = data.svg ?? null;
        }

        if (secretResponse.ok) {
            const data = await secretResponse.json();
            secret.value = data.secretKey ?? null;
        }
    } finally {
        loading.value = false;
    }
};

const enable = () => {
    router.post(
        '/user/two-factor-authentication',
        {},
        {
            preserveScroll: true,
            onSuccess: () => fetchSetup(),
        },
    );
};

const confirmTwoFactor = () => {
    confirmForm.post('/user/confirmed-two-factor-authentication', {
        preserveScroll: true,
        onSuccess: () => {
            confirmForm.reset();
            qrSvg.value = null;
            secret.value = null;
        },
    });
};

const disable = () => {
    if (confirm('Turn off two-factor authentication?')) {
        router.delete('/user/two-factor-authentication', {
            preserveScroll: true,
        });
    }
};

const loadCodes = async () => {
    const response = await fetch('/user/two-factor-recovery-codes', {
        headers: { Accept: 'application/json' },
    });

    if (response.ok) {
        recoveryCodes.value = await response.json();
        showCodes.value = true;
    }
};

const regenerate = () => {
    if (confirm('Generate new recovery codes? Old codes will stop working.')) {
        router.post(
            '/user/two-factor-recovery-codes',
            {},
            {
                preserveScroll: true,
                onSuccess: () => loadCodes(),
            },
        );
    }
};

const copySecret = async () => {
    if (secret.value) {
        await navigator.clipboard.writeText(secret.value);
    }
};

onMounted(() => {
    if (user.value.two_factor_pending) {
        fetchSetup();
    }
});
</script>

<template>
    <Head title="Security settings" />

    <h1 class="sr-only">Security settings</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Update password"
            description="Use a long, unique password to keep your account safe."
        />

        <form class="space-y-6" @submit.prevent="updatePassword">
            <div class="grid gap-2">
                <Label for="current_password">Current password</Label>
                <PasswordInput
                    id="current_password"
                    v-model="passwordForm.current_password"
                    autocomplete="current-password"
                />
                <InputError :message="passwordForm.errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password">New password</Label>
                <PasswordInput
                    id="password"
                    v-model="passwordForm.password"
                    autocomplete="new-password"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="passwordForm.errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Confirm new password</Label>
                <PasswordInput
                    id="password_confirmation"
                    v-model="passwordForm.password_confirmation"
                    autocomplete="new-password"
                    :passwordrules="props.passwordRules"
                />
                <InputError
                    :message="passwordForm.errors.password_confirmation"
                />
            </div>

            <Button
                :disabled="passwordForm.processing"
                data-test="update-password-button"
                >Save password</Button
            >
        </form>
    </div>

    <div class="my-10 h-px bg-border" />

    <section class="space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2
                    class="flex items-center gap-2 font-display text-lg font-semibold"
                >
                    <ShieldCheck class="size-5 text-primary" /> Two-factor
                    authentication
                </h2>
                <p class="mt-1 max-w-lg text-sm text-muted-foreground">
                    Add a one-time code from an authenticator app. Recommended
                    for every account, required for admins.
                </p>
            </div>
            <span
                class="rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                :class="
                    user.two_factor_enabled
                        ? 'bg-success/12 text-success ring-success/20'
                        : 'bg-muted text-muted-foreground ring-border'
                "
            >
                {{ user.two_factor_enabled ? 'Enabled' : 'Off' }}
            </span>
        </div>

        <div
            v-if="user.two_factor_enabled"
            class="rounded-card border bg-card p-5 shadow-soft"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-10 items-center justify-center rounded-full bg-success/10 text-success"
                        ><ShieldCheck class="size-5"
                    /></span>
                    <div>
                        <p class="font-medium">Your account is protected</p>
                        <p class="text-sm text-muted-foreground">
                            A code is required at every sign-in.
                        </p>
                    </div>
                </div>
                <Button
                    variant="outline"
                    class="text-destructive"
                    @click="disable"
                    ><ShieldOff class="size-4" /> Disable</Button
                >
            </div>

            <div class="mt-5 border-t pt-4">
                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="showCodes ? (showCodes = false) : loadCodes()"
                    >
                        <KeyRound class="size-3.5" />
                        {{
                            showCodes
                                ? 'Hide recovery codes'
                                : 'View recovery codes'
                        }}
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        @click="regenerate"
                        ><RefreshCw class="size-3.5" /> Regenerate</Button
                    >
                </div>

                <div v-if="showCodes" class="mt-4 rounded-control bg-muted p-4">
                    <p class="mb-3 text-xs text-muted-foreground">
                        Keep these somewhere safe. Each code works once.
                    </p>
                    <div class="grid gap-1.5 font-mono text-sm sm:grid-cols-2">
                        <span
                            v-for="code in recoveryCodes"
                            :key="code"
                            class="rounded bg-background px-2 py-1"
                            >{{ code }}</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <div
            v-else-if="qrSvg || user.two_factor_pending"
            class="rounded-card border bg-card p-5 shadow-soft"
        >
            <div class="flex items-start gap-3">
                <span
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-accent text-accent-foreground"
                    ><Smartphone class="size-5"
                /></span>
                <div>
                    <p class="font-medium">
                        Scan this with your authenticator app
                    </p>
                    <p class="text-sm text-muted-foreground">
                        Google Authenticator, 1Password, or Authy all work.
                    </p>
                </div>
            </div>

            <div
                v-if="loading"
                class="mt-4 h-48 w-48 animate-pulse rounded-control bg-muted"
            />
            <div v-else class="mt-4 flex flex-wrap items-start gap-5">
                <div
                    v-if="qrSvg"
                    class="size-48 rounded-control bg-white p-2 ring-1 ring-border"
                    v-html="qrSvg"
                />
                <div v-if="secret" class="grid max-w-xs gap-2">
                    <p class="text-xs font-medium text-muted-foreground">
                        Or enter this key manually
                    </p>
                    <div
                        class="flex items-center gap-2 rounded-control border bg-background px-3 py-2"
                    >
                        <code class="flex-1 font-mono text-xs break-all">{{
                            secret
                        }}</code>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="copySecret"
                            ><Copy class="size-3.5"
                        /></Button>
                    </div>
                </div>
            </div>

            <form
                class="mt-5 flex flex-wrap items-end gap-3"
                @submit.prevent="confirmTwoFactor"
            >
                <div class="grid w-48 gap-2">
                    <Label for="two_factor_code">Six-digit code</Label>
                    <Input
                        id="two_factor_code"
                        v-model="confirmForm.code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="123456"
                    />
                    <InputError :message="confirmForm.errors.code" />
                </div>
                <Button type="submit" :disabled="confirmForm.processing"
                    ><Check class="size-4" /> Confirm and enable</Button
                >
                <Button type="button" variant="ghost" @click="disable"
                    >Cancel</Button
                >
            </form>
        </div>

        <div v-else class="rounded-card border border-dashed bg-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span
                        class="flex size-10 items-center justify-center rounded-full bg-secondary text-secondary-foreground"
                        ><Smartphone class="size-5"
                    /></span>
                    <div>
                        <p class="font-medium">Two-factor is off</p>
                        <p class="text-sm text-muted-foreground">
                            Protect your account with an authenticator app.
                        </p>
                    </div>
                </div>
                <Button @click="enable">Set up two-factor</Button>
            </div>
        </div>
    </section>
</template>
