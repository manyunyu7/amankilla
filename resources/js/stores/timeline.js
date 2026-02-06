import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

export const useTimelineStore = defineStore('timeline', () => {
    // State
    const timelines = ref([]);
    const currentTimeline = ref(null);
    const loading = ref(false);
    const error = ref(null);

    // Getters
    const canonTimeline = computed(() => {
        return timelines.value.find(t => t.is_canon) || null;
    });

    const alternateTimelines = computed(() => {
        return timelines.value.filter(t => !t.is_canon);
    });

    const timelineById = computed(() => {
        return (id) => timelines.value.find(t => t.id === id) || null;
    });

    const sortedTimelines = computed(() => {
        // Canon timeline first, then alternates sorted by created_at
        return [...timelines.value].sort((a, b) => {
            if (a.is_canon && !b.is_canon) return -1;
            if (!a.is_canon && b.is_canon) return 1;
            return new Date(a.created_at) - new Date(b.created_at);
        });
    });

    // Actions
    function setTimelines(data) {
        timelines.value = data;
    }

    function setCurrentTimeline(timeline) {
        currentTimeline.value = timeline;
    }

    function addTimeline(timeline) {
        timelines.value.push(timeline);
    }

    function updateTimeline(id, data) {
        const index = timelines.value.findIndex(t => t.id === id);
        if (index !== -1) {
            timelines.value[index] = { ...timelines.value[index], ...data };
        }
        if (currentTimeline.value?.id === id) {
            currentTimeline.value = { ...currentTimeline.value, ...data };
        }
    }

    function removeTimeline(id) {
        timelines.value = timelines.value.filter(t => t.id !== id);
        if (currentTimeline.value?.id === id) {
            currentTimeline.value = null;
        }
    }

    function clearTimelines() {
        timelines.value = [];
        currentTimeline.value = null;
    }

    // Get branch origin info for a timeline
    function getBranchOrigin(timeline) {
        if (!timeline?.branch_from_id) return null;

        // Find the scene that this timeline branches from
        for (const t of timelines.value) {
            const scene = t.scenes?.find(s => s.id === timeline.branch_from_id);
            if (scene) {
                return {
                    timeline: t,
                    scene: scene,
                };
            }
        }
        return null;
    }

    return {
        // State
        timelines,
        currentTimeline,
        loading,
        error,

        // Getters
        canonTimeline,
        alternateTimelines,
        timelineById,
        sortedTimelines,

        // Actions
        setTimelines,
        setCurrentTimeline,
        addTimeline,
        updateTimeline,
        removeTimeline,
        clearTimelines,
        getBranchOrigin,
    };
});
