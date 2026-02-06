<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge, DInput, DModal, DToggle } from '@/Components/ui';
import { TimelineGraph } from '@/Components/graph';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
    allScenes: {
        type: Array,
        default: () => [],
    },
});

const selectedSceneId = ref(null);
const viewMode = ref('graph'); // 'graph' or 'list'

const handleSceneClick = (scene) => {
    selectedSceneId.value = scene.id;
};

const handleSceneDblClick = (scene) => {
    router.visit(route('scenes.show', scene.id));
};

const activeTimeline = ref(props.universe.timelines?.[0] || null);
const showTimelineModal = ref(false);
const showEditTimelineModal = ref(false);
const editingTimeline = ref(null);

const timelineForm = useForm({
    name: '',
    description: '',
    color: '#1CB0F6',
    is_canon: false,
});

const editTimelineForm = useForm({
    name: '',
    description: '',
    color: '',
    is_canon: false,
});

const predefinedColors = [
    '#1CB0F6', // Primary blue
    '#58CC02', // Success green
    '#F59E0B', // Warning orange
    '#EF4444', // Error red
    '#8B5CF6', // Purple
    '#EC4899', // Pink
    '#14B8A6', // Teal
    '#6B7280', // Gray
];

const selectTimeline = (timeline) => {
    activeTimeline.value = timeline;
};

const createTimeline = () => {
    timelineForm.post(route('universes.timelines.store', props.universe.id), {
        onSuccess: () => {
            showTimelineModal.value = false;
            timelineForm.reset();
        },
    });
};

const openEditTimeline = (timeline) => {
    editingTimeline.value = timeline;
    editTimelineForm.name = timeline.name;
    editTimelineForm.description = timeline.description || '';
    editTimelineForm.color = timeline.color || '#1CB0F6';
    editTimelineForm.is_canon = timeline.is_canon;
    showEditTimelineModal.value = true;
};

const updateTimeline = () => {
    editTimelineForm.put(route('timelines.update', editingTimeline.value.id), {
        onSuccess: () => {
            showEditTimelineModal.value = false;
            editingTimeline.value = null;
        },
    });
};

