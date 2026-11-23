import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Charts need the resolved theme, not the stored preference: `system` can mean
 * either. Watching the class on <html> covers the toggle and the OS switch.
 */
export function useDarkMode() {
    const isDark = ref(false);
    let observer: MutationObserver | null = null;

    onMounted(() => {
        const root = document.documentElement;
        isDark.value = root.classList.contains('dark');

        observer = new MutationObserver(() => {
            isDark.value = root.classList.contains('dark');
        });

        observer.observe(root, { attributes: true, attributeFilter: ['class'] });
    });

    onBeforeUnmount(() => observer?.disconnect());

    return { isDark };
}
