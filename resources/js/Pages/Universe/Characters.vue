<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge, DInput, DModal, DConfirmModal, DEmptyState } from '@/Components/ui';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
    characters: {
        type: Array,
        default: () => [],
    },
});

const showCharacterModal = ref(false);
const showDeleteConfirm = ref(false);
const characterToDelete = ref(null);
const isDeleting = ref(false);

const form = useForm({
    name: '',
    nickname: '',
    type: '',
    description: '',
    color: '#1CB0F6',
});

const predefinedColors = [
    '#1CB0F6', '#58CC02', '#F59E0B', '#EF4444',
    '#8B5CF6', '#EC4899', '#14B8A6', '#6B7280',
];

const createCharacter = () => {
    form.post(route('universes.characters.store', props.universe.id), {
        onSuccess: () => {
            showCharacterModal.value = false;
            form.reset();
        },
    });
};

const confirmDeleteCharacter = (character) => {
    characterToDelete.value = character;
    showDeleteConfirm.value = true;
};

const deleteCharacter = () => {
    if (!characterToDelete.value) return;

    isDeleting.value = true;
    router.delete(route('characters.destroy', characterToDelete.value.id), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            characterToDelete.value = null;
            isDeleting.value = false;
        },
        onError: () => {
            isDeleting.value = false;
        },
    });
};
</script>

<template>
    <Head :title="`Characters - ${universe.name}`" />

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
                            Characters
                        </h2>
                        <p class="text-sm text-text-secondary">
                            {{ universe.name }}
                        </p>
                    </div>
                </div>
                <DButton @click="showCharacterModal = true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Character
                </DButton>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <DCard v-if="characters.length === 0" padding="lg">
                    <DEmptyState
                        icon="character"
                        title="No Characters Yet"
                        description="Create characters to populate your story universe"
                        action-text="Create First Character"
                        size="lg"
                        @action="showCharacterModal = true"
                    />
                </DCard>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <DCard
                        v-for="character in characters"
                        :key="character.id"
                        padding="md"
                        class="group hover:shadow-lg transition-shadow"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-xl shrink-0"
                                :style="{ backgroundColor: character.color || '#6B7280' }"
                            >
                                {{ character.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <Link :href="route('characters.show', character.id)" class="block group">
                                    <h3 class="font-nunito font-bold text-text-primary group-hover:text-primary transition-colors truncate">
                                        {{ character.name }}
                                    </h3>
                                    <p v-if="character.nickname" class="text-sm text-text-hint">
                                        "{{ character.nickname }}"
                                    </p>
                                </Link>
                                <div class="flex items-center gap-2 mt-2">
                                    <DBadge v-if="character.type" variant="gray" size="sm">
                                        {{ character.type }}
                                    </DBadge>
                                    <span class="text-xs text-text-hint">
                                        {{ character.scenes_count || 0 }} scenes
                                    </span>
                                </div>
                                <div v-if="character.traits?.length" class="flex flex-wrap gap-1 mt-2">
                                    <span
                                        v-for="(trait, index) in character.traits.slice(0, 3)"
                                        :key="index"
                                        class="text-xs px-2 py-0.5 bg-bg-light-gray rounded-full text-text-secondary"
                                    >
                                        {{ trait }}
                                    </span>
                                    <span v-if="character.traits.length > 3" class="text-xs text-text-hint">
                                        +{{ character.traits.length - 3 }} more
                                    </span>
                                </div>
                            </div>
                            <button
                                class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-text-hint hover:text-error"
                                @click="confirmDeleteCharacter(character)"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </DCard>
                </div>
            </div>
        </div>

        <!-- Create Character Modal -->
        <DModal
            :show="showCharacterModal"
            title="Create New Character"
            @close="showCharacterModal = false"
        >
            <form @submit.prevent="createCharacter" class="space-y-4">
                <DInput
                    v-model="form.name"
                    label="Name"
                    placeholder="e.g., Sarah Chen"
                    :error="form.errors.name"
                    required
                />

                <DInput
                    v-model="form.nickname"
                    label="Nickname"
                    placeholder="e.g., Sari"
                    :error="form.errors.nickname"
                />

                <DInput
                    v-model="form.type"
                    label="Type"
                    placeholder="e.g., INFJ, Protagonist"
                    :error="form.errors.type"
                />

                <DInput
                    v-model="form.description"
                    label="Description"
                    placeholder="Brief description..."
                    :error="form.errors.description"
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
                                form.color === color ? 'ring-2 ring-offset-2 ring-primary' : '',
                            ]"
                            :style="{ backgroundColor: color }"
                            @click="form.color = color"
                        />
                    </div>
                </div>
            </form>

            <template #footer>
                <DButton variant="ghost" @click="showCharacterModal = false">
                    Cancel
                </DButton>
                <DButton
                    :loading="form.processing"
                    :disabled="!form.name"
                    @click="createCharacter"
                >
                    Create Character
                </DButton>
            </template>
        </DModal>

        <!-- Delete Character Confirmation -->
        <DConfirmModal
            :show="showDeleteConfirm"
            title="Delete Character"
            :message="`Are you sure you want to delete '${characterToDelete?.name}'? This action cannot be undone.`"
            confirm-text="Delete"
            cancel-text="Cancel"
            variant="error"
            :loading="isDeleting"
            @close="showDeleteConfirm = false"
            @confirm="deleteCharacter"
        />
    </AuthenticatedLayout>
</template>
