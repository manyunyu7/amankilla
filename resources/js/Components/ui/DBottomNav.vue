<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const navItems = [
    {
        name: 'My Universes',
        route: 'dashboard',
        icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        match: ['dashboard', 'universes.*'],
    },
    {
        name: 'Explore',
        route: 'explore.index',
        icon: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9',
        match: ['explore.*'],
    },
    {
        name: 'Profile',
        route: 'profile.edit',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        match: ['profile.*'],
    },
];

const isActive = (item) => {
    return item.match.some(pattern => {
        if (pattern.includes('*')) {
            const prefix = pattern.replace('.*', '');
            return page.url.startsWith(`/${prefix}`) || route().current(pattern);
        }
        return route().current(pattern);
    });
};
</script>

<template>
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-border-gray sm:hidden z-50 safe-area-bottom">
        <div class="flex items-center justify-around h-16">
            <Link
                v-for="item in navItems"
                :key="item.route"
                :href="route(item.route)"
                :class="[
                    'flex flex-col items-center justify-center flex-1 h-full transition-colors',
                    isActive(item)
                        ? 'text-primary'
                        : 'text-text-hint hover:text-text-secondary',
                ]"
            >
                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="item.icon"
                    />
                </svg>
                <span class="text-xs font-nunito font-semibold mt-1">
                    {{ item.name }}
                </span>
            </Link>
        </div>
    </nav>
</template>

<style scoped>
.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom, 0);
}
</style>
