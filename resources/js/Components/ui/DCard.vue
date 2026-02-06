<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'elevated', 'outlined', 'interactive'].includes(value),
    },
    padding: {
        type: String,
        default: 'md',
        validator: (value) => ['none', 'sm', 'md', 'lg'].includes(value),
    },
    clickable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['click']);

const paddingClasses = computed(() => {
    const paddings = {
        none: '',
        sm: 'p-3',
        md: 'p-4',
        lg: 'p-6',
    };
    return paddings[props.padding];
});

const variantClasses = computed(() => {
    const variants = {
        default: {
            wrapper: '',
            card: 'bg-white border-2 border-border-gray',
            shadow: 'bg-border-dark',
        },
        elevated: {
            wrapper: '',
            card: 'bg-white border-2 border-border-gray shadow-lg',
            shadow: 'bg-border-dark',
        },
        outlined: {
            wrapper: '',
            card: 'bg-transparent border-2 border-border-gray',
            shadow: '',
        },
        interactive: {
            wrapper: 'cursor-pointer hover-lift',
            card: 'bg-white border-2 border-border-gray hover:border-primary transition-all duration-200',
            shadow: 'bg-border-dark',
        },
    };
    return variants[props.variant];
});

const handleClick = (event) => {
    if (props.clickable || props.variant === 'interactive') {
        emit('click', event);
    }
};
</script>

<template>
    <div
        :class="[
            'relative',
            variantClasses.wrapper,
            { 'active:translate-y-[2px]': clickable || variant === 'interactive' },
        ]"
        @click="handleClick"
    >
        <!-- Shadow -->
        <div
            v-if="variantClasses.shadow"
            :class="[
                'absolute inset-0 rounded-2xl',
                variantClasses.shadow,
            ]"
            style="transform: translateY(4px)"
        />

        <!-- Card -->
        <div
            :class="[
                'relative rounded-2xl',
                variantClasses.card,
                paddingClasses,
            ]"
        >
            <slot />
        </div>
    </div>
</template>
