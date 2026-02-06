<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DInput, DToggle, DModal } from '@/Components/ui';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.universe.name,
    description: props.universe.description || '',
    is_public: props.universe.is_public,
    allow_fork: props.universe.allow_fork,
});

const showDeleteModal = ref(false);

const saveSettings = () => {
    form.put(route('universes.update', props.universe.id), {
        preserveScroll: true,
    });
};

const deleteUniverse = () => {
    router.delete(route('universes.destroy', props.universe.id));
};
</script>

<template>
    <Head :title="`Settings - ${universe.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('universes.show', universe.id)" class="text-text-hint hover:text-text-primary transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="text-xl font-nunito font-bold text-text-primary">
                    Settings - {{ universe.name }}
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- General settings -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">
                        General
                    </h3>
                    <form @submit.prevent="saveSettings" class="space-y-4">
                        <DInput
                            v-model="form.name"
                            label="Universe Name"
                            placeholder="Enter universe name"
                            :error="form.errors.name"
                            required
                        />

                        <div>
                            <label class="block mb-2 font-nunito font-semibold text-sm text-text-primary">
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                placeholder="Describe your universe..."
                                rows="4"
                                class="w-full px-4 py-3 rounded-xl border-2 border-border-gray font-nunito transition-all duration-200 outline-none placeholder:text-text-hint hover:border-border-dark focus:border-primary focus:ring-2 focus:ring-primary/20"
                            />
                            <p v-if="form.errors.description" class="mt-2 text-sm text-error font-nunito">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="flex justify-end pt-4">
                            <DButton type="submit" :loading="form.processing">
                                Save Changes
                            </DButton>
                        </div>
                    </form>
                </DCard>

                <!-- Visibility settings -->
                <DCard>
                    <h3 class="text-lg font-nunito font-bold text-text-primary mb-4">
                        Visibility
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-nunito font-semibold text-text-primary">Public Universe</p>
                                <p class="text-sm text-text-secondary">Allow others to view your universe</p>
                            </div>
                            <DToggle v-model="form.is_public" @update:modelValue="saveSettings" />
                        </div>

                        <div class="flex items-center justify-between" :class="{ 'opacity-50': !form.is_public }">
                            <div>
                                <p class="font-nunito font-semibold text-text-primary">Allow Forking</p>
                                <p class="text-sm text-text-secondary">Let others create copies of your universe</p>
                            </div>
                            <DToggle
                                v-model="form.allow_fork"
                                :disabled="!form.is_public"
                                @update:modelValue="saveSettings"
                            />
                        </div>
                    </div>
                </DCard>

                <!-- Danger zone -->
                <DCard class="border-error/30">
                    <h3 class="text-lg font-nunito font-bold text-error mb-4">
                        Danger Zone
                    </h3>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-nunito font-semibold text-text-primary">Delete Universe</p>
                            <p class="text-sm text-text-secondary">Permanently delete this universe and all its content</p>
                        </div>
                        <DButton variant="error" size="sm" @click="showDeleteModal = true">
                            Delete Universe
                        </DButton>
                    </div>
                </DCard>
            </div>
        </div>

        <!-- Delete confirmation modal -->
        <DModal
            :show="showDeleteModal"
            title="Delete Universe"
            @close="showDeleteModal = false"
        >
            <div class="text-center py-4">
                <div class="w-16 h-16 mx-auto rounded-full bg-error/10 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h4 class="text-lg font-nunito font-bold text-text-primary mb-2">
                    Are you sure?
                </h4>
                <p class="text-text-secondary">
                    This will permanently delete <strong>{{ universe.name }}</strong> and all its timelines, scenes, characters, and tags. This action cannot be undone.
                </p>
            </div>

            <template #footer>
                <DButton variant="ghost" @click="showDeleteModal = false">
                    Cancel
                </DButton>
                <DButton variant="error" @click="deleteUniverse">
                    Delete Forever
                </DButton>
            </template>
        </DModal>
    </AuthenticatedLayout>
</template>
