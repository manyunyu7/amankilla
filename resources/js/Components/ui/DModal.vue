<script setup>
import { watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value),
    },
    closable: {
        type: Boolean,
        default: true,
    },
    closeOnOverlay: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const sizeClasses = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    full: 'max-w-full mx-4',
};

const close = () => {
    if (props.closable) {
        emit('close');
    }
};

const handleOverlayClick = () => {
    if (props.closeOnOverlay) {
        close();
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Escape' && props.show && props.closable) {
        close();
    }
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            leave-active-class="transition-opacity duration-150 ease-in"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto"
            >
                <!-- Overlay -->
                <div
                    class="fixed inset-0 bg-black/50 transition-opacity"
                    @click="handleOverlayClick"
                />

                <!-- Modal container -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        leave-active-class="transition-all duration-150 ease-in"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                    >
                        <div
                            v-if="show"
                            :class="[
                                'relative w-full rounded-2xl bg-white shadow-xl',
                                sizeClasses[size],
                            ]"
                            @click.stop
                        >
                            <!-- Header -->
                            <div
                                v-if="title || closable"
                                class="flex items-center justify-between px-6 py-4 border-b border-border-gray"
                            >
                                <h3 class="text-lg font-nunito font-bold text-text-primary">
                                    {{ title }}
                                </h3>
                                <button
                                    v-if="closable"
                                    type="button"
                                    class="text-text-hint hover:text-text-primary transition-colors"
                                    @click="close"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Body -->
                            <div class="px-6 py-4">
                                <slot />
                            </div>

                            <!-- Footer -->
                            <div
                                v-if="$slots.footer"
                                class="px-6 py-4 border-t border-border-gray flex justify-end gap-3"
                            >
                                <slot name="footer" />
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
