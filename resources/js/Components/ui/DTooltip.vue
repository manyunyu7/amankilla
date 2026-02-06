<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    text: {
        type: String,
        required: true,
    },
    position: {
        type: String,
        default: 'top',
        validator: (value) => ['top', 'bottom', 'left', 'right'].includes(value),
    },
    delay: {
        type: Number,
        default: 200,
    },
});

const isVisible = ref(false);
let showTimeout = null;
let hideTimeout = null;

const positionClasses = computed(() => {
    const positions = {
        top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
        left: 'right-full top-1/2 -translate-y-1/2 mr-2',
        right: 'left-full top-1/2 -translate-y-1/2 ml-2',
    };
    return positions[props.position];
});

const arrowClasses = computed(() => {
    const arrows = {
        top: 'top-full left-1/2 -translate-x-1/2 border-t-text-primary border-x-transparent border-b-transparent',
        bottom: 'bottom-full left-1/2 -translate-x-1/2 border-b-text-primary border-x-transparent border-t-transparent',
        left: 'left-full top-1/2 -translate-y-1/2 border-l-text-primary border-y-transparent border-r-transparent',
        right: 'right-full top-1/2 -translate-y-1/2 border-r-text-primary border-y-transparent border-l-transparent',
    };
    return arrows[props.position];
});

const show = () => {
    clearTimeout(hideTimeout);
    showTimeout = setTimeout(() => {
        isVisible.value = true;
    }, props.delay);
};

const hide = () => {
    clearTimeout(showTimeout);
    hideTimeout = setTimeout(() => {
        isVisible.value = false;
    }, 100);
};
</script>

<template>
    <div
        class="relative inline-flex"
        @mouseenter="show"
        @mouseleave="hide"
        @focus="show"
        @blur="hide"
    >
        <slot />
        <Transition
            enter-active-class="transition-all duration-150 ease-out"
            leave-active-class="transition-all duration-100 ease-in"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="isVisible"
                :class="[
                    'absolute z-50 px-3 py-1.5',
                    'text-xs font-semibold text-white',
                    'bg-text-primary rounded-lg',
                    'whitespace-nowrap pointer-events-none',
                    positionClasses,
                ]"
            >
                {{ text }}
                <span
                    :class="[
                        'absolute border-[5px]',
                        arrowClasses,
                    ]"
                />
            </div>
        </Transition>
    </div>
</template>
