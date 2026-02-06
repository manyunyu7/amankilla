<script setup>
import { ref, watch, computed, onBeforeUnmount } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';
import Highlight from '@tiptap/extension-highlight';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Start writing your scene...',
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
    editable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'update']);

// Debounce timer for auto-save
let debounceTimer = null;
const DEBOUNCE_DELAY = 1000; // 1 second

const editor = useEditor({
    content: props.modelValue,
    editable: props.editable,
    autofocus: props.autofocus,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [2, 3],
            },
        }),
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
        Underline,
        TextAlign.configure({
            types: ['heading', 'paragraph'],
        }),
        Highlight.configure({
            multicolor: true,
        }),
    ],
    onUpdate: ({ editor }) => {
        const html = editor.getHTML();
        emit('update:modelValue', html);

        // Debounced update event for auto-save
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(() => {
            emit('update', html);
        }, DEBOUNCE_DELAY);
    },
});

// Watch for external content changes
watch(() => props.modelValue, (newValue) => {
    if (editor.value && newValue !== editor.value.getHTML()) {
        editor.value.commands.setContent(newValue, false);
    }
});

// Watch for editable changes
watch(() => props.editable, (newValue) => {
    if (editor.value) {
        editor.value.setEditable(newValue);
    }
});

// Word count
const wordCount = computed(() => {
    if (!editor.value) return 0;
    const text = editor.value.getText();
    return text.split(/\s+/).filter(word => word.length > 0).length;
});

// Character count
const characterCount = computed(() => {
    if (!editor.value) return 0;
    return editor.value.getText().length;
});

// Cleanup
onBeforeUnmount(() => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    if (editor.value) {
        editor.value.destroy();
    }
});

// Toolbar button helper
const isActive = (name, attributes = {}) => {
    return editor.value?.isActive(name, attributes) ?? false;
};

// Expose editor for parent components
defineExpose({
    editor,
    wordCount,
    characterCount,
});
</script>

<template>
    <div class="scene-editor">
        <!-- Toolbar -->
        <div v-if="editable" class="editor-toolbar flex flex-wrap items-center gap-1 p-2 bg-bg-light-gray border-2 border-b-0 border-border-gray rounded-t-xl">
            <!-- Text formatting -->
            <div class="flex items-center gap-1 pr-2 border-r border-border-gray">
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('bold') ? 'is-active' : '',
                    ]"
                    title="Bold (Ctrl+B)"
                    @click="editor?.chain().focus().toggleBold().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z" />
                    </svg>
                </button>
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('italic') ? 'is-active' : '',
                    ]"
                    title="Italic (Ctrl+I)"
                    @click="editor?.chain().focus().toggleItalic().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4m-2 0v16m-4 0h8" transform="skewX(-10)" />
                    </svg>
                </button>
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('underline') ? 'is-active' : '',
                    ]"
                    title="Underline (Ctrl+U)"
                    @click="editor?.chain().focus().toggleUnderline().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v7a5 5 0 0010 0V4M5 21h14" />
                    </svg>
                </button>
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('strike') ? 'is-active' : '',
                    ]"
                    title="Strikethrough"
                    @click="editor?.chain().focus().toggleStrike().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 10H7m10 4H7M12 4v16" />
                    </svg>
                </button>
            </div>

            <!-- Headings -->
            <div class="flex items-center gap-1 pr-2 border-r border-border-gray">
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('heading', { level: 2 }) ? 'is-active' : '',
                    ]"
                    title="Heading 2"
                    @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                >
                    <span class="text-xs font-bold">H2</span>
                </button>
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('heading', { level: 3 }) ? 'is-active' : '',
                    ]"
                    title="Heading 3"
                    @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                >
                    <span class="text-xs font-bold">H3</span>
                </button>
            </div>

            <!-- Lists -->
            <div class="flex items-center gap-1 pr-2 border-r border-border-gray">
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('bulletList') ? 'is-active' : '',
                    ]"
                    title="Bullet List"
                    @click="editor?.chain().focus().toggleBulletList().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('orderedList') ? 'is-active' : '',
                    ]"
                    title="Numbered List"
                    @click="editor?.chain().focus().toggleOrderedList().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 6h.01M3 12h.01M3 18h.01" />
                    </svg>
                </button>
            </div>

            <!-- Block formatting -->
            <div class="flex items-center gap-1 pr-2 border-r border-border-gray">
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('blockquote') ? 'is-active' : '',
                    ]"
                    title="Quote"
                    @click="editor?.chain().focus().toggleBlockquote().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </button>
                <button
                    type="button"
                    :class="[
                        'toolbar-btn',
                        isActive('highlight') ? 'is-active' : '',
                    ]"
                    title="Highlight"
                    @click="editor?.chain().focus().toggleHighlight().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                </button>
            </div>

            <!-- Undo/Redo -->
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="toolbar-btn"
                    title="Undo (Ctrl+Z)"
                    :disabled="!editor?.can().undo()"
                    @click="editor?.chain().focus().undo().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="toolbar-btn"
                    title="Redo (Ctrl+Shift+Z)"
                    :disabled="!editor?.can().redo()"
                    @click="editor?.chain().focus().redo().run()"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
                    </svg>
                </button>
            </div>

            <!-- Word count -->
            <div class="ml-auto text-xs text-text-hint">
                {{ wordCount.toLocaleString() }} words
            </div>
        </div>

        <!-- Editor content -->
        <div
            :class="[
                'editor-content-wrapper',
                editable ? 'rounded-b-xl' : 'rounded-xl',
                'border-2 border-border-gray bg-white',
            ]"
        >
            <EditorContent
                :editor="editor"
                class="prose prose-sm max-w-none p-4 min-h-[300px] focus:outline-none"
            />
        </div>
    </div>
</template>

<style scoped>
.toolbar-btn {
    padding: 0.5rem;
    border-radius: 0.5rem;
    color: #6B7280;
    transition: all 0.15s ease;
}

.toolbar-btn:hover {
    background-color: white;
    color: #1CB0F6;
}

.toolbar-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.toolbar-btn.is-active {
    background-color: #1CB0F6;
    color: white;
}

.editor-content-wrapper :deep(.ProseMirror) {
    outline: none;
    min-height: 300px;
}

.editor-content-wrapper :deep(.ProseMirror p.is-editor-empty:first-child::before) {
    color: #9CA3AF;
    content: attr(data-placeholder);
    float: left;
    height: 0;
    pointer-events: none;
}

.editor-content-wrapper :deep(.ProseMirror h2) {
    font-size: 1.25rem;
    font-weight: 700;
    font-family: 'Nunito', sans-serif;
    color: #1F2937;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
}

.editor-content-wrapper :deep(.ProseMirror h3) {
    font-size: 1.125rem;
    font-weight: 700;
    font-family: 'Nunito', sans-serif;
    color: #1F2937;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}

.editor-content-wrapper :deep(.ProseMirror p) {
    color: #1F2937;
    line-height: 1.625;
    margin-bottom: 1rem;
}

.editor-content-wrapper :deep(.ProseMirror blockquote) {
    border-left: 4px solid #1CB0F6;
    padding-left: 1rem;
    font-style: italic;
    color: #6B7280;
    margin: 1rem 0;
}

.editor-content-wrapper :deep(.ProseMirror mark) {
    background-color: #FEF08A;
    padding: 0 0.25rem;
    border-radius: 0.25rem;
}

.editor-content-wrapper :deep(.ProseMirror ul),
.editor-content-wrapper :deep(.ProseMirror ol) {
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.editor-content-wrapper :deep(.ProseMirror li) {
    margin-bottom: 0.25rem;
}
</style>
