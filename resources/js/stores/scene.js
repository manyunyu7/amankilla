import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

export const useSceneStore = defineStore('scene', () => {
    // State
    const scenes = ref([]);
    const currentScene = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const isDragging = ref(false);

    // Getters
    const sortedScenes = computed(() => {
        return [...scenes.value].sort((a, b) => a.order - b.order);
    });

    const sceneById = computed(() => {
        return (id) => scenes.value.find(s => s.id === id) || null;
    });

    const branchPointScenes = computed(() => {
        return scenes.value.filter(s => s.is_branch_point);
    });

    const totalWordCount = computed(() => {
        return scenes.value.reduce((sum, s) => sum + (s.word_count || 0), 0);
    });

    const sceneCount = computed(() => scenes.value.length);

    // Actions
    function setScenes(data) {
        scenes.value = data;
    }

    function setCurrentScene(scene) {
        currentScene.value = scene;
    }

    function addScene(scene) {
        scenes.value.push(scene);
    }

    function updateScene(id, data) {
        const index = scenes.value.findIndex(s => s.id === id);
        if (index !== -1) {
            scenes.value[index] = { ...scenes.value[index], ...data };
        }
        if (currentScene.value?.id === id) {
            currentScene.value = { ...currentScene.value, ...data };
        }
    }

    function removeScene(id) {
        scenes.value = scenes.value.filter(s => s.id !== id);
        if (currentScene.value?.id === id) {
            currentScene.value = null;
        }
    }

    function clearScenes() {
        scenes.value = [];
        currentScene.value = null;
    }

    // Reorder scenes (for drag and drop)
    function reorderScenes(newOrder) {
        // newOrder is an array of scene IDs in their new order
        const reorderedScenes = newOrder.map((id, index) => {
            const scene = scenes.value.find(s => s.id === id);
            return scene ? { ...scene, order: index + 1 } : null;
        }).filter(Boolean);

        scenes.value = reorderedScenes;
    }

    // Move scene to a new position
    function moveScene(fromIndex, toIndex) {
        const scenesCopy = [...sortedScenes.value];
        const [movedScene] = scenesCopy.splice(fromIndex, 1);
        scenesCopy.splice(toIndex, 0, movedScene);

        // Update order values
        scenesCopy.forEach((scene, index) => {
            scene.order = index + 1;
        });

        scenes.value = scenesCopy;
        return scenesCopy.map(s => s.id);
    }

    // Get previous and next scenes for navigation
    function getAdjacentScenes(sceneId) {
        const sorted = sortedScenes.value;
        const index = sorted.findIndex(s => s.id === sceneId);

        return {
            previous: index > 0 ? sorted[index - 1] : null,
            next: index < sorted.length - 1 ? sorted[index + 1] : null,
        };
    }

    // Set dragging state
    function setDragging(value) {
        isDragging.value = value;
    }

    return {
        // State
        scenes,
        currentScene,
        loading,
        error,
        isDragging,

        // Getters
        sortedScenes,
        sceneById,
        branchPointScenes,
        totalWordCount,
        sceneCount,

        // Actions
        setScenes,
        setCurrentScene,
        addScene,
        updateScene,
        removeScene,
        clearScenes,
        reorderScenes,
        moveScene,
        getAdjacentScenes,
        setDragging,
    };
});
