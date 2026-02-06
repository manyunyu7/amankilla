<script setup>
import { DButton, DBadge, DTagChip } from '@/Components/ui';

const props = defineProps({
    tags: {
        type: Array,
        default: () => [],
    },
    characters: {
        type: Array,
        default: () => [],
    },
    timelines: {
        type: Array,
        default: () => [],
    },
    moods: {
        type: Array,
        default: () => [],
    },
    selectedTagIds: {
        type: Array,
        default: () => [],
    },
    selectedCharacterIds: {
        type: Array,
        default: () => [],
    },
    selectedMood: {
        type: String,
        default: '',
    },
    selectedTimelineId: {
        type: [Number, String],
        default: null,
    },
});

const emit = defineEmits([
    'update:selectedTagIds',
    'update:selectedCharacterIds',
    'update:selectedMood',
    'update:selectedTimelineId',
    'clear',
]);

const toggleTag = (tagId) => {
    const newIds = [...props.selectedTagIds];
    const index = newIds.indexOf(tagId);
    if (index === -1) {
        newIds.push(tagId);
    } else {
        newIds.splice(index, 1);
    }
    emit('update:selectedTagIds', newIds);
};

const toggleCharacter = (characterId) => {
    const newIds = [...props.selectedCharacterIds];
    const index = newIds.indexOf(characterId);
    if (index === -1) {
        newIds.push(characterId);
    } else {
        newIds.splice(index, 1);
    }
    emit('update:selectedCharacterIds', newIds);
};

const selectMood = (mood) => {
    emit('update:selectedMood', props.selectedMood === mood ? '' : mood);
};

const selectTimeline = (timelineId) => {
    emit('update:selectedTimelineId', props.selectedTimelineId === timelineId ? null : timelineId);
};

const hasActiveFilters = () => {
    return props.selectedTagIds.length > 0
        || props.selectedCharacterIds.length > 0
        || props.selectedMood
        || props.selectedTimelineId;
};

const moodColors = {
    tense: 'bg-red-100 text-red-700 border-red-200',
    happy: 'bg-green-100 text-green-700 border-green-200',
    sad: 'bg-blue-100 text-blue-700 border-blue-200',
    romantic: 'bg-pink-100 text-pink-700 border-pink-200',
    mysterious: 'bg-purple-100 text-purple-700 border-purple-200',
    peaceful: 'bg-cyan-100 text-cyan-700 border-cyan-200',
};
</script>

<template>
    <div class="filter-panel space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h4 class="font-nunito font-bold text-text-primary">Filters</h4>
            <button
                v-if="hasActiveFilters()"
                class="text-sm text-primary hover:underline"
                @click="emit('clear')"
            >
                Clear all
            </button>
        </div>

        <!-- Timelines -->
        <div v-if="timelines.length > 0">
            <label class="block text-sm font-nunito font-semibold text-text-hint mb-2">
                Timeline
            </label>
            <div class="space-y-1">
                <button
                    v-for="timeline in timelines"
                    :key="timeline.id"
                    type="button"
                    :class="[
                        'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-colors',
                        selectedTimelineId === timeline.id
                            ? 'bg-primary-light border-2 border-primary'
                            : 'border-2 border-transparent hover:bg-bg-light-gray',
                    ]"
                    @click="selectTimeline(timeline.id)"
                >
                    <span
                        class="w-3 h-3 rounded-full shrink-0"
                        :style="{ backgroundColor: timeline.color || '#1CB0F6' }"
                    />
                    <span class="font-nunito text-sm text-text-primary truncate flex-1">
                        {{ timeline.name }}
                    </span>
                    <span class="text-xs text-text-hint">{{ timeline.scenes_count }}</span>
                </button>
            </div>
        </div>

        <!-- Moods -->
        <div v-if="moods.length > 0">
            <label class="block text-sm font-nunito font-semibold text-text-hint mb-2">
                Mood
            </label>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="mood in moods"
                    :key="mood"
                    type="button"
                    :class="[
                        'px-3 py-1 rounded-full text-sm font-medium border transition-all',
                        selectedMood === mood
                            ? moodColors[mood] + ' ring-2 ring-offset-1'
                            : moodColors[mood] || 'bg-gray-100 text-gray-700 border-gray-200',
                    ]"
                    @click="selectMood(mood)"
                >
                    {{ mood }}
                </button>
            </div>
        </div>

        <!-- Characters -->
        <div v-if="characters.length > 0">
            <label class="block text-sm font-nunito font-semibold text-text-hint mb-2">
                Characters
            </label>
            <div class="space-y-1 max-h-40 overflow-y-auto">
                <button
                    v-for="character in characters"
                    :key="character.id"
                    type="button"
                    :class="[
                        'w-full flex items-center gap-2 px-3 py-2 rounded-lg text-left transition-colors',
                        selectedCharacterIds.includes(character.id)
                            ? 'bg-primary-light border-2 border-primary'
                            : 'border-2 border-transparent hover:bg-bg-light-gray',
                    ]"
                    @click="toggleCharacter(character.id)"
                >
                    <span
                        class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                        :style="{ backgroundColor: character.color || '#6B7280' }"
                    >
                        {{ character.name.charAt(0).toUpperCase() }}
                    </span>
                    <span class="font-nunito text-sm text-text-primary truncate flex-1">
                        {{ character.name }}
                    </span>
                    <span class="text-xs text-text-hint">{{ character.scenes_count }}</span>
                </button>
            </div>
        </div>

        <!-- Tags -->
        <div v-if="tags.length > 0">
            <label class="block text-sm font-nunito font-semibold text-text-hint mb-2">
                Tags
            </label>
            <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                <button
                    v-for="tag in tags"
                    :key="tag.id"
                    type="button"
                    :class="[
                        'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium border-2 transition-all',
                        selectedTagIds.includes(tag.id)
                            ? 'border-primary bg-primary-light text-primary'
                            : 'border-border-light bg-white text-text-secondary hover:border-primary',
                    ]"
                    @click="toggleTag(tag.id)"
                >
                    <span
                        class="w-2 h-2 rounded-full"
                        :style="{ backgroundColor: tag.color || '#6B7280' }"
                    />
                    {{ tag.name }}
                </button>
            </div>
        </div>
    </div>
</template>
