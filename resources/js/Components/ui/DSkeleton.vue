<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'text',
        validator: (value) => ['text', 'circle', 'rectangle', 'card'].includes(value),
    },
    width: {
        type: String,
        default: '100%',
    },
    height: {
        type: String,
        default: null,
    },
    lines: {
        type: Number,
        default: 1,
    },
});

const variantStyles = computed(() => {
    const styles = {
        text: {
            height: props.height || '1rem',
            borderRadius: '4px',
        },
        circle: {
            width: props.width,
            height: props.width,
            borderRadius: '50%',
        },
        rectangle: {
            height: props.height || '100px',
            borderRadius: '8px',
        },
        card: {
            height: props.height || '150px',
            borderRadius: '16px',
        },
    };
    return styles[props.variant];
});
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="i in lines"
            :key="i"
            class="skeleton"
            :style="{
                width: variant === 'text' && i === lines ? '70%' : width,
                ...variantStyles,
            }"
        />
    </div>
</template>
