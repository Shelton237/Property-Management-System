<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import { navigation } from '@/navigation';
import { Link, usePage } from '@inertiajs/vue3';
import { History } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
    collapsed: boolean;
    mobileOpen: boolean;
}>();

const emit = defineEmits<{ close: [] }>();

const { t, brandName } = useTranslations();

const page = usePage();

const hotel = computed(() => page.props.hotel as { name: string; city: string | null } | null);

const initial = computed(() => (hotel.value?.name ?? brandName.value).charAt(0).toUpperCase());

/** Ziggy knows the current route; an entry without a route is never active. */
function isCurrent(name?: string): boolean {
    return name !== undefined && route().current(name);
}
</script>

<template>
    <!-- Backdrop, small screens only. -->
    <div v-if="mobileOpen" class="fixed inset-0 z-30 bg-slate-900/40 lg:hidden" aria-hidden="true" @click="emit('close')" />

    <aside
        class="fixed inset-y-0 left-0 z-40 flex flex-col border-r border-slate-200 bg-white transition-[width,transform] duration-200 dark:border-slate-800 dark:bg-slate-900"
        :class="[collapsed ? 'w-16' : 'w-[232px]', mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
    >
        <!-- Property identity -->
        <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-slate-200 px-4 dark:border-slate-800">
            <span
                class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-900 text-sm font-semibold text-white dark:bg-white dark:text-slate-900"
                aria-hidden="true"
            >
                {{ initial }}
            </span>

            <span v-if="!collapsed" class="min-w-0">
                <span class="block truncate text-sm font-semibold text-slate-900 dark:text-slate-50">
                    {{ hotel?.name ?? brandName }}
                </span>
                <span class="block truncate text-xs text-slate-500 dark:text-slate-400">
                    <template v-if="hotel?.city">{{ hotel.city }} · </template>{{ t('navigation.property_label') }}
                </span>
            </span>
        </div>

        <!-- Modules -->
        <nav :aria-label="t('navigation.aria_label')" class="flex-1 overflow-y-auto px-3 py-4">
            <div v-for="section in navigation" :key="section.key" class="mb-5 last:mb-0">
                <p v-if="!collapsed" class="px-3 pb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    {{ t(`navigation.sections.${section.key}`) }}
                </p>
                <hr v-else class="mx-2 mb-2 border-slate-200 dark:border-slate-800" />

                <ul class="grid gap-0.5">
                    <li v-for="item in section.items" :key="item.key">
                        <!-- Module already built: a real link. -->
                        <Link
                            v-if="item.route"
                            :href="route(item.route)"
                            :aria-current="isCurrent(item.route) ? 'page' : undefined"
                            :title="collapsed ? t(`navigation.items.${item.key}`) : undefined"
                            class="flex min-h-[44px] items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:focus-visible:ring-offset-slate-900"
                            :class="[
                                collapsed ? 'justify-center px-0' : '',
                                isCurrent(item.route)
                                    ? 'bg-brand-50 font-medium text-brand-900 dark:bg-brand-950 dark:text-brand-100'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
                            ]"
                            @click="emit('close')"
                        >
                            <component
                                :is="item.icon"
                                class="h-4 w-4 shrink-0"
                                :class="isCurrent(item.route) ? 'text-brand-700 dark:text-brand-400' : ''"
                                aria-hidden="true"
                            />
                            <span v-if="!collapsed" class="truncate">{{ t(`navigation.items.${item.key}`) }}</span>
                        </Link>

                        <!-- Module not built yet: shown, but nothing to open. -->
                        <span
                            v-else
                            :title="`${t(`navigation.items.${item.key}`)} — ${t('navigation.coming_soon')}`"
                            class="flex min-h-[44px] cursor-default items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-400 dark:text-slate-600"
                            :class="collapsed ? 'justify-center px-0' : ''"
                        >
                            <component :is="item.icon" class="h-4 w-4 shrink-0" aria-hidden="true" />
                            <span v-if="!collapsed" class="truncate">{{ t(`navigation.items.${item.key}`) }}</span>
                        </span>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Night audit state. No module yet, so no figures are shown. -->
        <div class="shrink-0 p-3">
            <div
                class="flex items-start gap-2.5 rounded-xl border border-slate-200 p-3 dark:border-slate-800"
                :class="collapsed ? 'justify-center' : ''"
                :title="collapsed ? t('navigation.night_audit_card.title') : undefined"
            >
                <History class="mt-0.5 h-4 w-4 shrink-0 text-slate-400 dark:text-slate-500" aria-hidden="true" />
                <div v-if="!collapsed" class="min-w-0">
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                        {{ t('navigation.night_audit_card.title') }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ t('navigation.night_audit_card.not_configured') }}
                    </p>
                </div>
            </div>
        </div>
    </aside>
</template>
