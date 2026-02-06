<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge, DInput, DModal } from '@/Components/ui';

const props = defineProps({
    timeline: {
        type: Object,
        required: true,
    },
    universe: {
        type: Object,
        required: true,
    },
});

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
    <Head :title="`${timeline.name} - ${universe.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('universes.show', universe.id)" class="text-text-hint hover:text-text-primary transition-colors">
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
                                {{ timeline.name }}
                                <DBadge v-if="timeline.is_canon" variant="success" size="sm">Canon</DBadge>
                            </h2>
                            <p v-if="timeline.description" class="text-sm text-text-secondary">
                                {{ timeline.description }}
                            </p>
                            <!-- Branch origin indicator -->
                            <p v-if="timeline.branch_from" class="text-sm text-text-hint flex items-center gap-1 mt-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                                </svg>
                                Branches from "{{ timeline.branch_from.title }}"
                                <span v-if="timeline.branch_from.timeline">
                                    in {{ timeline.branch_from.timeline.name }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <DButton @click="showSceneModal = true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Scene
                </DButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="!timeline.scenes || timeline.scenes.length === 0" class="text-center py-12">
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

                <div v-else class="space-y-4">
                    <DCard
                        v-for="(scene, index) in timeline.scenes"
                        :key="scene.id"
                        padding="md"
                        class="hover:shadow-lg transition-shadow"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Scene number -->
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-primary-light flex items-center justify-center">
                                <span class="font-nunito font-bold text-primary">{{ index + 1 }}</span>
                            </div>

                            <!-- Scene content -->
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
                            </div>

                            <!-- Actions -->
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
