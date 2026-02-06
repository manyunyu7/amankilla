<script setup>
import DButton from './DButton.vue';

const props = defineProps({
    icon: {
        type: String,
        default: 'document',
        validator: (v) => ['document', 'universe', 'timeline', 'scene', 'character', 'tag', 'search', 'import', 'explore'].includes(v),
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    actionText: {
        type: String,
        default: '',
    },
    actionVariant: {
        type: String,
        default: 'primary',
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
});

const emit = defineEmits(['action']);

const sizeClasses = {
    sm: {
        container: 'py-6',
        icon: 'w-12 h-12',
        iconWrapper: 'w-16 h-16',
        title: 'text-base',
        description: 'text-sm',
    },
    md: {
        container: 'py-8',
        icon: 'w-10 h-10',
        iconWrapper: 'w-20 h-20',
        title: 'text-xl',
        description: 'text-sm',
    },
    lg: {
        container: 'py-12',
        icon: 'w-12 h-12',
        iconWrapper: 'w-24 h-24',
        title: 'text-2xl',
        description: 'text-base',
    },
};

const icons = {
    document: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    universe: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    timeline: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    scene: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z',
    character: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    tag: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
    search: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
    import: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',
    explore: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9',
};
</script>

<template>
    <div :class="['text-center', sizeClasses[size].container]">
        <!-- Illustrated Icon -->
        <div
            :class="[
                'mx-auto rounded-full bg-primary-light flex items-center justify-center mb-4',
                sizeClasses[size].iconWrapper,
            ]"
        >
            <svg
                :class="['text-primary', sizeClasses[size].icon]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="icons[icon]"
                />
            </svg>
        </div>

        <!-- Title -->
        <h3 :class="['font-nunito font-bold text-text-primary mb-2', sizeClasses[size].title]">
            {{ title }}
        </h3>

        <!-- Description -->
        <p v-if="description" :class="['text-text-secondary mb-4', sizeClasses[size].description]">
            {{ description }}
        </p>

        <!-- Action Button -->
        <DButton
            v-if="actionText"
            :variant="actionVariant"
            @click="emit('action')"
        >
            <slot name="action-icon" />
            {{ actionText }}
        </DButton>

        <!-- Custom slot for additional content -->
        <slot />
    </div>
</template>
