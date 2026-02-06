<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge } from '@/Components/ui';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
});

const activeTimeline = ref(props.universe.timelines?.[0] || null);

const selectTimeline = (timeline) => {
    activeTimeline.value = timeline;
};
</script>

<template>
    <Head :title="universe.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('dashboard')" class="text-text-hint hover:text-text-primary transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-xl font-nunito font-bold text-text-primary">
                            {{ universe.name }}
                        </h2>
                        <p v-if="universe.description" class="text-sm text-text-secondary">
                            {{ universe.description }}
                        </p>
                    </div>
                </div>
                <Link :href="route('universes.edit', universe.id)">
                    <DButton variant="ghost" size="sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </DButton>
                </Link>
            </div>
        </template>

        <div class="flex h-[calc(100vh-theme(spacing.16))]">
            <!-- Sidebar -->
            <aside class="w-64 bg-white border-r border-border-gray p-4 overflow-y-auto">
                <!-- Timelines section -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-nunito font-bold text-text-primary">Timelines</h3>
                        <button class="text-primary hover:text-primary-dark transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-2">
                        <button
                            v-for="timeline in universe.timelines"
                            :key="timeline.id"
                            :class="[
                                'w-full text-left px-3 py-2 rounded-lg transition-colors',
                                activeTimeline?.id === timeline.id
                                    ? 'bg-primary-light text-primary'
                                    : 'hover:bg-bg-light-gray text-text-primary',
                            ]"
                            @click="selectTimeline(timeline)"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-3 h-3 rounded-full"
                                    :style="{ backgroundColor: timeline.color || '#1CB0F6' }"
                                />
                                <span class="font-nunito font-medium truncate">{{ timeline.name }}</span>
                                <DBadge v-if="timeline.is_canon" variant="success" size="sm">Canon</DBadge>
                            </div>
                            <div class="text-xs text-text-hint mt-1 ml-5">
                                {{ timeline.scenes_count || 0 }} scenes
                            </div>
                        </button>
                    </div>
                    <div v-if="universe.timelines?.length === 0" class="text-center py-4 text-text-hint">
                        <p class="text-sm">No timelines yet</p>
                        <button class="text-primary text-sm font-semibold mt-1">Create first timeline</button>
                    </div>
                </div>

                <!-- Characters section -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-nunito font-bold text-text-primary">Characters</h3>
                        <button class="text-primary hover:text-primary-dark transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="character in universe.characters"
                            :key="character.id"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-bg-light-gray transition-colors cursor-pointer"
                        >
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm"
                                :style="{ backgroundColor: character.color || '#6B7280' }"
                            >
                                {{ character.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <span class="font-nunito font-medium text-sm text-text-primary">{{ character.name }}</span>
                                <span v-if="character.type" class="block text-xs text-text-hint">{{ character.type }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="universe.characters?.length === 0" class="text-center py-4 text-text-hint">
                        <p class="text-sm">No characters yet</p>
                        <button class="text-primary text-sm font-semibold mt-1">Add character</button>
                    </div>
                </div>

                <!-- Tags section -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-nunito font-bold text-text-primary">Tags</h3>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <DBadge
                            v-for="tag in universe.tags"
                            :key="tag.id"
                            :variant="tag.category === 'emotion' ? 'warning' : tag.category === 'event' ? 'success' : 'gray'"
                            size="sm"
                        >
                            {{ tag.name }}
                        </DBadge>
                    </div>
                    <div v-if="universe.tags?.length === 0" class="text-center py-4 text-text-hint">
                        <p class="text-sm">No tags yet</p>
                    </div>
                </div>
            </aside>

            <!-- Main content - Graph visualization area -->
            <main class="flex-1 bg-bg-light p-6 overflow-auto">
                <DCard v-if="!activeTimeline" padding="lg" class="h-full flex items-center justify-center">
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto rounded-full bg-primary-light flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-nunito font-bold text-text-primary mb-2">
                            Select a Timeline
                        </h3>
                        <p class="text-text-secondary mb-4">
                            Choose a timeline from the sidebar to view its scenes
                        </p>
                        <DButton @click="() => {}">
                            Create First Timeline
                        </DButton>
                    </div>
                </DCard>

                <div v-else class="h-full">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-nunito font-bold text-text-primary flex items-center gap-2">
                                <span
                                    class="w-4 h-4 rounded-full"
                                    :style="{ backgroundColor: activeTimeline.color || '#1CB0F6' }"
                                />
                                {{ activeTimeline.name }}
                            </h3>
                            <p v-if="activeTimeline.description" class="text-sm text-text-secondary">
                                {{ activeTimeline.description }}
                            </p>
                        </div>
                        <DButton size="sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Scene
                        </DButton>
                    </div>

                    <!-- Graph visualization placeholder -->
                    <DCard class="h-[calc(100%-4rem)]" padding="lg">
                        <div class="h-full flex items-center justify-center text-text-hint">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                <p class="font-nunito font-semibold">Timeline Graph</p>
                                <p class="text-sm">Vue Flow graph visualization will go here</p>
                            </div>
                        </div>
                    </DCard>
                </div>
            </main>
        </div>
    </AuthenticatedLayout>
</template>
