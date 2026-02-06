<script setup>
import { computed } from 'vue';

const props = defineProps({
    tag: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    removable: {
        type: Boolean,
        default: false,
    },
    clickable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['remove', 'click']);

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-3 py-1 text-sm',
        lg: 'px-4 py-1.5 text-base',
    };
    return sizes[props.size];
});

const dotSize = computed(() => {
    const sizes = {
        sm: 'w-2 h-2',
        md: 'w-2.5 h-2.5',
        lg: 'w-3 h-3',
    };
    return sizes[props.size];
});

const tagColor = computed(() => props.tag.color || '#6B7280');
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1.5 rounded-full font-nunito font-semibold border-2 border-border-light bg-white transition-all',
            sizeClasses,
            clickable ? 'cursor-pointer hover:border-primary hover:shadow-sm' : '',
        ]"
        @click="clickable && emit('click', tag)"
    >
        <span
            :class="['rounded-full shrink-0', dotSize]"
            :style="{ backgroundColor: tagColor }"
        ></span>
        <span class="text-text-primary">{{ tag.name }}</span>
        <button
            v-if="removable"
            type="button"
            class="ml-0.5 text-text-hint hover:text-error transition-colors"
            @click.stop="emit('remove', tag)"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </span>
</template>
