<script setup>
import { ref, onErrorCaptured } from 'vue';
import { DButton, DCard } from './ui';

const props = defineProps({
    fallback: {
        type: String,
        default: 'Something went wrong',
    },
});

const error = ref(null);
const errorInfo = ref(null);

const resetError = () => {
    error.value = null;
    errorInfo.value = null;
};

onErrorCaptured((err, instance, info) => {
    error.value = err;
    errorInfo.value = info;
    // Return false to prevent error from propagating
    return false;
});
</script>

<template>
    <div v-if="error" class="p-4">
        <DCard variant="outlined" padding="lg">
            <div class="text-center">
                <!-- Error Icon -->
                <div class="w-16 h-16 mx-auto rounded-full bg-error/10 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <!-- Error Message -->
                <h3 class="text-lg font-nunito font-bold text-text-primary mb-2">
                    {{ fallback }}
                </h3>
                <p class="text-text-secondary text-sm mb-4">
                    An unexpected error occurred. Please try again.
                </p>

                <!-- Error Details (dev only) -->
                <details v-if="error.message" class="text-left mb-4">
                    <summary class="text-sm text-text-hint cursor-pointer hover:text-text-secondary">
                        Technical details
                    </summary>
                    <pre class="mt-2 p-3 bg-bg-light-gray rounded-lg text-xs text-error overflow-auto max-h-32">{{ error.message }}</pre>
                </details>

                <!-- Retry Button -->
                <DButton @click="resetError">
                    Try Again
                </DButton>
            </div>
        </DCard>
    </div>
    <slot v-else />
</template>
