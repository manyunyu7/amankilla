<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { DButton, DCard, DInput, DModal, DTooltip, DEmptyState } from '@/Components/ui';
import { useKeyboardShortcuts } from '@/composables/useKeyboardShortcuts';

const props = defineProps({
    universes: {
        type: Array,
        default: () => [],
    },
});

const showCreateModal = ref(false);
const isLoaded = ref(false);

const form = useForm({
    name: '',
    description: '',
});

// Keyboard shortcuts
useKeyboardShortcuts({
    'ctrl+n': () => { showCreateModal.value = true; },
    'escape': () => { showCreateModal.value = false; },
});

const createUniverse = () => {
    form.post(route('universes.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const openUniverse = (universe) => {
    router.visit(route('universes.show', universe.id));
};

// Trigger animations after mount
onMounted(() => {
    setTimeout(() => {
        isLoaded.value = true;
    }, 50);
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-nunito font-bold text-text-primary">
                    Your Universes
                </h2>
                <DTooltip text="Ctrl+N" position="bottom">
                    <DButton @click="showCreateModal = true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Universe
                    </DButton>
                </DTooltip>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Empty state -->
                <DCard v-if="universes.length === 0" variant="outlined" padding="lg">
                    <DEmptyState
                        icon="universe"
                        title="No Universes Yet"
                        description="Create your first universe to start building your branching narrative!"
                        action-text="Create Your First Universe"
                        size="lg"
                        @action="showCreateModal = true"
                    />
                </DCard>

                <!-- Universe grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <DCard
                        v-for="(universe, index) in universes"
                        :key="universe.id"
                        variant="interactive"
                        :class="[
                            'opacity-0',
                            isLoaded ? 'animate-fade-in-up' : '',
                        ]"
                        :style="{ animationDelay: `${index * 50}ms`, animationFillMode: 'forwards' }"
                        @click="openUniverse(universe)"
                    >
                        <!-- Cover image or placeholder -->
                        <div class="h-32 -m-4 mb-4 rounded-t-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center">
                            <img
                                v-if="universe.cover_image"
                                :src="universe.cover_image"
                                :alt="universe.name"
                                class="w-full h-full object-cover rounded-t-2xl"
                            />
                            <svg v-else class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>

                        <!-- Content -->
                        <h3 class="text-lg font-nunito font-bold text-text-primary truncate">
                            {{ universe.name }}
                        </h3>
                        <p v-if="universe.description" class="text-text-secondary text-sm mt-1 line-clamp-2">
                            {{ universe.description }}
                        </p>

                        <!-- Stats -->
                        <div class="flex items-center gap-4 mt-4 text-sm text-text-hint">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                {{ universe.timelines_count || 0 }} timelines
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ universe.characters_count || 0 }} characters
                            </span>
                        </div>
                    </DCard>

                    <!-- Add new universe card -->
                    <DCard
                        variant="outlined"
                        class="cursor-pointer border-dashed"
                        @click="showCreateModal = true"
                    >
                        <div class="h-full flex flex-col items-center justify-center gap-3 py-8 text-text-hint hover:text-primary transition-colors">
                            <div class="w-12 h-12 rounded-full border-2 border-current flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <span class="font-nunito font-semibold">New Universe</span>
                        </div>
                    </DCard>
                </div>
            </div>
        </div>

        <!-- Create Universe Modal -->
        <DModal
            :show="showCreateModal"
            title="Create New Universe"
            @close="showCreateModal = false"
        >
            <form @submit.prevent="createUniverse" class="space-y-4">
                <DInput
                    v-model="form.name"
                    label="Universe Name"
                    placeholder="Enter a name for your universe"
                    :error="form.errors.name"
                    required
                />

                <DInput
                    v-model="form.description"
                    label="Description"
                    placeholder="What is this universe about?"
                    :error="form.errors.description"
                />
            </form>

            <template #footer>
                <DButton variant="ghost" @click="showCreateModal = false">
                    Cancel
                </DButton>
                <DButton
                    :loading="form.processing"
                    :disabled="!form.name"
                    @click="createUniverse"
                >
                    Create Universe
                </DButton>
            </template>
        </DModal>
    </AuthenticatedLayout>
</template>
