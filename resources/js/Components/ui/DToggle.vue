<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
});

const emit = defineEmits(['update:modelValue']);

const sizeClasses = computed(() => {
    const sizes = {
        sm: {
            track: 'w-8 h-5',
            thumb: 'w-3 h-3',
            translate: 'translate-x-4',
        },
        md: {
            track: 'w-11 h-6',
            thumb: 'w-4 h-4',
            translate: 'translate-x-6',
        },
        lg: {
            track: 'w-14 h-8',
            thumb: 'w-6 h-6',
            translate: 'translate-x-7',
        },
    };
    return sizes[props.size];
});

const toggle = () => {
    if (!props.disabled) {
        emit('update:modelValue', !props.modelValue);
    }
};
</script>

<template>
    <div class="flex items-center gap-3">
        <button
            type="button"
            role="switch"
            :aria-checked="modelValue"
            :disabled="disabled"
            :class="[
                'relative inline-flex shrink-0 rounded-full',
                'transition-colors duration-200 ease-in-out',
                'focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2',
                sizeClasses.track,
                modelValue ? 'bg-primary' : 'bg-border-dark',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer',
            ]"
            @click="toggle"
        >
            <!-- Thumb -->
            <span
                :class="[
                    'absolute top-1 left-1 inline-block rounded-full bg-white',
                    'transition-transform duration-200 ease-in-out shadow-sm',
                    sizeClasses.thumb,
                    modelValue ? sizeClasses.translate : 'translate-x-0',
                ]"
            />
        </button>

        <span
            v-if="label"
            :class="[
                'font-nunito text-text-primary',
                disabled ? 'opacity-50' : '',
            ]"
        >
            {{ label }}
        </span>
    </div>
</template>
