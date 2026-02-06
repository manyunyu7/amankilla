<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DCard } from '@/Components/ui';
import { SearchInput, SearchResults, FilterPanel } from '@/Components/search';
import { useSearchStore } from '@/stores/search';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
});

const searchStore = useSearchStore();
const showFilters = ref(true);

onMounted(async () => {
    await searchStore.loadFilters(props.universe.id);
    // Initial search to show all scenes
    await searchStore.search(props.universe.id);
});

const handleSearch = async () => {
    await searchStore.search(props.universe.id);
};

const handleClearFilters = () => {
    searchStore.clearFilters();
    searchStore.search(props.universe.id);
};

// Watch filter changes for automatic search
watch(
    () => searchStore.filters,
    () => {
        searchStore.search(props.universe.id);
    },
    { deep: true }
);
</script>

<template>
    <Head :title="`Search - ${universe.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('universes.show', universe.id)"
                    class="text-text-hint hover:text-text-primary transition-colors"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div>
                    <h2 class="text-xl font-nunito font-bold text-text-primary">
                        Search {{ universe.name }}
                    </h2>
                    <p class="text-sm text-text-secondary">
                        Find scenes by text, characters, tags, or mood
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Search Input -->
            <div class="mb-6">
                <SearchInput
                    v-model="searchStore.query"
                    placeholder="Search scenes by title, content, or location..."
                    :loading="searchStore.loading"
                    @search="handleSearch"
                />
            </div>

            <div class="flex gap-6">
                <!-- Filters Sidebar -->
                <aside
                    v-if="showFilters"
                    class="w-72 shrink-0"
                >
                    <DCard padding="md">
                        <FilterPanel
                            :tags="searchStore.availableTags"
                            :characters="searchStore.availableCharacters"
                            :timelines="searchStore.availableTimelines"
                            :moods="searchStore.availableMoods"
                            :selected-tag-ids="searchStore.filters.tag_ids"
                            :selected-character-ids="searchStore.filters.character_ids"
                            :selected-mood="searchStore.filters.mood"
                            :selected-timeline-id="searchStore.filters.timeline_id"
                            @update:selected-tag-ids="searchStore.filters.tag_ids = $event"
                            @update:selected-character-ids="searchStore.filters.character_ids = $event"
                            @update:selected-mood="searchStore.filters.mood = $event"
                            @update:selected-timeline-id="searchStore.filters.timeline_id = $event"
                            @clear="handleClearFilters"
                        />
                    </DCard>
                </aside>

                <!-- Results -->
                <main class="flex-1 min-w-0">
                    <!-- Results header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <button
                                class="lg:hidden text-text-hint hover:text-text-primary transition-colors"
                                @click="showFilters = !showFilters"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                </svg>
                            </button>
                            <p class="font-nunito text-text-secondary">
                                <span class="font-bold text-text-primary">{{ searchStore.total }}</span>
                                {{ searchStore.total === 1 ? 'scene' : 'scenes' }} found
                            </p>
                        </div>

                        <!-- Sort dropdown -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-text-hint">Sort by:</label>
                            <select
                                v-model="searchStore.sort"
                                class="px-3 py-1.5 rounded-lg border-2 border-border-light bg-white font-nunito text-sm text-text-primary focus:outline-none focus:border-primary"
                                @change="handleSearch"
                            >
                                <option value="order">Scene Order</option>
                                <option value="title">Title</option>
                                <option value="date">Date</option>
                                <option value="created_at">Created</option>
                            </select>
                            <button
                                class="p-1.5 rounded-lg border-2 border-border-light hover:border-primary transition-colors"
                                @click="searchStore.sortDir = searchStore.sortDir === 'asc' ? 'desc' : 'asc'; handleSearch()"
                            >
                                <svg
                                    class="w-4 h-4 text-text-secondary transition-transform"
                                    :class="{ 'rotate-180': searchStore.sortDir === 'desc' }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <SearchResults
                        :results="searchStore.results"
                        :query="searchStore.query"
                        :loading="searchStore.loading"
                    />
                </main>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
