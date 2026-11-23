<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import { ref } from 'vue';

withDefaults(
    defineProps<{
        title: string;
        subtitle?: string;
        /** Offers the table alternative required for any colour-coded chart. */
        views?: boolean;
    }>(),
    { views: false },
);

const { t } = useTranslations();

const view = ref<'chart' | 'table'>('chart');
</script>

<template>
    <section class="rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
        <header class="mb-4 flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-[15px] font-semibold text-slate-900 dark:text-slate-100">{{ title }}</h2>
                <p v-if="subtitle" class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ subtitle }}</p>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <slot name="action" />

                <div v-if="views" class="flex rounded-lg border border-slate-200 p-0.5 dark:border-slate-700" role="tablist">
                    <button
                        v-for="mode in ['chart', 'table'] as const"
                        :key="mode"
                        type="button"
                        role="tab"
                        :aria-selected="view === mode"
                        class="cursor-pointer rounded-md px-2.5 py-1 text-xs font-medium transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                        :class="
                            view === mode
                                ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100'
                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100'
                        "
                        @click="view = mode"
                    >
                        {{ t(`dashboard.view.${mode}`) }}
                    </button>
                </div>
            </div>
        </header>

        <slot v-if="!views || view === 'chart'" />
        <slot v-else name="table" />
    </section>
</template>
