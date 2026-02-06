<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge, DInput, DModal, DTagChip } from '@/Components/ui';

const props = defineProps({
    timeline: {
        type: Object,
        required: true,
    },
    scenes: {
        type: Array,
        default: () => [],
    },
    allTags: {
        type: Array,
        default: () => [],
    },
});

// Tag filtering
const selectedTagIds = ref([]);
const showFilters = ref(false);

const filteredScenes = computed(() => {
    if (selectedTagIds.value.length === 0) {
        return props.scenes;
    }
    return props.scenes.filter(scene =>
        scene.tags?.some(tag => selectedTagIds.value.includes(tag.id))
    );
});

const toggleTagFilter = (tagId) => {
    const index = selectedTagIds.value.indexOf(tagId);
    if (index === -1) {
        selectedTagIds.value.push(tagId);
    } else {
        selectedTagIds.value.splice(index, 1);
    }
};

const clearFilters = () => {
    selectedTagIds.value = [];
};

const showSceneModal = ref(false);

const sceneForm = useForm({
    title: '',
    summary: '',
    date: '',
    time: '',
    location: '',
    mood: '',
});

const createScene = () => {
    sceneForm.post(route('timelines.scenes.store', props.timeline.id), {
        onSuccess: () => {
            showSceneModal.value = false;
            sceneForm.reset();
        },
    });
};

