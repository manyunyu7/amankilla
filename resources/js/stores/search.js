import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useSearchStore = defineStore('search', () => {
    // State
    const query = ref('');
    const results = ref([]);
    const total = ref(0);
    const loading = ref(false);
    const filters = ref({
        tag_ids: [],
        character_ids: [],
        mood: '',
        timeline_id: null,
    });
    const sort = ref('order');
    const sortDir = ref('asc');

    // Filter options (fetched from backend)
    const availableTags = ref([]);
    const availableCharacters = ref([]);
    const availableTimelines = ref([]);
    const availableMoods = ref([]);

    // Computed
    const hasActiveFilters = computed(() => {
        return filters.value.tag_ids.length > 0
            || filters.value.character_ids.length > 0
            || filters.value.mood
            || filters.value.timeline_id;
    });

    const hasResults = computed(() => results.value.length > 0);

    // Actions
    const search = async (universeId) => {
        loading.value = true;
        try {
            const params = new URLSearchParams();
            if (query.value) params.append('q', query.value);
            if (filters.value.tag_ids.length) {
                filters.value.tag_ids.forEach(id => params.append('tag_ids[]', id));
            }
            if (filters.value.character_ids.length) {
                filters.value.character_ids.forEach(id => params.append('character_ids[]', id));
            }
            if (filters.value.mood) params.append('mood', filters.value.mood);
            if (filters.value.timeline_id) params.append('timeline_id', filters.value.timeline_id);
            params.append('sort', sort.value);
            params.append('sort_dir', sortDir.value);

            const response = await fetch(`/universes/${universeId}/search/api?${params}`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();

            results.value = data.results;
            total.value = data.total;
        } catch (error) {
            console.error('Search error:', error);
            results.value = [];
            total.value = 0;
        } finally {
            loading.value = false;
        }
    };

    const loadFilters = async (universeId) => {
        try {
            const response = await fetch(`/universes/${universeId}/search/filters`, {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();

            availableTags.value = data.tags;
            availableCharacters.value = data.characters;
            availableTimelines.value = data.timelines;
            availableMoods.value = data.moods;
        } catch (error) {
            console.error('Failed to load filters:', error);
        }
    };

    const clearFilters = () => {
        filters.value = {
            tag_ids: [],
            character_ids: [],
            mood: '',
            timeline_id: null,
        };
    };

    const clearAll = () => {
        query.value = '';
        results.value = [];
        total.value = 0;
        clearFilters();
    };

    const toggleTag = (tagId) => {
        const index = filters.value.tag_ids.indexOf(tagId);
        if (index === -1) {
            filters.value.tag_ids.push(tagId);
        } else {
            filters.value.tag_ids.splice(index, 1);
        }
    };

    const toggleCharacter = (characterId) => {
        const index = filters.value.character_ids.indexOf(characterId);
        if (index === -1) {
            filters.value.character_ids.push(characterId);
        } else {
            filters.value.character_ids.splice(index, 1);
        }
    };

    return {
        // State
        query,
        results,
        total,
        loading,
        filters,
        sort,
        sortDir,
        availableTags,
        availableCharacters,
        availableTimelines,
        availableMoods,

        // Computed
        hasActiveFilters,
        hasResults,

        // Actions
        search,
        loadFilters,
        clearFilters,
        clearAll,
        toggleTag,
        toggleCharacter,
    };
});
