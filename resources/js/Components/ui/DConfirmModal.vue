<script setup>
import { ref } from 'vue';
import DModal from './DModal.vue';
import DButton from './DButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirm Action',
    },
    message: {
        type: String,
        default: 'Are you sure you want to proceed?',
    },
    confirmText: {
        type: String,
        default: 'Confirm',
    },
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    variant: {
        type: String,
        default: 'error',
        validator: (v) => ['error', 'warning', 'primary'].includes(v),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm']);

const handleConfirm = () => {
    emit('confirm');
};

const handleClose = () => {
    if (!props.loading) {
        emit('close');
    }
};

const iconPaths = {
    error: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    primary: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};

const iconColors = {
    error: 'text-error bg-error/10',
    warning: 'text-warning bg-warning/10',
    primary: 'text-primary bg-primary/10',
};
</script>

<template>
    <DModal
        :show="show"
        :closable="!loading"
        size="sm"
        @close="handleClose"
    >
        <div class="text-center">
            <!-- Icon -->
            <div
                :class="[
                    'mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-4',
                    iconColors[variant],
                ]"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPaths[variant]" />
                </svg>
            </div>

            <!-- Title -->
            <h3 class="text-lg font-nunito font-bold text-text-primary mb-2">
                {{ title }}
            </h3>

            <!-- Message -->
            <p class="text-text-secondary text-sm mb-6">
                {{ message }}
            </p>

            <!-- Actions -->
            <div class="flex gap-3 justify-center">
                <DButton
                    variant="ghost"
                    :disabled="loading"
                    @click="handleClose"
                >
                    {{ cancelText }}
                </DButton>
                <DButton
                    :variant="variant"
                    :loading="loading"
                    @click="handleConfirm"
                >
                    {{ confirmText }}
                </DButton>
            </div>
        </div>
    </DModal>
</template>
