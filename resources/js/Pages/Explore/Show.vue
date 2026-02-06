<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { DButton, DCard, DBadge } from '@/Components/ui';
import { TimelineGraph } from '@/Components/graph';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
    allScenes: {
        type: Array,
        default: () => [],
    },
    canFork: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const isAuthenticated = !!page.props.auth?.user;
const Layout = isAuthenticated ? AuthenticatedLayout : GuestLayout;

const selectedSceneId = ref(null);
const forking = ref(false);

const handleSceneClick = (scene) => {
    selectedSceneId.value = scene.id;
};

const forkUniverse = () => {
    if (!props.canFork || forking.value) return;

    forking.value = true;
    router.post(route('explore.fork', props.universe.id), {}, {
        onFinish: () => {
            forking.value = false;
        },
    });
};
</script>

<template>
    <Head :title="universe.name" />

    <component :is="Layout">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('explore.index')" class="text-text-hint hover:text-text-primary transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-xl font-nunito font-bold text-text-primary">
                            {{ universe.name }}
                        </h2>
                        <p class="text-sm text-text-secondary">
                            by {{ universe.user?.name || universe.user?.username || 'Unknown' }}
                            <span v-if="universe.forked_from" class="text-text-hint">
                                · forked from
                                <Link
                                    :href="route('explore.show', universe.forked_from.id)"
                                    class="text-primary hover:underline"
                                >
                                    {{ universe.forked_from.name }}
                                </Link>
                            </span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <DBadge v-if="universe.fork_count > 0" variant="gray" size="sm">
                        {{ universe.fork_count }} {{ universe.fork_count === 1 ? 'fork' : 'forks' }}
                    </DBadge>
                    <DButton
                        v-if="canFork"
                        :loading="forking"
                        @click="forkUniverse"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        Fork
                    </DButton>
                    <Link v-if="!isAuthenticated" :href="route('login')">
                        <DButton>
                            Login to Fork
                        </DButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="flex h-[calc(100vh-theme(spacing.16))]">
            <!-- Sidebar -->
            <aside class="w-72 bg-white border-r border-border-gray p-4 overflow-y-auto">
                <!-- Description -->
                <div v-if="universe.description" class="mb-6">
                    <h3 class="font-nunito font-bold text-text-primary mb-2">About</h3>
                    <p class="text-sm text-text-secondary">{{ universe.description }}</p>
                </div>

                <!-- Timelines -->
                <div class="mb-6">
                    <h3 class="font-nunito font-bold text-text-primary mb-3">
                        Timelines
                        <span class="text-text-hint font-normal">({{ universe.timelines?.length || 0 }})</span>
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="timeline in universe.timelines"
                            :key="timeline.id"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg bg-bg-light"
                        >
                            <span
                                class="w-3 h-3 rounded-full shrink-0"
                                :style="{ backgroundColor: timeline.color || '#1CB0F6' }"
                            />
                            <span class="font-nunito text-sm text-text-primary truncate flex-1">
                                {{ timeline.name }}
                            </span>
                            <span class="text-xs text-text-hint">
                                {{ timeline.scenes_count || 0 }} scenes
                            </span>
                            <DBadge v-if="timeline.is_canon" variant="success" size="sm">Canon</DBadge>
                        </div>
                    </div>
                </div>

                <!-- Characters -->
                <div v-if="universe.characters?.length > 0" class="mb-6">
                    <h3 class="font-nunito font-bold text-text-primary mb-3">
                        Characters
                        <span class="text-text-hint font-normal">({{ universe.characters.length }})</span>
                    </h3>
                    <div class="space-y-2">
                        <div
                            v-for="character in universe.characters"
                            :key="character.id"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-bg-light-gray transition-colors"
                        >
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0"
                                :style="{ backgroundColor: character.color || '#6B7280' }"
                            >
                                {{ character.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <span class="font-nunito font-medium text-sm text-text-primary block truncate">
                                    {{ character.name }}
                                </span>
                                <span v-if="character.type" class="text-xs text-text-hint">
                                    {{ character.type }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tags -->
                <div v-if="universe.tags?.length > 0">
                    <h3 class="font-nunito font-bold text-text-primary mb-3">
                        Tags
                        <span class="text-text-hint font-normal">({{ universe.tags.length }})</span>
                    </h3>
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
                </div>
            </aside>

            <!-- Main Content - Graph -->
            <main class="flex-1 bg-bg-light p-6 overflow-auto">
                <div class="h-full rounded-xl overflow-hidden border-2 border-border-light">
                    <TimelineGraph
                        v-if="allScenes.length > 0"
                        :timelines="universe.timelines"
                        :scenes="allScenes"
                        v-model:selected-scene-id="selectedSceneId"
                        @scene-click="handleSceneClick"
                    />
                    <DCard v-else class="h-full" padding="lg">
                        <div class="h-full flex items-center justify-center text-text-hint">
                            <div class="text-center">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="font-nunito font-semibold">No Scenes Yet</p>
                                <p class="text-sm">This universe doesn't have any scenes</p>
                            </div>
                        </div>
                    </DCard>
                </div>
            </main>
        </div>
    </component>
</template>
