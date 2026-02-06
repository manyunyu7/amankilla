<script setup>
import { ref, computed } from 'vue';
import { DToggle, DBadge } from '@/Components/ui';

const props = defineProps({
    // Form data (v-model compatible)
    modelValue: {
        type: Object,
        required: true,
    },
    // Scene data for display mode
    scene: {
        type: Object,
        default: null,
    },
    // Available characters for selection
    characters: {
        type: Array,
        default: () => [],
    },
    // Available tags for selection
    tags: {
        type: Array,
        default: () => [],
    },
    // Edit mode
    editable: {
        type: Boolean,
        default: false,
    },
    // Show/hide state
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'update:collapsed']);

// Local collapsed state
const isCollapsed = computed({
    get: () => props.collapsed,
    set: (value) => emit('update:collapsed', value),
});

// Form proxy for v-model
const form = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

// Update individual form field
const updateField = (field, value) => {
    emit('update:modelValue', {
        ...props.modelValue,
        [field]: value,
    });
};

// Mood options
const moodOptions = [
    { value: '', label: 'No mood' },
    { value: 'tense', label: 'Tense' },
    { value: 'happy', label: 'Happy' },
    { value: 'sad', label: 'Sad' },
    { value: 'romantic', label: 'Romantic' },
    { value: 'mysterious', label: 'Mysterious' },
    { value: 'peaceful', label: 'Peaceful' },
    { value: 'anxious', label: 'Anxious' },
    { value: 'hopeful', label: 'Hopeful' },
];

// Toggle character selection
const toggleCharacter = (characterId) => {
    const currentIds = [...(form.value.character_ids || [])];
    const index = currentIds.indexOf(characterId);
    if (index === -1) {
        currentIds.push(characterId);
    } else {
        currentIds.splice(index, 1);
    }
    updateField('character_ids', currentIds);
};

// Toggle tag selection
const toggleTag = (tagId) => {
    const currentIds = [...(form.value.tag_ids || [])];
    const index = currentIds.indexOf(tagId);
    if (index === -1) {
        currentIds.push(tagId);
    } else {
        currentIds.splice(index, 1);
    }
    updateField('tag_ids', currentIds);
};

// Check if character is selected
const isCharacterSelected = (characterId) => {
    return (form.value.character_ids || []).includes(characterId);
};

// Check if tag is selected
const isTagSelected = (tagId) => {
    return (form.value.tag_ids || []).includes(tagId);
};
</script>

