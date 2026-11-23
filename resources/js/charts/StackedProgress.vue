<script setup lang="ts">
import { HOUSEKEEPING, resolve } from '@/charts/palette';
import { useDarkMode } from '@/composables/useDarkMode';
import { useTranslations } from '@/composables/useTranslations';
import { computed } from 'vue';

const props = defineProps<{
    rows: Array<{ key: string; count: number }>;
    total: number;
}>();

const { t } = useTranslations();
const { isDark } = useDarkMode();

const segments = computed(() =>
    props.rows
        .filter((row) => row.count > 0)
        .map((row) => ({
            ...row,
            share: (row.count / Math.max(1, props.total)) * 100,
            colour: resolve(HOUSEKEEPING[row.key] ?? HOUSEKEEPING.clean, isDark.value),
        })),
);
</script>

<template>
    <div>
        <!-- 2px gaps keep adjacent fills apart without a border. -->
        <div class="flex h-2.5 w-full gap-0.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
            <div
                v-for="segment in segments"
                :key="segment.key"
                class="h-full first:rounded-l-full last:rounded-r-full"
                :style="{ width: `${segment.share}%`, backgroundColor: segment.colour }"
            />
        </div>

        <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2.5">
            <div v-for="row in rows" :key="row.key" class="flex items-center justify-between gap-3">
                <dt class="flex items-center gap-2 text-[13px] text-slate-700 dark:text-slate-300">
                    <span
                        class="h-2.5 w-2.5 shrink-0 rounded-full"
                        :style="{ backgroundColor: resolve(HOUSEKEEPING[row.key] ?? HOUSEKEEPING.clean, isDark) }"
                        aria-hidden="true"
                    />
                    {{ t(`dashboard.housekeeping.${row.key}`) }}
                </dt>
                <dd class="text-[13px] font-medium tabular-nums text-slate-900 dark:text-slate-100">
                    {{ row.count }}
                </dd>
            </div>
        </dl>
    </div>
</template>
