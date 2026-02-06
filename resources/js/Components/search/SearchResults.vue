<script setup>
import { Link } from '@inertiajs/vue3';
import { DCard, DBadge, DTagChip } from '@/Components/ui';

const props = defineProps({
    results: {
        type: Array,
        default: () => [],
    },
    query: {
        type: String,
        default: '',
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const highlightText = (text, query) => {
    if (!query || !text) return text;

    const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<mark class="bg-warning-light text-warning-dark px-0.5 rounded">$1</mark>');
};

const moodColors = {
    tense: 'bg-red-100 text-red-700',
    happy: 'bg-green-100 text-green-700',
    sad: 'bg-blue-100 text-blue-700',
    romantic: 'bg-pink-100 text-pink-700',
    mysterious: 'bg-purple-100 text-purple-700',
    peaceful: 'bg-cyan-100 text-cyan-700',
};
</script>

<template>
    <div class="search-results">
        <!-- Loading state -->
        <div v-if="loading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="animate-pulse">
                <DCard padding="md">
                    <div class="flex gap-4">
                        <div class="w-10 h-10 bg-bg-light-gray rounded-full"></div>
                        <div class="flex-1">
                            <div class="h-4 bg-bg-light-gray rounded w-1/3 mb-2"></div>
                            <div class="h-3 bg-bg-light-gray rounded w-2/3"></div>
                        </div>
                    </div>
                </DCard>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else-if="results.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-text-hint opacity-50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <p class="font-nunito font-semibold text-text-primary mb-1">No Results Found</p>
            <p class="text-sm text-text-hint">Try adjusting your search or filters</p>
        </div>

        <!-- Results list -->
        <div v-else class="space-y-3">
            <Link
                v-for="scene in results"
                :key="scene.id"
                :href="route('scenes.show', scene.id)"
                class="block"
            >
                <DCard padding="md" class="hover:shadow-lg hover:border-primary transition-all cursor-pointer">
                    <div class="flex items-start gap-4">
                        <!-- Timeline indicator -->
                        <div class="shrink-0">
                            <span
                                class="flex items-center justify-center w-10 h-10 rounded-full text-white font-bold"
                                :style="{ backgroundColor: scene.timeline?.color || '#1CB0F6' }"
                            >
                                {{ scene.order }}
                            </span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <!-- Title with highlighting -->
                            <h4
                                class="font-nunito font-bold text-text-primary"
                                v-html="highlightText(scene.title, query)"
                            ></h4>

                            <!-- Timeline name -->
                            <p class="text-xs text-text-hint mb-2">
                                {{ scene.timeline?.name || 'Unknown Timeline' }}
                            </p>

                            <!-- Match context -->
                            <p
                                v-if="scene.match_context && query"
                                class="text-sm text-text-secondary mb-2 line-clamp-2"
                                v-html="highlightText(scene.match_context, query)"
                            ></p>

                            <!-- Metadata -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    v-if="scene.mood"
                                    :class="['px-2 py-0.5 rounded text-xs font-medium', moodColors[scene.mood] || 'bg-gray-100 text-gray-700']"
                                >
                                    {{ scene.mood }}
                                </span>

                                <span v-if="scene.date" class="text-xs text-text-hint">
                                    {{ scene.date }}
                                </span>

                                <span v-if="scene.location" class="text-xs text-text-hint flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    {{ scene.location }}
                                </span>

                                <DBadge v-if="scene.is_branch_point" variant="warning" size="sm">
                                    Branch Point
                                </DBadge>
                            </div>

                            <!-- Tags -->
                            <div v-if="scene.tags?.length" class="flex flex-wrap gap-1 mt-2">
                                <DTagChip
                                    v-for="tag in scene.tags.slice(0, 3)"
                                    :key="tag.id"
                                    :tag="tag"
                                    size="sm"
                                />
                                <span v-if="scene.tags.length > 3" class="text-xs text-text-hint self-center">
                                    +{{ scene.tags.length - 3 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </DCard>
            </Link>
        </div>
    </div>
</template>
