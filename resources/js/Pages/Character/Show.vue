<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DBadge, DInput, DModal } from '@/Components/ui';

const props = defineProps({
    character: {
        type: Object,
        required: true,
    },
    universe: {
        type: Object,
        required: true,
    },
});

const isEditing = ref(false);
const showDeleteModal = ref(false);

const form = useForm({
    name: props.character.name,
    nickname: props.character.nickname || '',
    type: props.character.type || '',
    description: props.character.description || '',
    traits: props.character.traits || [],
    color: props.character.color || '#6B7280',
});

const newTrait = ref('');

const addTrait = () => {
    if (newTrait.value.trim() && !form.traits.includes(newTrait.value.trim())) {
        form.traits.push(newTrait.value.trim());
        newTrait.value = '';
    }
};

const removeTrait = (index) => {
    form.traits.splice(index, 1);
};

const saveCharacter = () => {
    form.put(route('characters.update', props.character.id), {
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const deleteCharacter = () => {
    router.delete(route('characters.destroy', props.character.id));
};

const predefinedColors = [
    '#1CB0F6', '#58CC02', '#F59E0B', '#EF4444',
    '#8B5CF6', '#EC4899', '#14B8A6', '#6B7280',
];
</script>

<template>
    <Head :title="`${character.name} - ${universe.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('universes.characters.index', universe.id)" class="text-text-hint hover:text-text-primary transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg"
                            :style="{ backgroundColor: character.color || '#6B7280' }"
                        >
                            {{ character.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h2 class="text-xl font-nunito font-bold text-text-primary flex items-center gap-2">
                                {{ character.name }}
                                <span v-if="character.nickname" class="text-text-secondary font-normal text-base">
                                    ({{ character.nickname }})
                                </span>
                            </h2>
                            <p v-if="character.type" class="text-sm text-text-hint">
                                {{ character.type }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <DButton v-if="!isEditing" variant="ghost" @click="isEditing = true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </DButton>
                    <template v-else>
                        <DButton variant="ghost" @click="isEditing = false">Cancel</DButton>
                        <DButton :loading="form.processing" @click="saveCharacter">Save</DButton>
                    </template>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Main content -->
                    <div class="md:col-span-2 space-y-6">
                        <DCard padding="md">
                            <h3 class="font-nunito font-bold text-text-primary mb-4">Basic Info</h3>

                            <div v-if="isEditing" class="space-y-4">
                                <DInput
                                    v-model="form.name"
                                    label="Name"
                                    :error="form.errors.name"
                                    required
                                />
                                <DInput
                                    v-model="form.nickname"
                                    label="Nickname"
                                    :error="form.errors.nickname"
                                />
                                <DInput
                                    v-model="form.type"
                                    label="Type (e.g., INFJ, INFP)"
                                    placeholder="MBTI type or role"
                                    :error="form.errors.type"
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
                            </div>

                            <div v-else class="space-y-3">
                                <div>
                                    <span class="text-sm text-text-hint">Name:</span>
                                    <p class="font-medium">{{ character.name }}</p>
                                </div>
                                <div v-if="character.nickname">
                                    <span class="text-sm text-text-hint">Nickname:</span>
                                    <p>{{ character.nickname }}</p>
                                </div>
                                <div v-if="character.type">
                                    <span class="text-sm text-text-hint">Type:</span>
                                    <p>{{ character.type }}</p>
                                </div>
                            </div>
                        </DCard>

                        <DCard padding="md">
                            <h3 class="font-nunito font-bold text-text-primary mb-4">Description</h3>

                            <textarea
                                v-if="isEditing"
                                v-model="form.description"
                                class="w-full px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0 resize-none"
                                rows="6"
                                placeholder="Describe this character..."
                            ></textarea>
                            <p v-else class="text-text-primary whitespace-pre-wrap">
                                {{ character.description || 'No description yet.' }}
                            </p>
                        </DCard>

                        <DCard padding="md">
                            <h3 class="font-nunito font-bold text-text-primary mb-4">Traits</h3>

                            <div v-if="isEditing" class="space-y-3">
                                <div class="flex gap-2">
                                    <input
                                        v-model="newTrait"
                                        type="text"
                                        class="flex-1 px-3 py-2 border-2 border-border-gray rounded-lg text-sm focus:border-primary focus:ring-0"
                                        placeholder="Add a trait..."
                                        @keyup.enter="addTrait"
                                    />
                                    <DButton size="sm" @click="addTrait">Add</DButton>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(trait, index) in form.traits"
                                        :key="index"
                                        class="inline-flex items-center gap-1 px-3 py-1 bg-primary-light text-primary rounded-full text-sm"
                                    >
                                        {{ trait }}
                                        <button
                                            type="button"
                                            class="hover:text-error"
                                            @click="removeTrait(index)"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                            </div>
                            <div v-else class="flex flex-wrap gap-2">
                                <DBadge
                                    v-for="(trait, index) in character.traits"
                                    :key="index"
                                    variant="primary"
                                >
                                    {{ trait }}
                                </DBadge>
                                <span v-if="!character.traits?.length" class="text-text-hint">
                                    No traits defined.
                                </span>
                            </div>
                        </DCard>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <DCard padding="md">
                            <h3 class="font-nunito font-bold text-text-primary mb-4">Appears In</h3>
                            <p class="text-2xl font-bold text-primary">
                                {{ character.scenes_count || 0 }}
                            </p>
                            <p class="text-sm text-text-hint">scenes</p>

                            <div v-if="character.scenes?.length" class="mt-4 space-y-2">
                                <Link
                                    v-for="scene in character.scenes.slice(0, 5)"
                                    :key="scene.id"
                                    :href="route('scenes.show', scene.id)"
                                    class="block p-2 rounded-lg hover:bg-bg-light-gray transition-colors"
                                >
                                    <p class="font-medium text-sm text-text-primary">{{ scene.title }}</p>
                                </Link>
                            </div>
                        </DCard>

                        <DCard v-if="isEditing" padding="md">
                            <h3 class="font-nunito font-bold text-error mb-4">Danger Zone</h3>
                            <button
                                type="button"
                                class="text-error text-sm font-semibold hover:text-error-dark transition-colors"
                                @click="showDeleteModal = true"
                            >
                                Delete this character
                            </button>
                        </DCard>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <DModal
            :show="showDeleteModal"
            title="Delete Character"
            @close="showDeleteModal = false"
        >
            <p class="text-text-secondary">
                Are you sure you want to delete <strong>{{ character.name }}</strong>?
                This action cannot be undone.
            </p>

            <template #footer>
                <DButton variant="ghost" @click="showDeleteModal = false">
                    Cancel
                </DButton>
                <DButton variant="danger" @click="deleteCharacter">
                    Delete
                </DButton>
            </template>
        </DModal>
    </AuthenticatedLayout>
</template>