const deleteScene = (scene) => {
    if (confirm(`Are you sure you want to delete "${scene.title}"?`)) {
        router.delete(route('scenes.destroy', scene.id));
    }
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
    <Head :title="`${timeline.name} - Scenes`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('universes.show', timeline.universe.id)" class="text-text-hint hover:text-text-primary transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div class="flex items-center gap-2">
                        <span
                            class="w-4 h-4 rounded-full"
                            :style="{ backgroundColor: timeline.color || '#1CB0F6' }"
                        />
                        <div>
                            <h2 class="text-xl font-nunito font-bold text-text-primary flex items-center gap-2">
                                {{ timeline.name }} - Scenes
                                <DBadge v-if="timeline.is_canon" variant="success" size="sm">Canon</DBadge>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <DButton
                        v-if="allTags.length > 0"
                        variant="ghost"
                        @click="showFilters = !showFilters"
                        :class="selectedTagIds.length > 0 ? 'text-primary' : ''"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span v-if="selectedTagIds.length > 0">({{ selectedTagIds.length }})</span>
                    </DButton>
                    <DButton @click="showSceneModal = true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Scene
                    </DButton>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Tag Filter Panel -->
                <div v-if="showFilters && allTags.length > 0" class="mb-6">
                    <DCard padding="md">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="font-nunito font-bold text-text-primary">Filter by Tags</h4>
                            <button
                                v-if="selectedTagIds.length > 0"
                                class="text-sm text-primary hover:underline"
                                @click="clearFilters"
                            >
                                Clear all
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="tag in allTags"
                                :key="tag.id"
                                type="button"
                                :class="[
                                    'inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-nunito font-semibold text-sm transition-all border-2',
                                    selectedTagIds.includes(tag.id)
                                        ? 'border-primary bg-primary-light text-primary'
                                        : 'border-border-light bg-white text-text-secondary hover:border-primary',
                                ]"
                                @click="toggleTagFilter(tag.id)"
                            >
                                <span
                                    class="w-2.5 h-2.5 rounded-full"
                                    :style="{ backgroundColor: tag.color || '#6B7280' }"
                                ></span>
                                {{ tag.name }}
                            </button>
                        </div>
                        <p v-if="selectedTagIds.length > 0" class="mt-3 text-sm text-text-hint">
                            Showing {{ filteredScenes.length }} of {{ scenes.length }} scenes
                        </p>
                    </DCard>
                </div>

                <div v-if="scenes.length === 0" class="text-center py-12">
                    <DCard padding="lg">
                        <div class="w-20 h-20 mx-auto rounded-full bg-primary-light flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-nunito font-bold text-text-primary mb-2">
                            No Scenes Yet
                        </h3>
                        <p class="text-text-secondary mb-4">
                            Start writing your story by adding the first scene
                        </p>
                        <DButton @click="showSceneModal = true">
                            Create First Scene
                        </DButton>
                    </DCard>
                </div>

                <div v-else-if="filteredScenes.length === 0" class="text-center py-12">
                    <DCard padding="lg">
                        <div class="w-20 h-20 mx-auto rounded-full bg-primary-light flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-nunito font-bold text-text-primary mb-2">
                            No Matching Scenes
                        </h3>
                        <p class="text-text-secondary mb-4">
                            No scenes match the selected tag filters
                        </p>
                        <DButton variant="secondary" @click="clearFilters">
                            Clear Filters
                        </DButton>
                    </DCard>
                </div>

                <div v-else class="space-y-4">
                    <DCard
                        v-for="(scene, index) in filteredScenes"
                        :key="scene.id"
                        padding="md"
                        class="hover:shadow-lg transition-shadow"
                    >
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-light flex items-center justify-center">
                                <span class="font-nunito font-bold text-primary">{{ index + 1 }}</span>
                            </div>

                            <div class="flex-1 min-w-0">
                                <Link :href="route('scenes.show', scene.id)" class="block group">
                                    <h3 class="font-nunito font-bold text-text-primary group-hover:text-primary transition-colors">
                                        {{ scene.title }}
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-2 mt-1 text-sm text-text-hint">
                                        <span v-if="scene.date">{{ scene.date }}</span>
                                        <span v-if="scene.time">{{ scene.time }}</span>
                                        <span v-if="scene.location" class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            {{ scene.location }}
                                        </span>
                                    </div>
                                    <p v-if="scene.summary" class="text-sm text-text-secondary mt-2 line-clamp-2">
                                        {{ scene.summary }}
                                    </p>
                                </Link>

                                <div class="flex items-center gap-2 mt-3">
                                    <span
                                        v-if="scene.mood"
                                        :class="['px-2 py-0.5 rounded text-xs font-medium', moodColors[scene.mood] || 'bg-gray-100 text-gray-700']"
                                    >
                                        {{ scene.mood }}
                                    </span>
                                    <DBadge v-if="scene.is_branch_point" variant="warning" size="sm">
                                        Branch Point
                                    </DBadge>
                                    <span v-if="scene.word_count" class="text-xs text-text-hint">
                                        {{ scene.word_count.toLocaleString() }} words
                                    </span>
                                </div>

                                <!-- Scene Tags -->
                                <div v-if="scene.tags?.length" class="flex flex-wrap gap-1 mt-2">
                                    <DTagChip
                                        v-for="tag in scene.tags.slice(0, 4)"
                                        :key="tag.id"
                                        :tag="tag"
                                        size="sm"
                                        clickable
                                        @click="toggleTagFilter(tag.id)"
                                    />
                                    <span v-if="scene.tags.length > 4" class="text-xs text-text-hint self-center ml-1">
                                        +{{ scene.tags.length - 4 }} more
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1">
                                <Link :href="route('scenes.show', scene.id)">
                                    <DButton variant="ghost" size="sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </DButton>
                                </Link>
                                <button
                                    class="p-2 text-text-hint hover:text-error transition-colors"
                                    @click="deleteScene(scene)"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </DCard>
                </div>
            </div>
        </div>

        <!-- Create Scene Modal -->
        <DModal
            :show="showSceneModal"
            title="Create New Scene"
            @close="showSceneModal = false"
        >
            <form @submit.prevent="createScene" class="space-y-4">
                <DInput
                    v-model="sceneForm.title"
                    label="Scene Title"
                    placeholder="e.g., The First Meeting"
                    :error="sceneForm.errors.title"
                    required
                />

                <DInput
                    v-model="sceneForm.summary"
                    label="Summary"
                    placeholder="Brief description of what happens..."
                    :error="sceneForm.errors.summary"
                />

                <div class="grid grid-cols-2 gap-4">
                    <DInput
                        v-model="sceneForm.date"
                        label="Date"
                        placeholder="e.g., November 15"
                        :error="sceneForm.errors.date"
                    />

                    <DInput
                        v-model="sceneForm.time"
                        label="Time"
                        placeholder="e.g., Evening"
                        :error="sceneForm.errors.time"
                    />
                </div>

                <DInput
                    v-model="sceneForm.location"
                    label="Location"
                    placeholder="e.g., Coffee Shop"
                    :error="sceneForm.errors.location"
                />

                <div>
                    <label class="block mb-2 font-nunito font-semibold text-sm text-text-primary">
                        Mood
                    </label>
                    <select
                        v-model="sceneForm.mood"
                        class="w-full px-4 py-3 rounded-xl border-2 border-border-gray focus:border-primary focus:ring-0 font-nunito transition-colors"
                    >
                        <option value="">Select mood...</option>
                        <option value="tense">Tense</option>
                        <option value="happy">Happy</option>
                        <option value="sad">Sad</option>
                        <option value="romantic">Romantic</option>
                        <option value="mysterious">Mysterious</option>
                        <option value="peaceful">Peaceful</option>
                    </select>
                </div>
            </form>

            <template #footer>
                <DButton variant="ghost" @click="showSceneModal = false">
                    Cancel
                </DButton>
                <DButton
                    :loading="sceneForm.processing"
                    :disabled="!sceneForm.title"
                    @click="createScene"
                >
                    Create Scene
                </DButton>
            </template>
        </DModal>
    </AuthenticatedLayout>
</template>
