<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const isVisible = ref(false);

// Trigger animation on mount
onMounted(() => {
    requestAnimationFrame(() => {
        isVisible.value = true;
    });
});

// Reset animation on page change
watch(() => page.url, () => {
    isVisible.value = false;
    requestAnimationFrame(() => {
        isVisible.value = true;
    });
});
</script>

<template>
    <Transition
        appear
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="isVisible">
            <slot />
        </div>
    </Transition>
</template>
