<script setup>
import { ref, computed, watch } from 'vue';
import DTagChip from './DTagChip.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    availableTags: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Search or create tags...',
    },
    allowCreate: {
        type: Boolean,
        default: true,
    },
    label: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'create']);

const searchQuery = ref('');
const isOpen = ref(false);
const inputRef = ref(null);

const selectedTagIds = computed(() => props.modelValue.map(t => t.id));

const filteredTags = computed(() => {
    const query = searchQuery.value.toLowerCase().trim();
    if (!query) {
        return props.availableTags.filter(tag => !selectedTagIds.value.includes(tag.id));
    }
    return props.availableTags.filter(tag =>
        tag.name.toLowerCase().includes(query) && !selectedTagIds.value.includes(tag.id)
    );
});

const showCreateOption = computed(() => {
    if (!props.allowCreate || !searchQuery.value.trim()) return false;
    const query = searchQuery.value.toLowerCase().trim();
    return !props.availableTags.some(tag => tag.name.toLowerCase() === query);
});

const selectTag = (tag) => {
    emit('update:modelValue', [...props.modelValue, tag]);
    searchQuery.value = '';
};

const removeTag = (tag) => {
    emit('update:modelValue', props.modelValue.filter(t => t.id !== tag.id));
};

const createTag = () => {
    const name = searchQuery.value.trim();
    if (name) {
        emit('create', name);
        searchQuery.value = '';
    }
};

const handleFocus = () => {
    isOpen.value = true;
};

const handleBlur = () => {
    // Delay to allow click events to fire
    setTimeout(() => {
        isOpen.value = false;
    }, 200);
};

const handleKeydown = (e) => {
    if (e.key === 'Enter' && showCreateOption.value) {
        e.preventDefault();
        createTag();
    } else if (e.key === 'Backspace' && !searchQuery.value && props.modelValue.length > 0) {
        removeTag(props.modelValue[props.modelValue.length - 1]);
    }
};
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block mb-2 font-nunito font-semibold text-sm text-text-primary">
            {{ label }}
        </label>

        <div class="relative">
            <!-- Selected Tags & Input -->
            <div
                :class="[
                    'flex flex-wrap gap-2 p-2 rounded-xl border-2 bg-white transition-colors min-h-[48px]',
                    isOpen ? 'border-primary' : 'border-border-light',
                    error ? 'border-error' : '',
                ]"
                @click="inputRef?.focus()"
            >
                <DTagChip
                    v-for="tag in modelValue"
                    :key="tag.id"
                    :tag="tag"
                    size="sm"
                    removable
                    @remove="removeTag"
                />

                <input
                    ref="inputRef"
                    v-model="searchQuery"
                    type="text"
                    :placeholder="modelValue.length === 0 ? placeholder : ''"
                    class="flex-1 min-w-[120px] px-2 py-1 outline-none font-nunito text-text-primary placeholder-text-hint"
                    @focus="handleFocus"
                    @blur="handleBlur"
                    @keydown="handleKeydown"
                />
            </div>

            <!-- Dropdown -->
            <div
                v-if="isOpen && (filteredTags.length > 0 || showCreateOption)"
                class="absolute z-20 w-full mt-1 py-1 bg-white rounded-xl border-2 border-border-light shadow-lg max-h-60 overflow-y-auto"
            >
                <!-- Available Tags -->
                <button
                    v-for="tag in filteredTags"
                    :key="tag.id"
                    type="button"
                    class="w-full px-3 py-2 text-left hover:bg-bg-light-gray transition-colors flex items-center gap-2"
                    @mousedown.prevent="selectTag(tag)"
                >
                    <span
                        class="w-3 h-3 rounded-full shrink-0"
                        :style="{ backgroundColor: tag.color || '#6B7280' }"
                    ></span>
                    <span class="font-nunito text-text-primary">{{ tag.name }}</span>
                    <span v-if="tag.category" class="text-xs text-text-hint ml-auto">
                        {{ tag.category }}
                    </span>
                </button>

                <!-- Create Option -->
                <button
                    v-if="showCreateOption"
                    type="button"
                    class="w-full px-3 py-2 text-left hover:bg-primary-light transition-colors flex items-center gap-2 border-t border-border-light"
                    @mousedown.prevent="createTag"
                >
                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="font-nunito text-primary font-semibold">
                        Create "{{ searchQuery.trim() }}"
                    </span>
                </button>
            </div>
        </div>

        <p v-if="error" class="mt-1 text-sm text-error">
            {{ error }}
        </p>
    </div>
</template>