const deleteTimeline = (timeline) => {
    if (confirm(`Are you sure you want to delete "${timeline.name}"?`)) {
        router.delete(route('timelines.destroy', timeline.id));
    }
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
                <div class="flex items-center gap-2">
                    <Link :href="route('universes.search.index', universe.id)">
                        <DButton variant="ghost" size="sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Search
                        </DButton>
                    </Link>
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
            </div>
        </template>

        <div class="flex h-[calc(100vh-theme(spacing.16))]">
            <!-- Sidebar -->
            <aside class="w-64 bg-white border-r border-border-gray p-4 overflow-y-auto">
                <!-- Timelines section -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-nunito font-bold text-text-primary">Timelines</h3>
                        <button
                            class="text-primary hover:text-primary-dark transition-colors"
                            @click="showTimelineModal = true"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="timeline in universe.timelines"
                            :key="timeline.id"
                            :class="[
                                'group w-full text-left px-3 py-2 rounded-lg transition-colors',
                                activeTimeline?.id === timeline.id
                                    ? 'bg-primary-light'
                                    : 'hover:bg-bg-light-gray',
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <button
                                    class="flex-1 flex items-center gap-2"
                                    @click="selectTimeline(timeline)"
                                >
                                    <span
                                        class="w-3 h-3 rounded-full shrink-0"
                                        :style="{ backgroundColor: timeline.color || '#1CB0F6' }"
                                    />
                                    <span
                                        :class="[
                                            'font-nunito font-medium truncate',
                                            activeTimeline?.id === timeline.id ? 'text-primary' : 'text-text-primary',
                                        ]"
                                    >
                                        {{ timeline.name }}
                                    </span>
                                    <DBadge v-if="timeline.is_canon" variant="success" size="sm">Canon</DBadge>
                                </button>
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                    <button
                                        class="p-1 text-text-hint hover:text-text-primary transition-colors"
                                        @click.stop="openEditTimeline(timeline)"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="text-xs text-text-hint mt-1 ml-5">
                                {{ timeline.scenes_count || 0 }} scenes
                                <!-- Branch origin indicator -->
                                <div v-if="timeline.branch_from" class="flex items-center gap-1 mt-1 text-text-hint">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                    </svg>
                                    <span class="truncate">
                                        from "{{ timeline.branch_from.title }}"
                                        <span v-if="timeline.branch_from.timeline" class="opacity-75">
                                            ({{ timeline.branch_from.timeline.name }})
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="universe.timelines?.length === 0" class="text-center py-4 text-text-hint">
                        <p class="text-sm">No timelines yet</p>
                        <button
                            class="text-primary text-sm font-semibold mt-1"
                            @click="showTimelineModal = true"
                        >
                            Create first timeline
                        </button>
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
                        <DButton @click="showTimelineModal = true">
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
                                <DBadge v-if="activeTimeline.is_canon" variant="success" size="sm">Canon</DBadge>
                            </h3>
                            <p v-if="activeTimeline.description" class="text-sm text-text-secondary">
                                {{ activeTimeline.description }}
                            </p>
                        </div>
                        <Link :href="route('timelines.scenes.index', activeTimeline.id)">
                            <DButton size="sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Manage Scenes
                            </DButton>
                        </Link>
                    </div>

                    <!-- Graph visualization -->
                    <div class="h-[calc(100%-4rem)] rounded-xl overflow-hidden border-2 border-border-light">
                        <TimelineGraph
                            v-if="allScenes.length > 0"
                            :timelines="universe.timelines"
                            :scenes="allScenes"
                            v-model:selected-scene-id="selectedSceneId"
                            @scene-click="handleSceneClick"
                            @scene-dblclick="handleSceneDblClick"
                        />
                        <DCard v-else class="h-full" padding="lg">
                            <div class="h-full flex items-center justify-center text-text-hint">
                                <div class="text-center">
                                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="font-nunito font-semibold">No Scenes Yet</p>
                                    <p class="text-sm">Add scenes to this timeline to visualize them</p>
                                    <Link :href="route('timelines.scenes.index', activeTimeline.id)" class="mt-4 inline-block">
                                        <DButton size="sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Add Scene
                                        </DButton>
                                    </Link>
                                </div>
                            </div>
                        </DCard>
                    </div>
                </div>
            </main>
        </div>

        <!-- Create Timeline Modal -->
        <DModal
            :show="showTimelineModal"
            title="Create New Timeline"
            @close="showTimelineModal = false"
        >
            <form @submit.prevent="createTimeline" class="space-y-4">
                <DInput
                    v-model="timelineForm.name"
                    label="Timeline Name"
                    placeholder="e.g., Canon, What If..."
                    :error="timelineForm.errors.name"
                    required
                />

                <DInput
                    v-model="timelineForm.description"
                    label="Description"
                    placeholder="What happens in this timeline?"
                    :error="timelineForm.errors.description"
                />

                <div>
                    <label class="block mb-2 font-nunito font-semibold text-sm text-text-primary">
                        Color
                    </label>
                    <div class="flex gap-2 flex-wrap">
                        <button
                            v-for="color in predefinedColors"
                            :key="color"
                            type="button"
                            :class="[
                                'w-8 h-8 rounded-full transition-all',
                                timelineForm.color === color ? 'ring-2 ring-offset-2 ring-primary' : '',
                            ]"
                            :style="{ backgroundColor: color }"
                            @click="timelineForm.color = color"
                        />
                    </div>
                </div>

                <DToggle
                    v-model="timelineForm.is_canon"
                    label="This is the canon timeline"
                />
            </form>

            <template #footer>
                <DButton variant="ghost" @click="showTimelineModal = false">
                    Cancel
                </DButton>
                <DButton
                    :loading="timelineForm.processing"
                    :disabled="!timelineForm.name"
                    @click="createTimeline"
                >
                    Create Timeline
                </DButton>
            </template>
        </DModal>

        <!-- Edit Timeline Modal -->
        <DModal
            :show="showEditTimelineModal"
            title="Edit Timeline"
            @close="showEditTimelineModal = false"
        >
            <form @submit.prevent="updateTimeline" class="space-y-4">
                <DInput
                    v-model="editTimelineForm.name"
                    label="Timeline Name"
                    placeholder="e.g., Canon, What If..."
                    :error="editTimelineForm.errors.name"
                    required
                />

                <DInput
                    v-model="editTimelineForm.description"
                    label="Description"
                    placeholder="What happens in this timeline?"
                    :error="editTimelineForm.errors.description"
                />

                <div>
                    <label class="block mb-2 font-nunito font-semibold text-sm text-text-primary">
                        Color
                    </label>
                    <div class="flex gap-2 flex-wrap">
                        <button
                            v-for="color in predefinedColors"
                            :key="color"
                            type="button"
                            :class="[
                                'w-8 h-8 rounded-full transition-all',
                                editTimelineForm.color === color ? 'ring-2 ring-offset-2 ring-primary' : '',
                            ]"
                            :style="{ backgroundColor: color }"
                            @click="editTimelineForm.color = color"
                        />
                    </div>
                </div>

                <DToggle
                    v-model="editTimelineForm.is_canon"
                    label="This is the canon timeline"
                />

                <div class="pt-4 border-t border-border-gray">
                    <button
                        type="button"
                        class="text-error text-sm font-semibold hover:text-error-dark transition-colors"
                        @click="deleteTimeline(editingTimeline)"
                    >
                        Delete this timeline
                    </button>
                </div>
            </form>

            <template #footer>
                <DButton variant="ghost" @click="showEditTimelineModal = false">
                    Cancel
                </DButton>
                <DButton
                    :loading="editTimelineForm.processing"
                    :disabled="!editTimelineForm.name"
                    @click="updateTimeline"
                >
                    Save Changes
                </DButton>
            </template>
        </DModal>
    </AuthenticatedLayout>
</template>