<template>
    <div class="metadata-panel">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-nunito font-bold text-text-primary">Metadata</h3>
            <button
                type="button"
                class="text-text-hint hover:text-text-primary transition-colors"
                @click="isCollapsed = !isCollapsed"
            >
                <svg
                    class="w-5 h-5 transition-transform"
                    :class="{ 'rotate-180': isCollapsed }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </div>

        <div v-show="!isCollapsed" class="space-y-4">
            <!-- Summary -->
            <div>
                <label class="block mb-1 text-sm font-semibold text-text-secondary">Summary</label>
                <textarea
                    v-if="editable"
                    :value="form.summary"
                    class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0 resize-none"
                    rows="3"
                    placeholder="Brief description..."
                    @input="updateField('summary', $event.target.value)"
                ></textarea>
                <p v-else class="text-sm text-text-primary">
                    {{ scene?.summary || 'No summary' }}
                </p>
            </div>

            <!-- Date & Time -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block mb-1 text-sm font-semibold text-text-secondary">Date</label>
                    <input
                        v-if="editable"
                        :value="form.date"
                        type="text"
                        class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                        placeholder="Nov 15"
                        @input="updateField('date', $event.target.value)"
                    />
                    <p v-else class="text-sm text-text-primary">{{ scene?.date || '-' }}</p>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-semibold text-text-secondary">Time</label>
                    <input
                        v-if="editable"
                        :value="form.time"
                        type="text"
                        class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                        placeholder="Evening"
                        @input="updateField('time', $event.target.value)"
                    />
                    <p v-else class="text-sm text-text-primary">{{ scene?.time || '-' }}</p>
                </div>
            </div>

            <!-- Location -->
            <div>
                <label class="block mb-1 text-sm font-semibold text-text-secondary">Location</label>
                <input
                    v-if="editable"
                    :value="form.location"
                    type="text"
                    class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                    placeholder="Coffee shop"
                    @input="updateField('location', $event.target.value)"
                />
                <p v-else class="text-sm text-text-primary">{{ scene?.location || '-' }}</p>
            </div>

            <!-- Mood -->
            <div>
                <label class="block mb-1 text-sm font-semibold text-text-secondary">Mood</label>
                <select
                    v-if="editable"
                    :value="form.mood"
                    class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                    @change="updateField('mood', $event.target.value)"
                >
                    <option v-for="opt in moodOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </select>
                <p v-else class="text-sm text-text-primary capitalize">{{ scene?.mood || '-' }}</p>
            </div>

            <!-- POV -->
            <div>
                <label class="block mb-1 text-sm font-semibold text-text-secondary">POV</label>
                <input
                    v-if="editable"
                    :value="form.pov"
                    type="text"
                    class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                    placeholder="Character name"
                    @input="updateField('pov', $event.target.value)"
                />
                <p v-else class="text-sm text-text-primary">{{ scene?.pov || '-' }}</p>
            </div>

            <!-- Characters -->
            <div v-if="characters.length > 0 || scene?.characters?.length">
                <label class="block mb-2 text-sm font-semibold text-text-secondary">Characters</label>
                <div v-if="editable" class="flex flex-wrap gap-2">
                    <button
                        v-for="char in characters"
                        :key="char.id"
                        type="button"
                        :class="[
                            'px-2 py-1 rounded-lg text-sm font-medium transition-colors',
                            isCharacterSelected(char.id)
                                ? 'bg-primary text-white'
                                : 'bg-bg-light-gray text-text-secondary hover:bg-primary-light',
                        ]"
                        @click="toggleCharacter(char.id)"
                    >
                        {{ char.name }}
                    </button>
                    <span v-if="characters.length === 0" class="text-sm text-text-hint">
                        No characters available
                    </span>
                </div>
                <div v-else class="flex flex-wrap gap-1">
                    <DBadge
                        v-for="char in scene?.characters"
                        :key="char.id"
                        variant="gray"
                        size="sm"
                    >
                        {{ char.name }}
                    </DBadge>
                    <span v-if="!scene?.characters?.length" class="text-sm text-text-hint">
                        No characters
                    </span>
                </div>
            </div>

            <!-- Tags -->
            <div v-if="tags.length > 0 || scene?.tags?.length">
                <label class="block mb-2 text-sm font-semibold text-text-secondary">Tags</label>
                <div v-if="editable" class="flex flex-wrap gap-2">
                    <button
                        v-for="tag in tags"
                        :key="tag.id"
                        type="button"
                        :class="[
                            'px-2 py-1 rounded-lg text-sm font-medium transition-colors',
                            isTagSelected(tag.id)
                                ? 'bg-success text-white'
                                : 'bg-bg-light-gray text-text-secondary hover:bg-success-light',
                        ]"
                        @click="toggleTag(tag.id)"
                    >
                        {{ tag.name }}
                    </button>
                    <span v-if="tags.length === 0" class="text-sm text-text-hint">
                        No tags available
                    </span>
                </div>
                <div v-else class="flex flex-wrap gap-1">
                    <DBadge
                        v-for="tag in scene?.tags"
                        :key="tag.id"
                        variant="success"
                        size="sm"
                    >
                        {{ tag.name }}
                    </DBadge>
                    <span v-if="!scene?.tags?.length" class="text-sm text-text-hint">
                        No tags
                    </span>
                </div>
            </div>

            <!-- Branch Point -->
            <div class="pt-4 border-t border-border-gray">
                <div v-if="editable">
                    <DToggle
                        :modelValue="form.is_branch_point"
                        label="Mark as Branch Point"
                        @update:modelValue="updateField('is_branch_point', $event)"
                    />
                    <input
                        v-if="form.is_branch_point"
                        :value="form.branch_question"
                        type="text"
                        class="w-full mt-2 px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                        placeholder="What if...?"
                        @input="updateField('branch_question', $event.target.value)"
                    />
                </div>
                <div v-else-if="scene?.is_branch_point" class="text-center">
                    <DBadge variant="warning">Branch Point</DBadge>
                    <p v-if="scene?.branch_question" class="text-sm text-text-secondary mt-2 italic">
                        "{{ scene.branch_question }}"
                    </p>
                </div>
            </div>

            <!-- Word count (display only) -->
            <div v-if="scene?.word_count" class="pt-4 border-t border-border-gray text-center">
                <p class="text-sm text-text-hint">
                    {{ scene.word_count.toLocaleString() }} words
                </p>
            </div>
        </div>
    </div>
</template>
