<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    DButton,
    DCard,
    DInput,
    DBadge,
    DToggle,
    DToast,
    DModal,
    DBottomSheet,
    DTagChip,
    DTagSelector,
    DSkeleton,
    DTooltip,
    DConfirmModal,
    DEmptyState,
} from '@/Components/ui';

// State for interactive demos
const inputValue = ref('');
const toggleValue = ref(false);
const showModal = ref(false);
const showBottomSheet = ref(false);
const showConfirmModal = ref(false);
const showToast = ref(false);
const selectedTags = ref([]);

const sampleTags = [
    { id: 1, name: 'Action', color: '#EF4444', category: 'event' },
    { id: 2, name: 'Romance', color: '#EC4899', category: 'theme' },
    { id: 3, name: 'Happy', color: '#58CC02', category: 'emotion' },
    { id: 4, name: 'Sad', color: '#1CB0F6', category: 'emotion' },
];

const handleConfirm = () => {
    showConfirmModal.value = false;
    showToast.value = true;
    setTimeout(() => {
        showToast.value = false;
    }, 3000);
};
</script>

<template>
    <Head title="Component Sandbox" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-nunito font-bold text-text-primary">
                Component Sandbox
            </h2>
            <p class="text-sm text-text-secondary">
                Interactive showcase of all Duolingo-style UI components
            </p>
        </template>

        <div class="py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <!-- Buttons -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Buttons</h3>
                    <div class="flex flex-wrap gap-3">
                        <DButton>Primary</DButton>
                        <DButton variant="secondary">Secondary</DButton>
                        <DButton variant="success">Success</DButton>
                        <DButton variant="error">Error</DButton>
                        <DButton variant="warning">Warning</DButton>
                        <DButton variant="ghost">Ghost</DButton>
                        <DButton variant="outline">Outline</DButton>
                        <DButton disabled>Disabled</DButton>
                        <DButton loading>Loading</DButton>
                    </div>
                    <div class="flex flex-wrap gap-3 mt-4">
                        <DButton size="sm">Small</DButton>
                        <DButton size="md">Medium</DButton>
                        <DButton size="lg">Large</DButton>
                    </div>
                </DCard>

                <!-- Inputs -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Inputs</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <DInput
                            v-model="inputValue"
                            label="Text Input"
                            placeholder="Enter some text..."
                        />
                        <DInput
                            label="With Error"
                            placeholder="Invalid input"
                            error="This field has an error"
                        />
                        <DInput
                            label="With Hint"
                            placeholder="Enter value"
                            hint="This is a helpful hint"
                        />
                        <DInput
                            label="Disabled"
                            placeholder="Cannot edit"
                            disabled
                        />
                    </div>
                </DCard>

                <!-- Badges -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Badges</h3>
                    <div class="flex flex-wrap gap-3">
                        <DBadge>Default</DBadge>
                        <DBadge variant="primary">Primary</DBadge>
                        <DBadge variant="success">Success</DBadge>
                        <DBadge variant="error">Error</DBadge>
                        <DBadge variant="warning">Warning</DBadge>
                        <DBadge variant="gray">Gray</DBadge>
                    </div>
                    <div class="flex flex-wrap gap-3 mt-4">
                        <DBadge size="sm">Small</DBadge>
                        <DBadge size="md">Medium</DBadge>
                        <DBadge size="lg">Large</DBadge>
                    </div>
                </DCard>

                <!-- Toggle -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Toggle</h3>
                    <div class="flex items-center gap-4">
                        <DToggle v-model="toggleValue" label="Enable feature" />
                        <span class="text-sm text-text-secondary">
                            Value: {{ toggleValue }}
                        </span>
                    </div>
                </DCard>

                <!-- Tag Chips -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Tag Chips</h3>
                    <div class="flex flex-wrap gap-2">
                        <DTagChip
                            v-for="tag in sampleTags"
                            :key="tag.id"
                            :color="tag.color"
                            removable
                        >
                            {{ tag.name }}
                        </DTagChip>
                    </div>
                </DCard>

                <!-- Tag Selector -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Tag Selector</h3>
                    <DTagSelector
                        v-model="selectedTags"
                        :available-tags="sampleTags"
                        placeholder="Select tags..."
                    />
                    <p class="text-sm text-text-hint mt-2">
                        Selected: {{ selectedTags.length }} tags
                    </p>
                </DCard>

                <!-- Skeleton Loaders -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Skeleton Loaders</h3>
                    <div class="space-y-3">
                        <DSkeleton variant="text" />
                        <DSkeleton variant="text" class="w-3/4" />
                        <div class="flex gap-3">
                            <DSkeleton variant="avatar" />
                            <div class="flex-1 space-y-2">
                                <DSkeleton variant="text" class="w-1/2" />
                                <DSkeleton variant="text" />
                            </div>
                        </div>
                        <DSkeleton variant="card" class="h-32" />
                    </div>
                </DCard>

                <!-- Tooltips -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Tooltips</h3>
                    <div class="flex flex-wrap gap-4">
                        <DTooltip text="This is a top tooltip" position="top">
                            <DButton variant="outline">Top</DButton>
                        </DTooltip>
                        <DTooltip text="This is a bottom tooltip" position="bottom">
                            <DButton variant="outline">Bottom</DButton>
                        </DTooltip>
                        <DTooltip text="This is a left tooltip" position="left">
                            <DButton variant="outline">Left</DButton>
                        </DTooltip>
                        <DTooltip text="This is a right tooltip" position="right">
                            <DButton variant="outline">Right</DButton>
                        </DTooltip>
                    </div>
                </DCard>

                <!-- Empty States -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Empty States</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <DCard variant="outlined" padding="sm">
                            <DEmptyState
                                icon="universe"
                                title="No Universes"
                                description="Create your first universe"
                                size="sm"
                            />
                        </DCard>
                        <DCard variant="outlined" padding="sm">
                            <DEmptyState
                                icon="timeline"
                                title="No Timelines"
                                description="Add a timeline to get started"
                                size="sm"
                            />
                        </DCard>
                        <DCard variant="outlined" padding="sm">
                            <DEmptyState
                                icon="search"
                                title="No Results"
                                description="Try a different search"
                                size="sm"
                            />
                        </DCard>
                    </div>
                </DCard>

                <!-- Modals & Overlays -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Modals & Overlays</h3>
                    <div class="flex flex-wrap gap-3">
                        <DButton @click="showModal = true">Open Modal</DButton>
                        <DButton variant="secondary" @click="showBottomSheet = true">Open Bottom Sheet</DButton>
                        <DButton variant="error" @click="showConfirmModal = true">Open Confirm Dialog</DButton>
                    </div>
                </DCard>

                <!-- Cards -->
                <div>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">Card Variants</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <DCard>
                            <h4 class="font-bold">Default Card</h4>
                            <p class="text-sm text-text-secondary">With shadow effect</p>
                        </DCard>
                        <DCard variant="outlined">
                            <h4 class="font-bold">Outlined Card</h4>
                            <p class="text-sm text-text-secondary">Border only</p>
                        </DCard>
                        <DCard variant="elevated">
                            <h4 class="font-bold">Elevated Card</h4>
                            <p class="text-sm text-text-secondary">Higher elevation</p>
                        </DCard>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <DModal :show="showModal" title="Example Modal" @close="showModal = false">
            <p class="text-text-secondary mb-4">
                This is an example modal with some content. You can put any content here.
            </p>
            <div class="flex justify-end gap-2">
                <DButton variant="ghost" @click="showModal = false">Cancel</DButton>
                <DButton @click="showModal = false">Confirm</DButton>
            </div>
        </DModal>

        <!-- Bottom Sheet -->
        <DBottomSheet :show="showBottomSheet" title="Bottom Sheet" @close="showBottomSheet = false">
            <p class="text-text-secondary mb-4">
                This is a bottom sheet that slides up from the bottom of the screen.
                It's great for mobile-friendly interactions.
            </p>
            <DButton class="w-full" @click="showBottomSheet = false">Close</DButton>
        </DBottomSheet>

        <!-- Confirm Modal -->
        <DConfirmModal
            :show="showConfirmModal"
            title="Confirm Action"
            message="Are you sure you want to proceed? This action demonstrates the confirmation dialog."
            confirm-text="Yes, proceed"
            cancel-text="Cancel"
            variant="warning"
            @close="showConfirmModal = false"
            @confirm="handleConfirm"
        />

        <!-- Toast -->
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-200 ease-in"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div v-if="showToast" class="fixed bottom-24 sm:bottom-6 right-6 z-50">
                <DToast
                    message="Action confirmed successfully!"
                    variant="success"
                    :show="showToast"
                    @close="showToast = false"
                />
            </div>
        </Transition>
    </AuthenticatedLayout>
</template>
