<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'success', 'warning', 'error', 'ghost'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    block: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

const variantClasses = computed(() => {
    const variants = {
        primary: {
            main: 'bg-primary text-white border-primary',
            shadow: 'bg-primary-dark',
            hover: 'hover:bg-primary/90',
        },
        secondary: {
            main: 'bg-secondary text-white border-secondary',
            shadow: 'bg-secondary-dark',
            hover: 'hover:bg-secondary/90',
        },
        success: {
            main: 'bg-success text-white border-success',
            shadow: 'bg-success-dark',
            hover: 'hover:bg-success/90',
        },
        warning: {
            main: 'bg-warning text-white border-warning',
            shadow: 'bg-warning-dark',
            hover: 'hover:bg-warning/90',
        },
        error: {
            main: 'bg-error text-white border-error',
            shadow: 'bg-error-dark',
            hover: 'hover:bg-error/90',
        },
        ghost: {
            main: 'bg-transparent text-text-primary border-border-gray',
            shadow: 'bg-border-dark',
            hover: 'hover:bg-gray-100',
        },
    };
    return variants[props.variant];
});

const sizeClasses = computed(() => {
    const sizes = {
        sm: 'px-4 py-2 text-sm min-h-[36px]',
        md: 'px-6 py-3 text-base min-h-[44px]',
        lg: 'px-8 py-4 text-lg min-h-[52px]',
    };
    return sizes[props.size];
});

const handleClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event);
    }
};
</script>

<template>
    <button
        :class="[
            'relative font-nunito font-bold transition-all duration-100 cursor-pointer',
            'active:translate-y-[3px] active:shadow-none',
            { 'w-full': block },
            { 'opacity-50 cursor-not-allowed': disabled || loading },
            disabled || loading ? '' : variantClasses.hover,
        ]"
        :disabled="disabled || loading"
        @click="handleClick"
    >
        <!-- Shadow layer -->
        <span
            :class="[
                'absolute inset-0 rounded-duo',
                variantClasses.shadow,
            ]"
            style="transform: translateY(4px)"
        />

        <!-- Main button -->
        <span
            :class="[
                'relative flex items-center justify-center gap-2',
                'rounded-duo border-2',
                sizeClasses,
                variantClasses.main,
            ]"
        >
            <!-- Loading spinner -->
            <svg
                v-if="loading"
                class="animate-spin h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
            </svg>
            <slot />
        </span>
    </button>
</template>
