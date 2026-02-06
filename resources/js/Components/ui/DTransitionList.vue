<script setup>
import { TransitionGroup } from 'vue';

defineProps({
    tag: {
        type: String,
        default: 'div',
    },
    animation: {
        type: String,
        default: 'fade-up',
        validator: (value) => ['fade', 'fade-up', 'fade-down', 'slide-right', 'slide-left', 'scale'].includes(value),
    },
    stagger: {
        type: Number,
        default: 50, // milliseconds between each item
    },
    duration: {
        type: Number,
        default: 300,
    },
});

const getAnimationClasses = (animation) => {
    const animations = {
        'fade': {
            enterFrom: 'opacity-0',
            enterTo: 'opacity-100',
            leaveFrom: 'opacity-100',
            leaveTo: 'opacity-0',
        },
        'fade-up': {
            enterFrom: 'opacity-0 translate-y-4',
            enterTo: 'opacity-100 translate-y-0',
            leaveFrom: 'opacity-100 translate-y-0',
            leaveTo: 'opacity-0 -translate-y-2',
        },
        'fade-down': {
            enterFrom: 'opacity-0 -translate-y-4',
            enterTo: 'opacity-100 translate-y-0',
            leaveFrom: 'opacity-100 translate-y-0',
            leaveTo: 'opacity-0 translate-y-2',
        },
        'slide-right': {
            enterFrom: 'opacity-0 -translate-x-4',
            enterTo: 'opacity-100 translate-x-0',
            leaveFrom: 'opacity-100 translate-x-0',
            leaveTo: 'opacity-0 translate-x-4',
        },
        'slide-left': {
            enterFrom: 'opacity-0 translate-x-4',
            enterTo: 'opacity-100 translate-x-0',
            leaveFrom: 'opacity-100 translate-x-0',
            leaveTo: 'opacity-0 -translate-x-4',
        },
        'scale': {
            enterFrom: 'opacity-0 scale-95',
            enterTo: 'opacity-100 scale-100',
            leaveFrom: 'opacity-100 scale-100',
            leaveTo: 'opacity-0 scale-95',
        },
    };
    return animations[animation];
};
</script>

<template>
    <TransitionGroup
        :tag="tag"
        :enter-active-class="`transition-all ease-out`"
        :leave-active-class="`transition-all ease-in`"
        :enter-from-class="getAnimationClasses(animation).enterFrom"
        :enter-to-class="getAnimationClasses(animation).enterTo"
        :leave-from-class="getAnimationClasses(animation).leaveFrom"
        :leave-to-class="getAnimationClasses(animation).leaveTo"
        :style="{
            '--stagger': `${stagger}ms`,
            '--duration': `${duration}ms`,
        }"
        :css="true"
        @before-enter="(el) => { el.style.transitionDelay = `${el.dataset.index * stagger}ms`; el.style.transitionDuration = `${duration}ms`; }"
        @after-enter="(el) => { el.style.transitionDelay = ''; el.style.transitionDuration = ''; }"
        @before-leave="(el) => { el.style.transitionDuration = `${duration * 0.7}ms`; }"
    >
        <slot />
    </TransitionGroup>
</template>
