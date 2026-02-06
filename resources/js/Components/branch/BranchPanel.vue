<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { DButton, DCard, DInput, DToggle, DModal, DBadge } from '@/Components/ui';

const props = defineProps({
    scene: {
        type: Object,
        required: true,
    },
    editable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['branchPointToggled']);

const showBranchModal = ref(false);
const branches = ref([]);
const loadingBranches = ref(false);

const branchPointForm = useForm({
    is_branch_point: props.scene.is_branch_point || false,
    branch_question: props.scene.branch_question || '',
});

const newBranchForm = useForm({
    name: '',
    description: '',
    color: '#F59E0B',
    copy_subsequent_scenes: false,
});

const predefinedColors = [
    '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899',
    '#14B8A6', '#6B7280', '#1CB0F6', '#58CC02',
];

const toggleBranchPoint = () => {
    branchPointForm.is_branch_point = !branchPointForm.is_branch_point;

    router.patch(route('scenes.toggle-branch-point', props.scene.id), {
        is_branch_point: branchPointForm.is_branch_point,
        branch_question: branchPointForm.branch_question,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            emit('branchPointToggled', branchPointForm.is_branch_point);
        },
    });
};

const updateBranchQuestion = () => {
    if (!branchPointForm.is_branch_point) return;

    router.patch(route('scenes.toggle-branch-point', props.scene.id), {
        is_branch_point: true,
        branch_question: branchPointForm.branch_question,
    }, {
        preserveScroll: true,
    });
};

const loadBranches = async () => {
    loadingBranches.value = true;
    try {
        const response = await fetch(route('scenes.branches', props.scene.id), {
            headers: { 'Accept': 'application/json' },
        });
        branches.value = await response.json();
    } finally {
        loadingBranches.value = false;
    }
};

const openBranchModal = () => {
    newBranchForm.reset();
    newBranchForm.color = '#F59E0B';
    showBranchModal.value = true;
    loadBranches();
};

const createBranch = () => {
    newBranchForm.post(route('scenes.create-branch', props.scene.id), {
        onSuccess: () => {
            showBranchModal.value = false;
        },
    });
};

const navigateToBranch = (timeline) => {
    router.visit(route('timelines.show', timeline.id));
};
</script>

<template>
    <div class="branch-panel">
        <!-- Branch Point Toggle -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span class="font-nunito font-bold text-text-primary">Branch Point</span>
            </div>
            <DToggle
                v-if="editable"
                v-model="branchPointForm.is_branch_point"
                @update:model-value="toggleBranchPoint"
            />
            <DBadge v-else-if="scene.is_branch_point" variant="warning" size="sm">
                Active
            </DBadge>
        </div>

        <!-- Branch Question -->
        <div v-if="branchPointForm.is_branch_point || scene.is_branch_point" class="mb-4">
            <div v-if="editable">
                <DInput
                    v-model="branchPointForm.branch_question"
                    label="Branch Question"
                    placeholder="What if...?"
                    @blur="updateBranchQuestion"
                />
                <p class="mt-1 text-xs text-text-hint">
                    Optional: Describe the "what if" for this branch point
                </p>
            </div>
            <div v-else-if="scene.branch_question">
                <label class="block text-sm font-nunito font-semibold text-text-hint mb-1">
                    Branch Question
                </label>
                <p class="text-text-secondary italic">
                    "{{ scene.branch_question }}"
                </p>
            </div>
        </div>

        <!-- Create Branch Button -->
        <div v-if="branchPointForm.is_branch_point || scene.is_branch_point">
            <DButton
                variant="secondary"
                size="sm"
                class="w-full"
                @click="openBranchModal"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Alternate Timeline
            </DButton>
        </div>

        <!-- Existing Branches -->
        <div v-if="scene.is_branch_point" class="mt-4 pt-4 border-t border-border-light">
            <label class="block text-sm font-nunito font-semibold text-text-hint mb-2">
                Branches from this scene
            </label>
            <div v-if="loadingBranches" class="text-center py-4 text-text-hint">
                Loading...
            </div>
            <div v-else-if="branches.length === 0" class="text-center py-4 text-text-hint text-sm">
                No branches yet
            </div>
            <div v-else class="space-y-2">
                <button
                    v-for="branch in branches"
                    :key="branch.id"
                    class="w-full flex items-center gap-2 p-2 rounded-lg hover:bg-bg-light-gray transition-colors text-left"
                    @click="navigateToBranch(branch)"
                >
                    <span
                        class="w-3 h-3 rounded-full shrink-0"
                        :style="{ backgroundColor: branch.color || '#F59E0B' }"
                    />
                    <span class="font-nunito text-sm text-text-primary truncate flex-1">
                        {{ branch.name }}
                    </span>
                    <span class="text-xs text-text-hint">
                        {{ branch.scenes_count || 0 }} scenes
                    </span>
                </button>
            </div>
        </div>

        <!-- Create Branch Modal -->
        <DModal
            :show="showBranchModal"
            title="Create Alternate Timeline"
            @close="showBranchModal = false"
        >
            <form @submit.prevent="createBranch" class="space-y-4">
                <DInput
                    v-model="newBranchForm.name"
                    label="Timeline Name"
                    placeholder="e.g., What if they never met?"
                    :error="newBranchForm.errors.name"
                    required
                />

                <DInput
                    v-model="newBranchForm.description"
                    label="Description"
                    placeholder="Describe this alternate reality..."
                    :error="newBranchForm.errors.description"
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
                                newBranchForm.color === color ? 'ring-2 ring-offset-2 ring-primary' : '',
                            ]"
                            :style="{ backgroundColor: color }"
                            @click="newBranchForm.color = color"
                        />
                    </div>
                </div>

                <div class="pt-4 border-t border-border-light">
                    <DToggle
                        v-model="newBranchForm.copy_subsequent_scenes"
                        label="Copy subsequent scenes to new timeline"
                    />
                    <p class="mt-1 text-xs text-text-hint">
                        This will copy all scenes that come after this branch point
                    </p>
                </div>
            </form>

            <template #footer>
                <DButton variant="ghost" @click="showBranchModal = false">
                    Cancel
                </DButton>
                <DButton
                    :loading="newBranchForm.processing"
                    :disabled="!newBranchForm.name"
                    @click="createBranch"
                >
                    Create Timeline
                </DButton>
            </template>
        </DModal>
    </div>
</template>
