<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { DButton, DInput, DToggle } from '@/Components/ui';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <!-- Title -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-nunito font-bold text-text-primary">
                Welcome back!
            </h1>
            <p class="text-text-secondary mt-1">
                Log in to continue your story
            </p>
        </div>

        <!-- Success status message -->
        <div
            v-if="status"
            class="mb-4 p-3 rounded-xl bg-success-light text-success-dark text-sm font-medium"
        >
            {{ status }}
        </div>

        <!-- Login form -->
        <form @submit.prevent="submit" class="space-y-4">
            <DInput
                v-model="form.email"
                type="email"
                label="Email"
                placeholder="your@email.com"
                :error="form.errors.email"
                required
                autofocus
                autocomplete="username"
            />

            <DInput
                v-model="form.password"
                type="password"
                label="Password"
                placeholder="Enter your password"
                :error="form.errors.password"
                required
                autocomplete="current-password"
            />

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="w-4 h-4 rounded border-border-gray text-primary focus:ring-primary focus:ring-offset-0"
                    />
                    <span class="text-sm text-text-secondary">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-primary hover:text-primary-dark font-semibold transition-colors"
                >
                    Forgot password?
                </Link>
            </div>

            <DButton
                block
                :loading="form.processing"
                :disabled="form.processing || !form.email || !form.password"
                @click="submit"
            >
                Log In
            </DButton>
        </form>

        <!-- Register link -->
        <div class="mt-6 text-center">
            <p class="text-text-secondary text-sm">
                Don't have an account?
                <Link
                    :href="route('register')"
                    class="text-primary hover:text-primary-dark font-semibold transition-colors"
                >
                    Sign up
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
