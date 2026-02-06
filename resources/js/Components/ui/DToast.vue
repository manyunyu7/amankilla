<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    variant: {
        type: String,
        default: 'info',
        validator: (value) => ['info', 'success', 'warning', 'error'].includes(value),
    },
    message: {
        type: String,
        required: true,
    },
    duration: {
        type: Number,
        default: 3000,
    },
    closable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const isVisible = ref(false);
let timeoutId = null;

const variantClasses = computed(() => {
    const variants = {
        info: {
            bg: 'bg-primary',
            icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        },
        success: {
            bg: 'bg-success',
            icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        },
        warning: {
            bg: 'bg-warning',
            icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        },
        error: {
            bg: 'bg-error',
            icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
        },
    };
    return variants[props.variant];
});

const close = () => {
    isVisible.value = false;
    emit('close');
};

const startTimer = () => {
    if (props.duration > 0) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(close, props.duration);
    }
};

watch(() => props.show, (newVal) => {
    isVisible.value = newVal;
    if (newVal) {
        startTimer();
    }
});

onMounted(() => {
    if (props.show) {
        isVisible.value = true;
        startTimer();
    }
});
</script>

<template>
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        leave-active-class="transition-all duration-200 ease-in"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="isVisible"
            :class="[
                'fixed bottom-4 left-1/2 -translate-x-1/2 z-50',
                'flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg',
                'text-white font-nunito font-semibold',
                variantClasses.bg,
            ]"
        >
            <!-- Icon -->
            <svg
                class="w-5 h-5 shrink-0"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    :d="variantClasses.icon"
                />
            </svg>

            <!-- Message -->
            <span>{{ message }}</span>

            <!-- Close button -->
            <button
                v-if="closable"
                type="button"
                class="ml-2 hover:opacity-70 transition-opacity"
                @click="close"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    />
                </svg>
            </button>
        </div>
    </Transition>
</template>
