<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { VueFlow, useVueFlow } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';
import { MiniMap } from '@vue-flow/minimap';
import SceneNode from './SceneNode.vue';

const props = defineProps({
    timelines: {
        type: Array,
        default: () => [],
    },
    scenes: {
        type: Array,
        default: () => [],
    },
    selectedSceneId: {
        type: [Number, String],
        default: null,
    },
    activeTimelineId: {
        type: [Number, String],
        default: null,
    },
    showMinimap: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['scene-click', 'scene-dblclick', 'update:selectedSceneId']);

// Track if graph has been rendered for animation
const isRendered = ref(false);
// Track if touch hint should be shown
const showTouchHint = ref(true);

const { fitView } = useVueFlow();

// Generate nodes and edges from scenes
const nodes = computed(() => {
    const result = [];
    const timelineYPositions = {};
    let y = 0;

    // Assign Y position for each timeline
    props.timelines.forEach((timeline) => {
        timelineYPositions[timeline.id] = y;
        y += 180; // Spacing between timelines
    });

    // Create nodes for each scene
    props.scenes.forEach((scene, index) => {
        const timeline = props.timelines.find(t => t.id === scene.timeline_id);
        const yPos = timelineYPositions[scene.timeline_id] || 0;
        const isInActiveTimeline = props.activeTimelineId ? scene.timeline_id === props.activeTimelineId : true;

        result.push({
            id: `scene-${scene.id}`,
            type: 'sceneNode',
            position: { x: (scene.order - 1) * 280, y: yPos },
            data: {
                scene,
                timeline,
                isHighlighted: isInActiveTimeline,
                animationDelay: isRendered.value ? 0 : index * 50, // Staggered animation on first render
            },
            selected: props.selectedSceneId === scene.id,
            class: isInActiveTimeline ? '' : 'opacity-40',
        });
    });

    return result;
});

const edges = computed(() => {
    const result = [];
    const scenesByTimeline = {};

    // Group scenes by timeline
    props.scenes.forEach((scene) => {
        if (!scenesByTimeline[scene.timeline_id]) {
            scenesByTimeline[scene.timeline_id] = [];
        }
        scenesByTimeline[scene.timeline_id].push(scene);
    });

    // Create edges within each timeline
    Object.values(scenesByTimeline).forEach((timelineScenes) => {
        const sorted = [...timelineScenes].sort((a, b) => a.order - b.order);
        for (let i = 0; i < sorted.length - 1; i++) {
            const timeline = props.timelines.find(t => t.id === sorted[i].timeline_id);
            const isInActiveTimeline = props.activeTimelineId ? sorted[i].timeline_id === props.activeTimelineId : true;
            result.push({
                id: `edge-${sorted[i].id}-${sorted[i + 1].id}`,
                source: `scene-${sorted[i].id}`,
                target: `scene-${sorted[i + 1].id}`,
                type: 'smoothstep',
                animated: isInActiveTimeline && props.activeTimelineId,
                style: {
                    stroke: timeline?.color || '#1CB0F6',
                    strokeWidth: isInActiveTimeline ? 4 : 2,
                    opacity: isInActiveTimeline ? 1 : 0.3,
                    transition: 'all 0.3s ease',
                },
            });
        }
    });

    // Create branch edges (from branch point to branched timeline's first scene)
    props.timelines.forEach((timeline) => {
        if (timeline.branch_from_scene_id) {
            const branchScene = props.scenes.find(s => s.id === timeline.branch_from_scene_id);
            const firstSceneInBranch = props.scenes
                .filter(s => s.timeline_id === timeline.id)
                .sort((a, b) => a.order - b.order)[0];

            if (branchScene && firstSceneInBranch) {
                result.push({
                    id: `branch-edge-${branchScene.id}-${firstSceneInBranch.id}`,
                    source: `scene-${branchScene.id}`,
                    target: `scene-${firstSceneInBranch.id}`,
                    type: 'smoothstep',
                    animated: true,
                    style: {
                        stroke: timeline.color || '#F59E0B',
                        strokeWidth: 2,
                        strokeDasharray: '5 5',
                    },
                });
            }
        }
    });

    return result;
});

const handleNodeClick = (event) => {
    const nodeId = event.node.id;
    const sceneId = parseInt(nodeId.replace('scene-', ''));
    const scene = props.scenes.find(s => s.id === sceneId);
    if (scene) {
        emit('update:selectedSceneId', sceneId);
        emit('scene-click', scene);
    }
};

const handleNodeDoubleClick = (event) => {
    const nodeId = event.node.id;
    const sceneId = parseInt(nodeId.replace('scene-', ''));
    const scene = props.scenes.find(s => s.id === sceneId);
    if (scene) {
        emit('scene-dblclick', scene);
    }
};

onMounted(() => {
    // Fit view after a short delay to ensure nodes are rendered
    setTimeout(() => {
        fitView({ padding: 0.2 });
        // Mark as rendered after initial animation
        setTimeout(() => {
            isRendered.value = true;
        }, 500);
    }, 100);

    // Hide touch hint after 5 seconds
    setTimeout(() => {
        showTouchHint.value = false;
    }, 5000);
});

// Custom node type registration
const nodeTypes = {
    sceneNode: SceneNode,
};
</script>

<template>
    <div class="timeline-graph w-full h-full bg-bg-paper rounded-xl overflow-hidden">
        <VueFlow
            :nodes="nodes"
            :edges="edges"
            :node-types="nodeTypes"
            :default-viewport="{ x: 50, y: 50, zoom: 0.8 }"
            :fit-view-on-init="true"
            :pan-on-scroll="true"
            :zoom-on-scroll="true"
            :zoom-on-pinch="true"
            :pan-on-drag="true"
            :prevent-scrolling="false"
            :min-zoom="0.2"
            :max-zoom="2"
            :snap-to-grid="false"
            selection-key-code=""
            pan-activation-key-code=""
            @node-click="handleNodeClick"
            @node-double-click="handleNodeDoubleClick"
        >
            <Background pattern-color="#E5E7EB" :gap="20" />
            <Controls position="bottom-right" />
            <MiniMap v-if="showMinimap" position="bottom-left" />
        </VueFlow>

        <!-- Timeline Legend -->
        <div class="absolute top-4 left-4 bg-white rounded-xl shadow-md p-3 space-y-2 max-w-xs z-10">
            <h4 class="font-nunito font-bold text-sm text-text-primary">Timelines</h4>
            <div
                v-for="timeline in timelines"
                :key="timeline.id"
                class="flex items-center gap-2"
            >
                <span
                    class="w-3 h-3 rounded-full shrink-0"
                    :style="{ backgroundColor: timeline.color || '#1CB0F6' }"
                />
                <span class="text-sm text-text-secondary truncate">
                    {{ timeline.name }}
                </span>
                <span
                    v-if="timeline.is_canon"
                    class="text-xs px-1.5 py-0.5 bg-success-light text-success rounded-full"
                >
                    Canon
                </span>
            </div>
        </div>

        <!-- Mobile touch hint (hidden on desktop) -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            leave-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showTouchHint"
                class="absolute bottom-20 left-1/2 -translate-x-1/2 sm:hidden bg-black/60 text-white text-xs px-3 py-1.5 rounded-full z-10 pointer-events-none"
            >
                Pinch to zoom • Drag to pan
            </div>
        </Transition>
    </div>
</template>

<style>
/* Vue Flow base styles */
@import '@vue-flow/core/dist/style.css';
@import '@vue-flow/core/dist/theme-default.css';
@import '@vue-flow/controls/dist/style.css';
@import '@vue-flow/minimap/dist/style.css';

.timeline-graph {
    font-family: 'Nunito', sans-serif;
}

/* Custom controls styling */
.vue-flow__controls {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    border: 2px solid #E5E7EB;
}

.vue-flow__controls-button {
    background: white;
    border: none;
    width: 32px;
    height: 32px;
}

.vue-flow__controls-button:hover {
    background: #F3F4F6;
}

/* Custom minimap styling */
.vue-flow__minimap {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    border: 2px solid #E5E7EB;
}

/* Node transitions */
.vue-flow__node {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

/* Edge transitions */
.vue-flow__edge path {
    transition: stroke-width 0.3s ease, opacity 0.3s ease, stroke 0.3s ease;
}

/* Animate edges for active timeline */
.vue-flow__edge.animated path {
    stroke-dasharray: 5;
    animation: flowDash 0.5s linear infinite;
}

@keyframes flowDash {
    to {
        stroke-dashoffset: -10;
    }
}

/* Smooth panning transition */
.vue-flow__viewport {
    transition: transform 0.3s ease;
}
</style>
