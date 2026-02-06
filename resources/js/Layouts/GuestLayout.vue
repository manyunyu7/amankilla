<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen flex flex-col bg-bg-light">
        <!-- Header -->
        <header class="w-full py-4 px-6">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-primary flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <span class="font-nunito font-bold text-xl text-text-primary">StoryBranch</span>
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-md">
                <!-- Card with 3D effect -->
                <div class="relative">
                    <!-- Shadow layer -->
                    <div class="absolute inset-0 bg-border-dark rounded-2xl" style="transform: translateY(4px)" />

                    <!-- Main card -->
                    <div class="relative bg-white rounded-2xl border-2 border-border-gray p-8">
                        <slot />
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-4 px-6 text-center text-sm text-text-hint">
            <p>&copy; {{ new Date().getFullYear() }} StoryBranch. All rights reserved.</p>
        </footer>
    </div>
</template>
