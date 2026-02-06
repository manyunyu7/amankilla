<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { DButton, DInput } from '@/Components/ui';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    username: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const isFormValid = () => {
    return form.username && form.name && form.email &&
           form.password && form.password_confirmation &&
           form.password === form.password_confirmation;
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <!-- Title -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-nunito font-bold text-text-primary">
                Create your account
            </h1>
            <p class="text-text-secondary mt-1">
                Start building your branching narratives
            </p>
        </div>

        <!-- Register form -->
        <form @submit.prevent="submit" class="space-y-4">
            <DInput
                v-model="form.username"
                type="text"
                label="Username"
                placeholder="Choose a username"
                :error="form.errors.username"
                required
                autofocus
                autocomplete="username"
            />

            <DInput
                v-model="form.name"
                type="text"
                label="Display Name"
                placeholder="Your name"
                :error="form.errors.name"
                required
                autocomplete="name"
            />

            <DInput
                v-model="form.email"
                type="email"
                label="Email"
                placeholder="your@email.com"
                :error="form.errors.email"
                required
                autocomplete="email"
            />

            <DInput
                v-model="form.password"
                type="password"
                label="Password"
                placeholder="Create a password"
                :error="form.errors.password"
                required
                autocomplete="new-password"
            />

            <DInput
                v-model="form.password_confirmation"
                type="password"
                label="Confirm Password"
                placeholder="Confirm your password"
                :error="form.errors.password_confirmation"
                required
                autocomplete="new-password"
            />

            <!-- Password mismatch warning -->
            <p
                v-if="form.password && form.password_confirmation && form.password !== form.password_confirmation"
                class="text-error text-sm"
            >
                Passwords do not match
            </p>

            <DButton
                block
                :loading="form.processing"
                :disabled="form.processing || !isFormValid()"
                @click="submit"
            >
                Create Account
            </DButton>
        </form>

        <!-- Login link -->
        <div class="mt-6 text-center">
            <p class="text-text-secondary text-sm">
                Already have an account?
                <Link
                    :href="route('login')"
                    class="text-primary hover:text-primary-dark font-semibold transition-colors"
                >
                    Log in
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
