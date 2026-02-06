<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge, DInput } from '@/Components/ui';
import { SceneEditor, MetadataPanel } from '@/Components/editor';

const props = defineProps({
    scene: {
        type: Object,
        required: true,
    },
    timeline: {
        type: Object,
        required: true,
    },
    universe: {
        type: Object,
        required: true,
    },
    characters: {
        type: Array,
        default: () => [],
    },
    tags: {
        type: Array,
        default: () => [],
    },
    previousScene: {
        type: Object,
        default: null,
    },
    nextScene: {
        type: Object,
        default: null,
    },
});

const isEditing = ref(false);
const metadataCollapsed = ref(false);

const form = useForm({
    title: props.scene.title,
    content: props.scene.content || '',
    summary: props.scene.summary || '',
    date: props.scene.date || '',
    time: props.scene.time || '',
    location: props.scene.location || '',
    mood: props.scene.mood || '',
    pov: props.scene.pov || '',
    is_branch_point: props.scene.is_branch_point || false,
    branch_question: props.scene.branch_question || '',
    character_ids: props.scene.characters?.map(c => c.id) || [],
    tag_ids: props.scene.tags?.map(t => t.id) || [],
});

// Metadata form data (reactive object for MetadataPanel)
const metadataForm = reactive({
    summary: form.summary,
    date: form.date,
    time: form.time,
    location: form.location,
    mood: form.mood,
    pov: form.pov,
    is_branch_point: form.is_branch_point,
    branch_question: form.branch_question,
    character_ids: form.character_ids,
    tag_ids: form.tag_ids,
});

// Sync metadata form changes back to main form
const updateMetadata = (newMetadata) => {
    Object.assign(metadataForm, newMetadata);
    // Also update main form
    form.summary = newMetadata.summary;
    form.date = newMetadata.date;
    form.time = newMetadata.time;
    form.location = newMetadata.location;
    form.mood = newMetadata.mood;
    form.pov = newMetadata.pov;
    form.is_branch_point = newMetadata.is_branch_point;
    form.branch_question = newMetadata.branch_question;
    form.character_ids = newMetadata.character_ids;
    form.tag_ids = newMetadata.tag_ids;
};

const saveScene = () => {
    form.put(route('scenes.update', props.scene.id), {
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`${scene.title} - ${timeline.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('timelines.show', timeline.id)" class="text-text-hint hover:text-text-primary transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2 text-sm text-text-hint mb-1">
                            <span
                                class="w-3 h-3 rounded-full"
                                :style="{ backgroundColor: timeline.color || '#1CB0F6' }"
                            />
                            <span>{{ timeline.name }}</span>
                        </div>
                        <h2 class="text-xl font-nunito font-bold text-text-primary">
                            {{ scene.title }}
                        </h2>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Navigation between scenes -->
                    <Link
                        v-if="previousScene"
                        :href="route('scenes.show', previousScene.id)"
                        class="p-2 text-text-hint hover:text-text-primary transition-colors"
                        title="Previous scene"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <Link
                        v-if="nextScene"
                        :href="route('scenes.show', nextScene.id)"
                        class="p-2 text-text-hint hover:text-text-primary transition-colors"
                        title="Next scene"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <DButton
                        v-if="!isEditing"
                        @click="isEditing = true"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </DButton>
                    <template v-else>
                        <DButton variant="ghost" @click="isEditing = false">
                            Cancel
                        </DButton>
                        <DButton :loading="form.processing" @click="saveScene">
                            Save
                        </DButton>
                    </template>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex gap-6">
                    <!-- Main content area -->
                    <div class="flex-1">
                        <DCard padding="lg">
                            <!-- Title -->
                            <div v-if="isEditing" class="mb-4">
                                <DInput
                                    v-model="form.title"
                                    label="Title"
                                    :error="form.errors.title"
                                />
                            </div>

                            <!-- Content editor -->
                            <div class="min-h-[400px]">
                                <div v-if="isEditing">
                                    <label class="block font-nunito font-semibold text-sm text-text-primary mb-2">
                                        Content
                                    </label>
                                    <SceneEditor
                                        v-model="form.content"
                                        placeholder="Write your scene content here..."
                                        :editable="true"
                                    />
                                </div>

                                <div v-else>
                                    <div
                                        v-if="scene.content"
                                        class="prose prose-sm max-w-none font-nunito"
                                        v-html="scene.content"
                                    ></div>
                                    <div v-else class="text-center py-12 text-text-hint">
                                        <svg class="w-12 h-12 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <p class="font-nunito">No content yet</p>
                                        <DButton variant="secondary" size="sm" class="mt-2" @click="isEditing = true">
                                            Start Writing
                                        </DButton>
                                    </div>
                                </div>
                            </div>
                        </DCard>
                    </div>

                    <!-- Metadata sidebar -->
                    <aside class="w-80 shrink-0">
                        <DCard padding="md" class="sticky top-6">
                            <MetadataPanel
                                :modelValue="metadataForm"
                                :scene="scene"
                                :characters="characters"
                                :tags="tags"
                                :editable="isEditing"
                                v-model:collapsed="metadataCollapsed"
                                @update:modelValue="updateMetadata"
                            />
                        </DCard>
                    </aside>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
