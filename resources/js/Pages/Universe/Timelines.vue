<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge, DInput, DModal, DToggle } from '@/Components/ui';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
    timelines: {
        type: Array,
        default: () => [],
    },
});

const showTimelineModal = ref(false);
const showEditTimelineModal = ref(false);
const editingTimeline = ref(null);

const timelineForm = useForm({
    name: '',
    description: '',
    color: '#1CB0F6',
    is_canon: false,
});

const editTimelineForm = useForm({
    name: '',
    description: '',
    color: '',
    is_canon: false,
});

const predefinedColors = [
    '#1CB0F6', // Primary blue
    '#58CC02', // Success green
    '#F59E0B', // Warning orange
    '#EF4444', // Error red
    '#8B5CF6', // Purple
    '#EC4899', // Pink
    '#14B8A6', // Teal
    '#6B7280', // Gray
];

const createTimeline = () => {
    timelineForm.post(route('universes.timelines.store', props.universe.id), {
        onSuccess: () => {
            showTimelineModal.value = false;
            timelineForm.reset();
        },
    });
};

const openEditTimeline = (timeline) => {
    editingTimeline.value = timeline;
    editTimelineForm.name = timeline.name;
    editTimelineForm.description = timeline.description || '';
    editTimelineForm.color = timeline.color || '#1CB0F6';
    editTimelineForm.is_canon = timeline.is_canon;
    showEditTimelineModal.value = true;
};

const updateTimeline = () => {
    editTimelineForm.put(route('timelines.update', editingTimeline.value.id), {
        onSuccess: () => {
            showEditTimelineModal.value = false;
            editingTimeline.value = null;
        },
    });
};

const deleteTimeline = (timeline) => {
    if (confirm(`Are you sure you want to delete "${timeline.name}"?`)) {
        router.delete(route('timelines.destroy', timeline.id));
    }
};
</script>

<template>
    <Head :title="`${universe.name} - Timelines`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('universes.show', universe.id)" class="text-text-hint hover:text-text-primary transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h2 class="text-xl font-nunito font-bold text-text-primary">
                            {{ universe.name }} - Timelines
                        </h2>
                    </div>
                </div>
                <DButton @click="showTimelineModal = true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Timeline
                </DButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="timelines.length === 0" class="text-center py-12">
                    <DCard padding="lg">
                        <div class="w-20 h-20 mx-auto rounded-full bg-primary-light flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-nunito font-bold text-text-primary mb-2">
                            No Timelines Yet
                        </h3>
                        <p class="text-text-secondary mb-4">
                            Create your first timeline to start organizing your story branches
                        </p>
                        <DButton @click="showTimelineModal = true">
                            Create First Timeline
                        </DButton>
                    </DCard>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <DCard
                        v-for="timeline in timelines"
                        :key="timeline.id"
                        padding="md"
                        class="hover:shadow-lg transition-shadow cursor-pointer"
                    >
                        <Link :href="route('timelines.show', timeline.id)" class="block">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-4 h-4 rounded-full"
                                        :style="{ backgroundColor: timeline.color || '#1CB0F6' }"
                                    />
                                    <h3 class="font-nunito font-bold text-text-primary">
                                        {{ timeline.name }}
                                    </h3>
                                </div>
                                <DBadge v-if="timeline.is_canon" variant="success" size="sm">Canon</DBadge>
                            </div>
                            <p v-if="timeline.description" class="text-sm text-text-secondary mb-3 line-clamp-2">
                                {{ timeline.description }}
                            </p>
                            <div class="flex items-center justify-between text-sm text-text-hint">
                                <span>{{ timeline.scenes_count || 0 }} scenes</span>
                            </div>
                        </Link>
                        <div class="mt-3 pt-3 border-t border-border-gray flex justify-end">
                            <button
                                class="text-text-hint hover:text-text-primary transition-colors p-1"
                                @click.prevent="openEditTimeline(timeline)"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                        </div>
                    </DCard>
                </div>
            </div>
        </div>

        <!-- Create Timeline Modal -->
        <DModal
            :show="showTimelineModal"
            title="Create New Timeline"
            @close="showTimelineModal = false"
        >
            <form @submit.prevent="createTimeline" class="space-y-4">
                <DInput
                    v-model="timelineForm.name"
                    label="Timeline Name"
                    placeholder="e.g., Canon, What If..."
                    :error="timelineForm.errors.name"
                    required
                />

                <DInput
                    v-model="timelineForm.description"
                    label="Description"
                    placeholder="What happens in this timeline?"
                    :error="timelineForm.errors.description"
                />

                <div>
                    <label class="block mb-2 font-nunito font-semibold text-sm text-text-primary">
                        Color
                    </label>
                    <div class="flex gap-2 flex-wrap">
                        <button
                            v-for="color in predefinedColors"
                            :key="color"
                            type="button"
                            :class="[
                                'w-8 h-8 rounded-full transition-all',
                                timelineForm.color === color ? 'ring-2 ring-offset-2 ring-primary' : '',
                            ]"
                            :style="{ backgroundColor: color }"
                            @click="timelineForm.color = color"
                        />
                    </div>
                </div>

                <DToggle
                    v-model="timelineForm.is_canon"
                    label="This is the canon timeline"
                />
            </form>

            <template #footer>
                <DButton variant="ghost" @click="showTimelineModal = false">
                    Cancel
                </DButton>
                <DButton
                    :loading="timelineForm.processing"
                    :disabled="!timelineForm.name"
                    @click="createTimeline"
                >
                    Create Timeline
                </DButton>
            </template>
        </DModal>

        <!-- Edit Timeline Modal -->
        <DModal
            :show="showEditTimelineModal"
            title="Edit Timeline"
            @close="showEditTimelineModal = false"
        >
            <form @submit.prevent="updateTimeline" class="space-y-4">
                <DInput
                    v-model="editTimelineForm.name"
                    label="Timeline Name"
                    placeholder="e.g., Canon, What If..."
                    :error="editTimelineForm.errors.name"
                    required
                />

                <DInput
                    v-model="editTimelineForm.description"
                    label="Description"
                    placeholder="What happens in this timeline?"
                    :error="editTimelineForm.errors.description"
                />

                <div>
                    <label class="block mb-2 font-nunito font-semibold text-sm text-text-primary">
                        Color
                    </label>
                    <div class="flex gap-2 flex-wrap">
                        <button
                            v-for="color in predefinedColors"
                            :key="color"
                            type="button"
                            :class="[
                                'w-8 h-8 rounded-full transition-all',
                                editTimelineForm.color === color ? 'ring-2 ring-offset-2 ring-primary' : '',
                            ]"
                            :style="{ backgroundColor: color }"
                            @click="editTimelineForm.color = color"
                        />
                    </div>
                </div>

                <DToggle
                    v-model="editTimelineForm.is_canon"
                    label="This is the canon timeline"
                />

                <div class="pt-4 border-t border-border-gray">
                    <button
                        type="button"
                        class="text-error text-sm font-semibold hover:text-error-dark transition-colors"
                        @click="deleteTimeline(editingTimeline)"
                    >
                        Delete this timeline
                    </button>
                </div>
            </form>

            <template #footer>
                <DButton variant="ghost" @click="showEditTimelineModal = false">
                    Cancel
                </DButton>
                <DButton
                    :loading="editTimelineForm.processing"
                    :disabled="!editTimelineForm.name"
                    @click="updateTimeline"
                >
                    Save Changes
                </DButton>
            </template>
        </DModal>
    </AuthenticatedLayout>
</template>
