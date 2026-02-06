<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PageTransition from '@/Components/PageTransition.vue';
import ErrorBoundary from '@/Components/ErrorBoundary.vue';
import { DBottomNav } from '@/Components/ui';

const showingNavigationDropdown = ref(false);
const showUserDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};

// Close dropdown when clicking outside
const closeDropdown = () => {
    showUserDropdown.value = false;
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-bg-light">
            <nav class="bg-white border-b border-border-gray shadow-sm">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex items-center">
                            <!-- Logo -->
                            <Link :href="route('dashboard')" class="flex items-center gap-2">
                                <div class="w-9 h-9 rounded-xl bg-primary flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <span class="hidden sm:block font-nunito font-bold text-lg text-text-primary">
                                    StoryBranch
                                </span>
                            </Link>

                            <!-- Navigation Links -->
                            <div class="hidden sm:flex sm:items-center sm:ml-8 space-x-1">
                                <Link
                                    :href="route('dashboard')"
                                    :class="[
                                        'px-4 py-2 rounded-xl font-nunito font-semibold text-sm transition-colors',
                                        route().current('dashboard')
                                            ? 'bg-primary-light text-primary'
                                            : 'text-text-secondary hover:bg-bg-light-gray hover:text-text-primary',
                                    ]"
                                >
                                    My Universes
                                </Link>
                                <Link
                                    :href="route('explore.index')"
                                    :class="[
                                        'px-4 py-2 rounded-xl font-nunito font-semibold text-sm transition-colors',
                                        route().current('explore.*')
                                            ? 'bg-primary-light text-primary'
                                            : 'text-text-secondary hover:bg-bg-light-gray hover:text-text-primary',
                                    ]"
                                >
                                    Explore
                                </Link>
                            </div>
                        </div>

                        <!-- User Dropdown (Desktop) -->
                        <div class="hidden sm:flex sm:items-center">
                            <div class="relative">
                                <button
                                    @click="showUserDropdown = !showUserDropdown"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl transition-colors hover:bg-bg-light-gray"
                                >
                                    <!-- User Avatar -->
                                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm">
                                        {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-nunito font-medium text-sm text-text-primary">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <svg
                                        :class="['w-4 h-4 text-text-hint transition-transform', showUserDropdown ? 'rotate-180' : '']"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown Menu -->
                                <Transition
                                    enter-active-class="transition-all duration-200 ease-out"
                                    leave-active-class="transition-all duration-150 ease-in"
                                    enter-from-class="opacity-0 scale-95 -translate-y-2"
                                    enter-to-class="opacity-100 scale-100 translate-y-0"
                                    leave-from-class="opacity-100 scale-100 translate-y-0"
                                    leave-to-class="opacity-0 scale-95 -translate-y-2"
                                >
                                    <div
                                        v-if="showUserDropdown"
                                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border-2 border-border-gray py-2 z-50"
                                        @click="closeDropdown"
                                    >
                                        <!-- User Info -->
                                        <div class="px-4 py-2 border-b border-border-gray">
                                            <p class="font-nunito font-bold text-text-primary">
                                                {{ $page.props.auth.user.name }}
                                            </p>
                                            <p class="text-sm text-text-hint truncate">
                                                {{ $page.props.auth.user.email }}
                                            </p>
                                        </div>

                                        <!-- Menu Items -->
                                        <div class="py-1">
                                            <Link
                                                :href="route('profile.edit')"
                                                class="flex items-center gap-3 px-4 py-2 text-sm text-text-primary hover:bg-bg-light-gray transition-colors"
                                            >
                                                <svg class="w-4 h-4 text-text-hint" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                Profile Settings
                                            </Link>
                                        </div>

                                        <!-- Logout -->
                                        <div class="border-t border-border-gray pt-1">
                                            <button
                                                @click="logout"
                                                class="w-full flex items-center gap-3 px-4 py-2 text-sm text-error hover:bg-error/5 transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                Log Out
                                            </button>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <!-- Hamburger (Mobile) -->
                        <div class="flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="p-2 rounded-xl text-text-hint hover:text-text-primary hover:bg-bg-light-gray transition-colors"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Navigation Menu -->
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    leave-active-class="transition-all duration-150 ease-in"
                    enter-from-class="opacity-0 -translate-y-4"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-4"
                >
                    <div v-if="showingNavigationDropdown" class="sm:hidden border-t border-border-gray">
                        <div class="p-4 space-y-2">
                            <Link
                                :href="route('dashboard')"
                                :class="[
                                    'block px-4 py-3 rounded-xl font-nunito font-semibold transition-colors',
                                    route().current('dashboard')
                                        ? 'bg-primary-light text-primary'
                                        : 'text-text-secondary hover:bg-bg-light-gray',
                                ]"
                            >
                                My Universes
                            </Link>
                            <Link
                                :href="route('explore.index')"
                                :class="[
                                    'block px-4 py-3 rounded-xl font-nunito font-semibold transition-colors',
                                    route().current('explore.*')
                                        ? 'bg-primary-light text-primary'
                                        : 'text-text-secondary hover:bg-bg-light-gray',
                                ]"
                            >
                                Explore
                            </Link>
                        </div>

                        <!-- Mobile User Section -->
                        <div class="border-t border-border-gray p-4">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white font-bold">
                                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <p class="font-nunito font-bold text-text-primary">
                                        {{ $page.props.auth.user.name }}
                                    </p>
                                    <p class="text-sm text-text-hint">
                                        {{ $page.props.auth.user.email }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Link
                                    :href="route('profile.edit')"
                                    class="block px-4 py-3 rounded-xl text-text-secondary hover:bg-bg-light-gray font-nunito font-semibold transition-colors"
                                >
                                    Profile Settings
                                </Link>
                                <button
                                    @click="logout"
                                    class="w-full text-left px-4 py-3 rounded-xl text-error hover:bg-error/5 font-nunito font-semibold transition-colors"
                                >
                                    Log Out
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white border-b border-border-gray">
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="pb-20 sm:pb-0">
                <ErrorBoundary>
                    <PageTransition>
                        <slot />
                    </PageTransition>
                </ErrorBoundary>
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <DBottomNav />

        <!-- Click outside to close dropdown -->
        <div
            v-if="showUserDropdown"
            class="fixed inset-0 z-40"
            @click="closeDropdown"
        />
    </div>
</template>
