<script setup>
import { ref, watch } from 'vue';
import { useDebounceFn } from '@vueuse/core';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Search scenes...',
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'search']);

const inputValue = ref(props.modelValue);

watch(() => props.modelValue, (newVal) => {
    inputValue.value = newVal;
});

const debouncedSearch = useDebounceFn(() => {
    emit('search');
}, 300);

const handleInput = (e) => {
    inputValue.value = e.target.value;
    emit('update:modelValue', inputValue.value);
    debouncedSearch();
};

const clear = () => {
    inputValue.value = '';
    emit('update:modelValue', '');
    emit('search');
};
</script>

<template>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg
                v-if="!loading"
                class="w-5 h-5 text-text-hint"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <svg
                v-else
                class="w-5 h-5 text-primary animate-spin"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <input
            type="text"
            :value="inputValue"
            :placeholder="placeholder"
            class="w-full pl-12 pr-10 py-3 rounded-xl border-2 border-border-light bg-white font-nunito text-text-primary placeholder-text-hint focus:outline-none focus:border-primary transition-colors"
            @input="handleInput"
        />

        <button
            v-if="inputValue"
            type="button"
            class="absolute inset-y-0 right-0 pr-4 flex items-center text-text-hint hover:text-text-primary transition-colors"
            @click="clear"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>
