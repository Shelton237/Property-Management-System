import { onMounted, ref } from 'vue';

const STORAGE_KEY = 'backoffice.sidebar.collapsed';
const DESKTOP = '(min-width: 1024px)';

/*
 * Module level refs on purpose: the header toggles the state the sidebar reads,
 * and both must share the same instance.
 */
const collapsed = ref(false);
const mobileOpen = ref(false);

function isDesktop(): boolean {
    return typeof window !== 'undefined' && window.matchMedia(DESKTOP).matches;
}

function store(value: boolean): void {
    try {
        localStorage.setItem(STORAGE_KEY, String(value));
    } catch {
        // Private browsing or blocked storage: the preference simply does not persist.
    }
}

function restore(): boolean {
    try {
        return localStorage.getItem(STORAGE_KEY) === 'true';
    } catch {
        return false;
    }
}

export function useSidebar() {
    onMounted(() => {
        collapsed.value = restore();
    });

    /** Narrow rail on desktop, overlay drawer on smaller screens. */
    function toggle(): void {
        if (isDesktop()) {
            collapsed.value = !collapsed.value;
            store(collapsed.value);

            return;
        }

        mobileOpen.value = !mobileOpen.value;
    }

    function closeMobile(): void {
        mobileOpen.value = false;
    }

    return { collapsed, mobileOpen, toggle, closeMobile };
}
