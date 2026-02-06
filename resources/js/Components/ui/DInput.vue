<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    label: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'focus', 'blur']);

const isFocused = ref(false);

const inputClasses = computed(() => {
    const base = [
        'w-full px-4 py-3 rounded-xl border-2 font-nunito',
        'transition-all duration-200 outline-none',
        'placeholder:text-text-hint',
    ];

    if (props.disabled) {
        base.push('bg-gray-100 cursor-not-allowed opacity-60');
    }

    if (props.error) {
        base.push('border-error focus:border-error focus:ring-2 focus:ring-error/20');
    } else if (isFocused.value) {
        base.push('border-primary focus:ring-2 focus:ring-primary/20');
    } else {
        base.push('border-border-gray hover:border-border-dark focus:border-primary focus:ring-2 focus:ring-primary/20');
    }

    return base;
});

const handleInput = (event) => {
    emit('update:modelValue', event.target.value);
};

const handleFocus = (event) => {
    isFocused.value = true;
    emit('focus', event);
};

const handleBlur = (event) => {
    isFocused.value = false;
    emit('blur', event);
};
</script>

<template>
    <div class="w-full">
        <!-- Label -->
        <label
            v-if="label"
            :class="[
                'block mb-2 font-nunito font-semibold text-sm',
                error ? 'text-error' : 'text-text-primary',
            ]"
        >
            {{ label }}
            <span v-if="required" class="text-error">*</span>
        </label>

        <!-- Input wrapper -->
        <div class="relative">
            <input
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :required="required"
                :class="inputClasses"
                @input="handleInput"
                @focus="handleFocus"
                @blur="handleBlur"
            />

            <!-- Slot for icons or actions -->
            <slot name="suffix" />
        </div>

        <!-- Error message -->
        <p
            v-if="error"
            class="mt-2 text-sm text-error font-nunito"
        >
            {{ error }}
        </p>
    </div>
</template>
