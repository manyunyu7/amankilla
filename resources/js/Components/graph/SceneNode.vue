<script setup>
import { computed } from 'vue';
import { Handle, Position } from '@vue-flow/core';

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    selected: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['view', 'edit']);

const scene = computed(() => props.data.scene);
const timeline = computed(() => props.data.timeline);
const isHighlighted = computed(() => props.data.isHighlighted !== false);
const animationDelay = computed(() => props.data.animationDelay || 0);

const nodeColor = computed(() => timeline.value?.color || '#1CB0F6');

const moodColors = {
    tense: '#EF4444',
    happy: '#22C55E',
    sad: '#3B82F6',
    romantic: '#EC4899',
    mysterious: '#8B5CF6',
    peaceful: '#14B8A6',
};

const moodColor = computed(() => moodColors[scene.value?.mood] || '#6B7280');
</script>

<template>
    <div
        :class="[
            'scene-node relative rounded-xl border-2 bg-white shadow-md cursor-pointer min-w-[180px] max-w-[220px]',
            'transition-all duration-300 ease-out',
            selected ? 'border-primary shadow-lg scale-105' : 'border-border-light hover:border-primary hover:shadow-lg',
            isHighlighted ? 'opacity-100' : 'opacity-40 hover:opacity-80',
            animationDelay > 0 ? 'animate-fade-in-up' : '',
        ]"
        :style="animationDelay > 0 ? { animationDelay: `${animationDelay}ms`, animationFillMode: 'forwards', opacity: 0 } : {}"
        @click="emit('view', scene)"
        @dblclick="emit('edit', scene)"
    >
        <!-- Connection handles -->
        <Handle
            type="target"
            :position="Position.Left"
            class="!w-3 !h-3 !bg-primary !border-2 !border-white"
        />
        <Handle
            type="source"
            :position="Position.Right"
            class="!w-3 !h-3 !bg-primary !border-2 !border-white"
        />

        <!-- Timeline color bar -->
        <div
            class="absolute top-0 left-0 right-0 h-1.5 rounded-t-xl"
            :style="{ backgroundColor: nodeColor }"
        />

        <!-- Content -->
        <div class="p-3 pt-4">
            <!-- Order number badge -->
            <div class="flex items-start justify-between gap-2 mb-2">
                <span
                    class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold text-white"
                    :style="{ backgroundColor: nodeColor }"
                >
                    {{ scene.order }}
                </span>
                <div class="flex items-center gap-1">
                    <!-- Branch point indicator -->
                    <span
                        v-if="scene.is_branch_point"
                        class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-warning-light"
                        title="Branch Point"
                    >
                        <svg class="w-3 h-3 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                    <!-- Mood indicator -->
                    <span
                        v-if="scene.mood"
                        class="w-2.5 h-2.5 rounded-full"
                        :style="{ backgroundColor: moodColor }"
                        :title="scene.mood"
                    />
                </div>
            </div>

            <!-- Title -->
            <h4 class="font-nunito font-bold text-sm text-text-primary truncate mb-1">
                {{ scene.title }}
            </h4>

            <!-- Metadata -->
            <div class="flex items-center gap-2 text-xs text-text-hint">
                <span v-if="scene.date">{{ scene.date }}</span>
                <span v-if="scene.location" class="truncate">
                    {{ scene.location }}
                </span>
            </div>

            <!-- Word count -->
            <div v-if="scene.word_count" class="mt-2 text-xs text-text-hint">
                {{ scene.word_count.toLocaleString() }} words
            </div>

            <!-- Tags preview -->
            <div v-if="scene.tags?.length" class="flex flex-wrap gap-1 mt-2">
                <span
                    v-for="tag in scene.tags.slice(0, 2)"
                    :key="tag.id"
                    class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-xs bg-bg-light-gray"
                >
                    <span
                        class="w-1.5 h-1.5 rounded-full"
                        :style="{ backgroundColor: tag.color || '#6B7280' }"
                    />
                    {{ tag.name }}
                </span>
                <span v-if="scene.tags.length > 2" class="text-xs text-text-hint">
                    +{{ scene.tags.length - 2 }}
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scene-node {
    font-family: 'Nunito', sans-serif;
}
</style>
