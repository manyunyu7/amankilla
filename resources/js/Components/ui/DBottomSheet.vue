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
    height: {
        type: String,
        default: 'auto',
        validator: (value) => ['auto', 'half', 'full'].includes(value),
    },
    closable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const heightClasses = {
    auto: 'max-h-[90vh]',
    half: 'h-1/2',
    full: 'h-full',
};

const close = () => {
    if (props.closable) {
        emit('close');
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
                class="fixed inset-0 z-50"
            >
                <!-- Overlay -->
                <div
                    class="fixed inset-0 bg-black/50 transition-opacity"
                    @click="close"
                />

                <!-- Bottom Sheet -->
                <Transition
                    enter-active-class="transition-transform duration-300 ease-out"
                    leave-active-class="transition-transform duration-200 ease-in"
                    enter-from-class="translate-y-full"
                    enter-to-class="translate-y-0"
                    leave-from-class="translate-y-0"
                    leave-to-class="translate-y-full"
                >
                    <div
                        v-if="show"
                        :class="[
                            'fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-xl overflow-hidden',
                            heightClasses[height],
                        ]"
                        @click.stop
                    >
                        <!-- Handle bar -->
                        <div class="flex justify-center pt-3 pb-1">
                            <div class="w-10 h-1 bg-border-dark rounded-full" />
                        </div>

                        <!-- Header -->
                        <div
                            v-if="title || closable"
                            class="flex items-center justify-between px-6 py-3 border-b border-border-gray"
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

                        <!-- Content -->
                        <div class="px-6 py-4 overflow-y-auto" style="max-height: calc(90vh - 100px)">
                            <slot />
                        </div>

                        <!-- Footer -->
                        <div
                            v-if="$slots.footer"
                            class="px-6 py-4 border-t border-border-gray bg-bg-light"
                        >
                            <slot name="footer" />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
