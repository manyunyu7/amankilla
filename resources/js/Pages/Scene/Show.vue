<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge, DInput, DToggle } from '@/Components/ui';

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
const showMetadata = ref(true);

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

const saveScene = () => {
    form.put(route('scenes.update', props.scene.id), {
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const wordCount = computed(() => {
    if (!form.content) return 0;
    const text = form.content.replace(/<[^>]*>/g, '');
    return text.split(/\s+/).filter(word => word.length > 0).length;
});

const moodOptions = [
    { value: '', label: 'No mood' },
    { value: 'tense', label: 'Tense' },
    { value: 'happy', label: 'Happy' },
    { value: 'sad', label: 'Sad' },
    { value: 'romantic', label: 'Romantic' },
    { value: 'mysterious', label: 'Mysterious' },
    { value: 'peaceful', label: 'Peaceful' },
];

const toggleCharacter = (characterId) => {
    const index = form.character_ids.indexOf(characterId);
    if (index === -1) {
        form.character_ids.push(characterId);
    } else {
        form.character_ids.splice(index, 1);
    }
};

const toggleTag = (tagId) => {
    const index = form.tag_ids.indexOf(tagId);
    if (index === -1) {
        form.tag_ids.push(tagId);
    } else {
        form.tag_ids.splice(index, 1);
    }
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

                            <!-- Content editor placeholder -->
                            <div class="min-h-[400px]">
                                <div v-if="isEditing" class="space-y-4">
                                    <label class="block font-nunito font-semibold text-sm text-text-primary">
                                        Content
                                    </label>
                                    <div class="border-2 border-border-gray rounded-xl p-4 bg-bg-light min-h-[300px]">
                                        <textarea
                                            v-model="form.content"
                                            class="w-full h-full min-h-[280px] bg-transparent border-0 focus:ring-0 font-nunito text-text-primary resize-none"
                                            placeholder="Write your scene content here... (TipTap editor coming soon)"
                                        ></textarea>
                                    </div>
                                    <div class="flex justify-between text-sm text-text-hint">
                                        <span>{{ wordCount.toLocaleString() }} words</span>
                                        <span class="text-xs">TipTap rich editor coming in Phase 6</span>
                                    </div>
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
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="font-nunito font-bold text-text-primary">Metadata</h3>
                                <button
                                    class="text-text-hint hover:text-text-primary transition-colors"
                                    @click="showMetadata = !showMetadata"
                                >
                                    <svg
                                        class="w-5 h-5 transition-transform"
                                        :class="{ 'rotate-180': !showMetadata }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>

                            <div v-show="showMetadata" class="space-y-4">
                                <!-- Summary -->
                                <div>
                                    <label class="block mb-1 text-sm font-semibold text-text-secondary">Summary</label>
                                    <textarea
                                        v-if="isEditing"
                                        v-model="form.summary"
                                        class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0 resize-none"
                                        rows="3"
                                        placeholder="Brief description..."
                                    ></textarea>
                                    <p v-else class="text-sm text-text-primary">
                                        {{ scene.summary || 'No summary' }}
                                    </p>
                                </div>

                                <!-- Date & Time -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block mb-1 text-sm font-semibold text-text-secondary">Date</label>
                                        <input
                                            v-if="isEditing"
                                            v-model="form.date"
                                            type="text"
                                            class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                                            placeholder="Nov 15"
                                        />
                                        <p v-else class="text-sm text-text-primary">{{ scene.date || '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block mb-1 text-sm font-semibold text-text-secondary">Time</label>
                                        <input
                                            v-if="isEditing"
                                            v-model="form.time"
                                            type="text"
                                            class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                                            placeholder="Evening"
                                        />
                                        <p v-else class="text-sm text-text-primary">{{ scene.time || '-' }}</p>
                                    </div>
                                </div>

                                <!-- Location -->
                                <div>
                                    <label class="block mb-1 text-sm font-semibold text-text-secondary">Location</label>
                                    <input
                                        v-if="isEditing"
                                        v-model="form.location"
                                        type="text"
                                        class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                                        placeholder="Coffee shop"
                                    />
                                    <p v-else class="text-sm text-text-primary">{{ scene.location || '-' }}</p>
                                </div>

                                <!-- Mood -->
                                <div>
                                    <label class="block mb-1 text-sm font-semibold text-text-secondary">Mood</label>
                                    <select
                                        v-if="isEditing"
                                        v-model="form.mood"
                                        class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                                    >
                                        <option v-for="opt in moodOptions" :key="opt.value" :value="opt.value">
                                            {{ opt.label }}
                                        </option>
                                    </select>
                                    <p v-else class="text-sm text-text-primary capitalize">{{ scene.mood || '-' }}</p>
                                </div>

                                <!-- POV -->
                                <div>
                                    <label class="block mb-1 text-sm font-semibold text-text-secondary">POV</label>
                                    <input
                                        v-if="isEditing"
                                        v-model="form.pov"
                                        type="text"
                                        class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                                        placeholder="Character name"
                                    />
                                    <p v-else class="text-sm text-text-primary">{{ scene.pov || '-' }}</p>
                                </div>

                                <!-- Characters -->
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-text-secondary">Characters</label>
                                    <div v-if="isEditing" class="flex flex-wrap gap-2">
                                        <button
                                            v-for="char in characters"
                                            :key="char.id"
                                            type="button"
                                            :class="[
                                                'px-2 py-1 rounded-lg text-sm font-medium transition-colors',
                                                form.character_ids.includes(char.id)
                                                    ? 'bg-primary text-white'
                                                    : 'bg-bg-light-gray text-text-secondary hover:bg-primary-light',
                                            ]"
                                            @click="toggleCharacter(char.id)"
                                        >
                                            {{ char.name }}
                                        </button>
                                    </div>
                                    <div v-else class="flex flex-wrap gap-1">
                                        <DBadge
                                            v-for="char in scene.characters"
                                            :key="char.id"
                                            variant="gray"
                                            size="sm"
                                        >
                                            {{ char.name }}
                                        </DBadge>
                                        <span v-if="!scene.characters?.length" class="text-sm text-text-hint">
                                            No characters
                                        </span>
                                    </div>
                                </div>

                                <!-- Tags -->
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-text-secondary">Tags</label>
                                    <div v-if="isEditing" class="flex flex-wrap gap-2">
                                        <button
                                            v-for="tag in tags"
                                            :key="tag.id"
                                            type="button"
                                            :class="[
                                                'px-2 py-1 rounded-lg text-sm font-medium transition-colors',
                                                form.tag_ids.includes(tag.id)
                                                    ? 'bg-success text-white'
                                                    : 'bg-bg-light-gray text-text-secondary hover:bg-success-light',
                                            ]"
                                            @click="toggleTag(tag.id)"
                                        >
                                            {{ tag.name }}
                                        </button>
                                    </div>
                                    <div v-else class="flex flex-wrap gap-1">
                                        <DBadge
                                            v-for="tag in scene.tags"
                                            :key="tag.id"
                                            variant="success"
                                            size="sm"
                                        >
                                            {{ tag.name }}
                                        </DBadge>
                                        <span v-if="!scene.tags?.length" class="text-sm text-text-hint">
                                            No tags
                                        </span>
                                    </div>
                                </div>

                                <!-- Branch Point -->
                                <div class="pt-4 border-t border-border-gray">
                                    <div v-if="isEditing">
                                        <DToggle
                                            v-model="form.is_branch_point"
                                            label="Mark as Branch Point"
                                        />
                                        <input
                                            v-if="form.is_branch_point"
                                            v-model="form.branch_question"
                                            type="text"
                                            class="w-full mt-2 px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                                            placeholder="What if...?"
                                        />
                                    </div>
                                    <div v-else-if="scene.is_branch_point" class="text-center">
                                        <DBadge variant="warning">Branch Point</DBadge>
                                        <p v-if="scene.branch_question" class="text-sm text-text-secondary mt-2 italic">
                                            "{{ scene.branch_question }}"
                                        </p>
                                    </div>
                                </div>

                                <!-- Word count -->
                                <div class="pt-4 border-t border-border-gray text-center">
                                    <p class="text-sm text-text-hint">
                                        {{ (scene.word_count || 0).toLocaleString() }} words
                                    </p>
                                </div>
                            </div>
                        </DCard>
                    </aside>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
