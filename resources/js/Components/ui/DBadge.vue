<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'success', 'warning', 'error', 'gray'].includes(value),
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
});

const emit = defineEmits(['remove']);

const variantClasses = computed(() => {
    const variants = {
        primary: 'bg-primary-light text-primary border-primary/30',
        secondary: 'bg-blue-100 text-secondary border-secondary/30',
        success: 'bg-success-light text-success-dark border-success/30',
        warning: 'bg-amber-100 text-warning-dark border-warning/30',
        error: 'bg-red-100 text-error-dark border-error/30',
        gray: 'bg-bg-light-gray text-text-secondary border-border-gray',
    };
    return variants[props.variant];
});

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-3 py-1 text-sm',
        lg: 'px-4 py-1.5 text-base',
    };
    return sizes[props.size];
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1 rounded-full font-nunito font-semibold border',
            variantClasses,
            sizeClasses,
        ]"
    >
        <slot />
        <button
            v-if="removable"
            type="button"
            class="ml-1 hover:opacity-70 transition-opacity"
            @click="emit('remove')"
        >
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path
                    fill-rule="evenodd"
                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                />
            </svg>
        </button>
    </span>
</template>
