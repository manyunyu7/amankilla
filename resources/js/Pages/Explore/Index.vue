<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { DButton, DCard, DBadge, DInput } from '@/Components/ui';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    universes: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', sort: 'latest' }),
    },
});

const page = usePage();
const isAuthenticated = !!page.props.auth?.user;
const Layout = isAuthenticated ? AuthenticatedLayout : GuestLayout;

const search = ref(props.filters.search || '');
const sort = ref(props.filters.sort || 'latest');

const applyFilters = () => {
    router.get(route('explore.index'), {
        search: search.value || undefined,
        sort: sort.value !== 'latest' ? sort.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

let searchTimeout = null;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

watch(sort, applyFilters);
</script>

<template>
    <Head title="Explore Public Universes" />

    <component :is="Layout">
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-nunito font-bold text-text-primary">
                        Explore
                    </h2>
                    <p class="text-sm text-text-secondary">
                        Discover public story universes
                    </p>
                </div>
                <Link v-if="isAuthenticated" :href="route('dashboard')">
                    <DButton variant="ghost" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        My Universes
                    </DButton>
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-text-hint" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search universes..."
                            class="w-full pl-12 pr-4 py-3 rounded-xl border-2 border-border-light bg-white font-nunito text-text-primary placeholder-text-hint focus:outline-none focus:border-primary transition-colors"
                        />
                    </div>
                </div>
                <select
                    v-model="sort"
                    class="px-4 py-3 rounded-xl border-2 border-border-light bg-white font-nunito text-text-primary focus:outline-none focus:border-primary"
                >
                    <option value="latest">Latest</option>
                    <option value="popular">Most Forked</option>
                    <option value="name">Alphabetical</option>
                </select>
            </div>

            <!-- Results count -->
            <p class="text-sm text-text-hint mb-4">
                {{ universes.total }} {{ universes.total === 1 ? 'universe' : 'universes' }} found
            </p>

            <!-- Universe Grid -->
            <div v-if="universes.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link
                    v-for="universe in universes.data"
                    :key="universe.id"
                    :href="route('explore.show', universe.id)"
                    class="block"
                >
                    <DCard padding="none" class="h-full hover:shadow-lg hover:border-primary transition-all">
                        <!-- Cover Image or Placeholder -->
                        <div class="h-32 bg-gradient-to-br from-primary to-primary-dark rounded-t-xl flex items-center justify-center">
                            <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>

                        <div class="p-4">
                            <h3 class="font-nunito font-bold text-text-primary truncate">
                                {{ universe.name }}
                            </h3>
                            <p class="text-sm text-text-hint mt-1">
                                by {{ universe.user?.name || universe.user?.username || 'Unknown' }}
                            </p>
                            <p v-if="universe.description" class="text-sm text-text-secondary mt-2 line-clamp-2">
                                {{ universe.description }}
                            </p>

                            <!-- Stats -->
                            <div class="flex items-center gap-4 mt-4 text-xs text-text-hint">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    {{ universe.timelines_count }} timelines
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ universe.characters_count }} characters
                                </span>
                            </div>

                            <!-- Fork badge -->
                            <div v-if="universe.fork_count > 0" class="mt-3">
                                <DBadge variant="primary" size="sm">
                                    {{ universe.fork_count }} {{ universe.fork_count === 1 ? 'fork' : 'forks' }}
                                </DBadge>
                            </div>
                        </div>
                    </DCard>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-text-hint opacity-50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="font-nunito font-semibold text-text-primary mb-1">No Universes Found</p>
                <p class="text-sm text-text-hint">
                    {{ search ? 'Try a different search term' : 'Be the first to share your story!' }}
                </p>
            </div>

            <!-- Pagination -->
            <div v-if="universes.last_page > 1" class="flex justify-center gap-2 mt-8">
                <Link
                    v-for="link in universes.links"
                    :key="link.label"
                    :href="link.url"
                    :class="[
                        'px-4 py-2 rounded-lg font-nunito text-sm transition-colors',
                        link.active
                            ? 'bg-primary text-white'
                            : link.url
                                ? 'bg-white border-2 border-border-light text-text-primary hover:border-primary'
                                : 'bg-bg-light-gray text-text-hint cursor-not-allowed',
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </component>
</template>
