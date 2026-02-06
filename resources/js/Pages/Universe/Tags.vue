<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge, DInput, DModal, DConfirmModal } from '@/Components/ui';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
    tags: {
        type: Array,
        default: () => [],
    },
});

const showTagModal = ref(false);
const editingTag = ref(null);
const showDeleteConfirm = ref(false);
const tagToDelete = ref(null);
const isDeleting = ref(false);

const categories = [
    { value: '', label: 'No Category' },
    { value: 'emotion', label: 'Emotion' },
    { value: 'event', label: 'Event' },
    { value: 'theme', label: 'Theme' },
    { value: 'character', label: 'Character' },
    { value: 'location', label: 'Location' },
    { value: 'other', label: 'Other' },
];

const predefinedColors = [
    '#1CB0F6', '#58CC02', '#F59E0B', '#EF4444',
    '#8B5CF6', '#EC4899', '#14B8A6', '#6B7280',
];

const form = useForm({
    name: '',
    color: '#1CB0F6',
    category: '',
});

const groupedTags = computed(() => {
    const groups = {};
    props.tags.forEach(tag => {
        const category = tag.category || 'uncategorized';
        if (!groups[category]) {
            groups[category] = [];
        }
        groups[category].push(tag);
    });
    return groups;
});

const getCategoryLabel = (category) => {
    const found = categories.find(c => c.value === category);
    return found ? found.label : 'Uncategorized';
};

const openCreateModal = () => {
    editingTag.value = null;
    form.reset();
    form.color = '#1CB0F6';
    showTagModal.value = true;
};

const openEditModal = (tag) => {
    editingTag.value = tag;
    form.name = tag.name;
    form.color = tag.color || '#1CB0F6';
    form.category = tag.category || '';
    showTagModal.value = true;
};

const submitForm = () => {
    if (editingTag.value) {
        form.put(route('tags.update', editingTag.value.id), {
            onSuccess: () => {
                showTagModal.value = false;
                editingTag.value = null;
                form.reset();
            },
        });
    } else {
        form.post(route('universes.tags.store', props.universe.id), {
            onSuccess: () => {
                showTagModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDeleteTag = (tag) => {
    tagToDelete.value = tag;
    showDeleteConfirm.value = true;
};

const deleteTag = () => {
    if (!tagToDelete.value) return;

    isDeleting.value = true;
    router.delete(route('tags.destroy', tagToDelete.value.id), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            tagToDelete.value = null;
            isDeleting.value = false;
        },
        onError: () => {
            isDeleting.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Tags - ${universe.name}`" />

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
                            Tags
                        </h2>
                        <p class="text-sm text-text-secondary">
                            {{ universe.name }}
                        </p>
                    </div>
                </div>
                <DButton @click="openCreateModal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Tag
                </DButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="tags.length === 0" class="text-center py-12">
                    <DCard padding="lg">
                        <div class="w-20 h-20 mx-auto rounded-full bg-primary-light flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-nunito font-bold text-text-primary mb-2">
                            No Tags Yet
                        </h3>
                        <p class="text-text-secondary mb-4">
                            Create tags to organize and categorize your scenes
                        </p>
                        <DButton @click="openCreateModal">
                            Create First Tag
                        </DButton>
                    </DCard>
                </div>

                <div v-else class="space-y-6">
                    <div v-for="(categoryTags, category) in groupedTags" :key="category">
                        <h3 class="font-nunito font-bold text-text-primary mb-3 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-primary"></span>
                            {{ getCategoryLabel(category) }}
                            <span class="text-sm font-normal text-text-hint">({{ categoryTags.length }})</span>
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <div
                                v-for="tag in categoryTags"
                                :key="tag.id"
                                class="group inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white border-2 border-border-light hover:border-primary transition-colors cursor-pointer"
                                @click="openEditModal(tag)"
                            >
                                <span
                                    class="w-3 h-3 rounded-full shrink-0"
                                    :style="{ backgroundColor: tag.color || '#6B7280' }"
                                ></span>
                                <span class="font-nunito font-semibold text-text-primary">
                                    {{ tag.name }}
                                </span>
                                <span class="text-xs text-text-hint">
                                    {{ tag.scenes_count || 0 }}
                                </span>
                                <button
                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-0.5 text-text-hint hover:text-error"
                                    @click.stop="confirmDeleteTag(tag)"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Tag Modal -->
        <DModal
            :show="showTagModal"
            :title="editingTag ? 'Edit Tag' : 'Create New Tag'"
            @close="showTagModal = false"
        >
            <form @submit.prevent="submitForm" class="space-y-4">
                <DInput
                    v-model="form.name"
                    label="Name"
                    placeholder="e.g., romantic, action, flashback"
                    :error="form.errors.name"
                    required
                />

                <div>
                    <label class="block mb-2 font-nunito font-semibold text-sm text-text-primary">
                        Category
                    </label>
                    <select
                        v-model="form.category"
                        class="w-full px-4 py-3 rounded-xl border-2 border-border-light bg-white font-nunito text-text-primary focus:outline-none focus:border-primary transition-colors"
                    >
                        <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                            {{ cat.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.category" class="mt-1 text-sm text-error">
                        {{ form.errors.category }}
                    </p>
                </div>

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
                                form.color === color ? 'ring-2 ring-offset-2 ring-primary' : '',
                            ]"
                            :style="{ backgroundColor: color }"
                            @click="form.color = color"
                        />
                    </div>
                    <p v-if="form.errors.color" class="mt-1 text-sm text-error">
                        {{ form.errors.color }}
                    </p>
                </div>
            </form>

            <template #footer>
                <DButton variant="ghost" @click="showTagModal = false">
                    Cancel
                </DButton>
                <DButton
                    :loading="form.processing"
                    :disabled="!form.name"
                    @click="submitForm"
                >
                    {{ editingTag ? 'Update Tag' : 'Create Tag' }}
                </DButton>
            </template>
        </DModal>

        <!-- Delete Tag Confirmation -->
        <DConfirmModal
            :show="showDeleteConfirm"
            title="Delete Tag"
            :message="`Are you sure you want to delete the tag '${tagToDelete?.name}'? This action cannot be undone.`"
            confirm-text="Delete"
            cancel-text="Cancel"
            variant="error"
            :loading="isDeleting"
            @close="showDeleteConfirm = false"
            @confirm="deleteTag"
        />
    </AuthenticatedLayout>
</template>
