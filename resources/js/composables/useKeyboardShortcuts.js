import { onMounted, onUnmounted, ref } from 'vue';

/**
 * Composable for handling keyboard shortcuts
 * @param {Object} shortcuts - Object mapping key combinations to callbacks
 * @example
 * useKeyboardShortcuts({
 *   'ctrl+s': () => save(),
 *   'ctrl+shift+n': () => createNew(),
 *   'escape': () => close(),
 * })
 */
export function useKeyboardShortcuts(shortcuts = {}) {
    const activeModifiers = ref({
        ctrl: false,
        shift: false,
        alt: false,
        meta: false,
    });

    const parseShortcut = (shortcut) => {
        const parts = shortcut.toLowerCase().split('+');
        const key = parts.pop();
        const modifiers = {
            ctrl: parts.includes('ctrl') || parts.includes('control'),
            shift: parts.includes('shift'),
            alt: parts.includes('alt'),
            meta: parts.includes('meta') || parts.includes('cmd') || parts.includes('command'),
        };
        return { key, modifiers };
    };

    const matchesShortcut = (event, { key, modifiers }) => {
        const eventKey = event.key.toLowerCase();
        const eventModifiers = {
            ctrl: event.ctrlKey,
            shift: event.shiftKey,
            alt: event.altKey,
            meta: event.metaKey,
        };

        // Check if all required modifiers are pressed
        if (modifiers.ctrl !== eventModifiers.ctrl) return false;
        if (modifiers.shift !== eventModifiers.shift) return false;
        if (modifiers.alt !== eventModifiers.alt) return false;
        if (modifiers.meta !== eventModifiers.meta) return false;

        // Check the key
        return eventKey === key;
    };

    const handleKeyDown = (event) => {
        // Don't trigger shortcuts when typing in inputs
        const target = event.target;
        const isInput = target.tagName === 'INPUT' ||
            target.tagName === 'TEXTAREA' ||
            target.isContentEditable;

        // Allow escape to work in inputs
        if (isInput && event.key !== 'Escape') {
            return;
        }

        for (const [shortcut, callback] of Object.entries(shortcuts)) {
            const parsed = parseShortcut(shortcut);
            if (matchesShortcut(event, parsed)) {
                event.preventDefault();
                callback(event);
                return;
            }
        }
    };

    onMounted(() => {
        window.addEventListener('keydown', handleKeyDown);
    });

    onUnmounted(() => {
        window.removeEventListener('keydown', handleKeyDown);
    });

    return {
        activeModifiers,
    };
}

/**
 * Common keyboard shortcuts for the app
 */
export const commonShortcuts = {
    SAVE: 'ctrl+s',
    NEW: 'ctrl+n',
    SEARCH: 'ctrl+k',
    CLOSE: 'escape',
    DELETE: 'delete',
    UNDO: 'ctrl+z',
    REDO: 'ctrl+shift+z',
};

export default useKeyboardShortcuts;
