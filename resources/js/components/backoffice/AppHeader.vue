<script setup lang="ts">
import UserMenu from '@/components/backoffice/UserMenu.vue';
import { useAppearance } from '@/composables/useAppearance';
import { useTranslations } from '@/composables/useTranslations';
import { usePage } from '@inertiajs/vue3';
import { Bell, CalendarPlus, Moon, PanelLeft, Search, Sun } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

defineProps<{ collapsed: boolean }>();

const emit = defineEmits<{ toggle: [] }>();

const { t } = useTranslations();

const page = usePage();

const businessDate = computed(() => page.props.businessDate as { label: string; iso: string } | null);

const { updateAppearance } = useAppearance();

const isDark = ref(false);

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark');
});

function toggleTheme(): void {
    updateAppearance(isDark.value ? 'light' : 'dark');
    isDark.value = !isDark.value;
}
</script>

<template>
    <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white px-4 dark:border-slate-800 dark:bg-slate-900">
        <button
            type="button"
            :aria-label="t('navigation.header.toggle_sidebar')"
            :aria-expanded="!collapsed"
            class="grid h-11 w-11 shrink-0 cursor-pointer place-items-center rounded-lg text-slate-500 transition-colors duration-200 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
            @click="emit('toggle')"
        >
            <PanelLeft class="h-4 w-4" aria-hidden="true" />
        </button>

        <!--
            Search is part of the shell, but no module can be searched yet:
            the field is shown disabled rather than pretending to work.
        -->
        <div class="relative hidden min-w-0 flex-1 md:block md:max-w-sm">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
            <input
                type="search"
                disabled
                :placeholder="t('navigation.header.search_placeholder')"
                :title="t('navigation.header.search_unavailable')"
                class="w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 py-2 pl-9 pr-16 text-sm text-slate-500 placeholder:text-slate-400 dark:border-slate-800 dark:bg-slate-950 dark:placeholder:text-slate-600"
            />
            <kbd
                class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 rounded border border-slate-200 px-1.5 py-0.5 text-[10px] font-medium text-slate-400 dark:border-slate-700"
            >
                {{ t('navigation.header.shortcut') }}
            </kbd>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <p
                v-if="businessDate"
                class="hidden items-center gap-2 rounded-lg bg-slate-50 px-3 py-1.5 text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300 lg:flex"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-brand-600" aria-hidden="true" />
                <span class="text-slate-400 dark:text-slate-500">{{ t('navigation.header.business_date') }}</span>
                <time :datetime="businessDate.iso" class="font-medium text-slate-900 dark:text-slate-100">
                    {{ businessDate.label }}
                </time>
            </p>

            <!-- Bookings do not exist yet, so the action is visible but inert. -->
            <button
                type="button"
                disabled
                :title="t('navigation.coming_soon')"
                class="hidden cursor-not-allowed items-center gap-2 rounded-lg bg-brand-800 px-3 py-2 text-sm font-semibold text-white opacity-60 sm:inline-flex"
            >
                <CalendarPlus class="h-4 w-4" aria-hidden="true" />
                {{ t('navigation.header.new_booking') }}
            </button>

            <button
                type="button"
                :aria-label="isDark ? t('navigation.header.toggle_theme_light') : t('navigation.header.toggle_theme_dark')"
                class="grid h-11 w-11 cursor-pointer place-items-center rounded-lg text-slate-500 transition-colors duration-200 hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100"
                @click="toggleTheme"
            >
                <Sun v-if="isDark" class="h-4 w-4" aria-hidden="true" />
                <Moon v-else class="h-4 w-4" aria-hidden="true" />
            </button>

            <!-- No notification source yet: no badge is shown rather than a made up count. -->
            <button
                type="button"
                disabled
                :aria-label="t('navigation.header.notifications')"
                :title="t('navigation.header.no_notifications')"
                class="grid h-11 w-11 cursor-not-allowed place-items-center rounded-lg text-slate-300 dark:text-slate-600"
            >
                <Bell class="h-4 w-4" aria-hidden="true" />
            </button>

            <UserMenu />
        </div>
    </header>
</template>
