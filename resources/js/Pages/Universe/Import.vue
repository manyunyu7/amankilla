<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DInput, DToggle, DBadge } from '@/Components/ui';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
});

const content = ref('');
const previewData = ref(null);
const isPreviewLoading = ref(false);
const previewError = ref('');

const form = useForm({
    content: '',
    timeline_name: 'Imported Timeline',
    timeline_description: '',
    timeline_color: '#1CB0F6',
    is_canon: false,
    create_characters: true,
    create_tags: true,
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

const hasPreview = computed(() => previewData.value !== null);
const canImport = computed(() => hasPreview.value && previewData.value.scenes?.length > 0);

const handleFileUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const text = await file.text();
    content.value = text;
    previewContent();
};

const handlePaste = () => {
    // Allow paste into textarea
    setTimeout(() => {
        if (content.value.length > 100) {
            previewContent();
        }
    }, 100);
};

const previewContent = async () => {
    if (!content.value.trim()) {
        previewError.value = 'Please provide content to import';
        return;
    }

    isPreviewLoading.value = true;
    previewError.value = '';

    try {
        const response = await fetch(route('universes.import.preview', props.universe.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                content: content.value,
                timeline_name: form.timeline_name,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to preview content');
        }

        previewData.value = data;
        form.content = content.value;
    } catch (error) {
        previewError.value = error.message;
        previewData.value = null;
    } finally {
        isPreviewLoading.value = false;
    }
};

const submitImport = () => {
    form.post(route('universes.import', props.universe.id), {
        onSuccess: () => {
            router.visit(route('universes.show', props.universe.id));
        },
    });
};

const clearPreview = () => {
    content.value = '';
    previewData.value = null;
    previewError.value = '';
    form.reset();
};
</script>

<template>
    <Head :title="`Import - ${universe.name}`" />

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
                        Import Content
                    </h2>
                    <p class="text-sm text-text-secondary">
                        Import scenes from raw text files
                    </p>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Input Section -->
                <div class="space-y-4">
                    <DCard padding="lg">
                        <h3 class="font-nunito font-bold text-text-primary mb-4">Content Source</h3>

                        <!-- File Upload -->
                        <div class="mb-4">
                            <label class="block">
                                <span class="sr-only">Choose file</span>
                                <input
                                    type="file"
                                    accept=".md,.txt"
                                    class="block w-full text-sm text-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-light file:text-primary hover:file:bg-primary hover:file:text-white file:cursor-pointer file:transition-colors"
                                    @change="handleFileUpload"
                                />
                            </label>
                        </div>

                        <div class="text-center text-text-hint text-sm my-4">or paste content directly</div>

                        <!-- Text Area -->
                        <textarea
                            v-model="content"
                            rows="15"
                            placeholder="Paste your raw story content here..."
                            class="w-full px-4 py-3 rounded-xl border-2 border-border-light bg-white font-mono text-sm text-text-primary placeholder-text-hint focus:outline-none focus:border-primary transition-colors resize-none"
                            @paste="handlePaste"
                        ></textarea>

                        <div class="flex justify-between items-center mt-4">
                            <span class="text-sm text-text-hint">
                                {{ content.length.toLocaleString() }} characters
                            </span>
                            <DButton
                                :loading="isPreviewLoading"
                                :disabled="!content.trim()"
                                @click="previewContent"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Preview
                            </DButton>
                        </div>

                        <p v-if="previewError" class="mt-2 text-sm text-error">
                            {{ previewError }}
                        </p>
                    </DCard>

                    <!-- Import Options -->
                    <DCard v-if="hasPreview" padding="lg">
                        <h3 class="font-nunito font-bold text-text-primary mb-4">Import Settings</h3>

                        <div class="space-y-4">
                            <DInput
                                v-model="form.timeline_name"
                                label="Timeline Name"
                                placeholder="Name for the imported timeline"
                                :error="form.errors.timeline_name"
                                required
                            />

                            <DInput
                                v-model="form.timeline_description"
                                label="Description"
                                placeholder="Optional description"
                                :error="form.errors.timeline_description"
                            />

                            <div>
                                <label class="block mb-2 font-nunito font-semibold text-sm text-text-primary">
                                    Timeline Color
                                </label>
                                <div class="flex gap-2 flex-wrap">
                                    <button
                                        v-for="color in predefinedColors"
                                        :key="color"
                                        type="button"
                                        :class="[
                                            'w-8 h-8 rounded-full transition-all',
                                            form.timeline_color === color ? 'ring-2 ring-offset-2 ring-primary' : '',
                                        ]"
                                        :style="{ backgroundColor: color }"
                                        @click="form.timeline_color = color"
                                    />
                                </div>
                            </div>

                            <DToggle
                                v-model="form.is_canon"
                                label="This is the canon timeline"
                            />

                            <DToggle
                                v-model="form.create_characters"
                                label="Auto-create detected characters"
                            />

                            <DToggle
                                v-model="form.create_tags"
                                label="Auto-create detected tags"
                            />
                        </div>
                    </DCard>
                </div>

                <!-- Preview Section -->
                <div class="space-y-4">
                    <DCard padding="lg">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-nunito font-bold text-text-primary">Preview</h3>
                            <button
                                v-if="hasPreview"
                                class="text-sm text-text-hint hover:text-text-primary transition-colors"
                                @click="clearPreview"
                            >
                                Clear
                            </button>
                        </div>

                        <div v-if="!hasPreview" class="text-center py-12 text-text-hint">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="font-nunito font-semibold">No preview yet</p>
                            <p class="text-sm">Upload a file or paste content to preview</p>
                        </div>

                        <div v-else>
                            <!-- Stats -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                                <div class="text-center p-3 bg-bg-light rounded-lg">
                                    <div class="text-2xl font-bold text-primary">{{ previewData.stats.total_scenes }}</div>
                                    <div class="text-xs text-text-hint">Scenes</div>
                                </div>
                                <div class="text-center p-3 bg-bg-light rounded-lg">
                                    <div class="text-2xl font-bold text-success">{{ Object.keys(previewData.stats.characters).length }}</div>
                                    <div class="text-xs text-text-hint">Characters</div>
                                </div>
                                <div class="text-center p-3 bg-bg-light rounded-lg">
                                    <div class="text-2xl font-bold text-warning">{{ Object.keys(previewData.stats.tags).length }}</div>
                                    <div class="text-xs text-text-hint">Tags</div>
                                </div>
                                <div class="text-center p-3 bg-bg-light rounded-lg">
                                    <div class="text-2xl font-bold text-purple-500">{{ previewData.stats.branch_points }}</div>
                                    <div class="text-xs text-text-hint">Branch Points</div>
                                </div>
                            </div>

                            <!-- Characters -->
                            <div v-if="Object.keys(previewData.stats.characters).length > 0" class="mb-4">
                                <h4 class="text-sm font-semibold text-text-secondary mb-2">Characters</h4>
                                <div class="flex flex-wrap gap-2">
                                    <DBadge
                                        v-for="(count, name) in previewData.stats.characters"
                                        :key="name"
                                        variant="primary"
                                        size="sm"
                                    >
                                        {{ name }} ({{ count }})
                                    </DBadge>
                                </div>
                            </div>

                            <!-- Moods -->
                            <div v-if="Object.keys(previewData.stats.moods || {}).length > 0" class="mb-4">
                                <h4 class="text-sm font-semibold text-text-secondary mb-2">Moods Detected</h4>
                                <div class="flex flex-wrap gap-2">
                                    <DBadge
                                        v-for="(count, mood) in previewData.stats.moods"
                                        :key="mood"
                                        variant="warning"
                                        size="sm"
                                    >
                                        {{ mood }} ({{ count }})
                                    </DBadge>
                                </div>
                            </div>

                            <!-- Scene List -->
                            <div class="border-t border-border-light pt-4 mt-4">
                                <h4 class="text-sm font-semibold text-text-secondary mb-3">Scenes to Import</h4>
                                <div class="space-y-2 max-h-96 overflow-y-auto">
                                    <div
                                        v-for="(scene, index) in previewData.scenes"
                                        :key="index"
                                        class="p-3 bg-bg-light rounded-lg"
                                    >
                                        <div class="flex items-start gap-3">
                                            <span class="flex items-center justify-center w-6 h-6 bg-primary text-white text-xs font-bold rounded-full shrink-0">
                                                {{ index + 1 }}
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <h5 class="font-nunito font-semibold text-text-primary text-sm truncate">
                                                    {{ scene.title }}
                                                </h5>
                                                <p v-if="scene.location" class="text-xs text-text-hint">
                                                    {{ scene.location }}
                                                </p>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    <span
                                                        v-if="scene.mood"
                                                        class="px-1.5 py-0.5 text-xs bg-warning-light text-warning-dark rounded"
                                                    >
                                                        {{ scene.mood }}
                                                    </span>
                                                    <span
                                                        v-if="scene.is_branch_point"
                                                        class="px-1.5 py-0.5 text-xs bg-purple-100 text-purple-700 rounded"
                                                    >
                                                        branch point
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </DCard>

                    <!-- Import Button -->
                    <div v-if="canImport" class="flex gap-3">
                        <DButton
                            variant="ghost"
                            class="flex-1"
                            @click="clearPreview"
                        >
                            Cancel
                        </DButton>
                        <DButton
                            class="flex-1"
                            :loading="form.processing"
                            @click="submitImport"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import {{ previewData.stats.total_scenes }} Scenes
                        </DButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
