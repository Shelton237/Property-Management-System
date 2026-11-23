<script setup lang="ts">
import { MAGNITUDE, resolve } from '@/charts/palette';
import { useDarkMode } from '@/composables/useDarkMode';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';

defineProps<{
    rows: Array<{ key: string; amount: number; share: number }>;
    /** Translation group holding one label per row key. */
    labels: string;
}>();

const { t } = useTranslations();
const { isDark } = useDarkMode();
const { money } = useFormatters();

/*
 * One hue on purpose: these bars compare magnitudes inside a single dimension,
 * so colour carries no identity. Every row is named and valued in text.
 */
</script>

<template>
    <dl class="grid gap-3.5">
        <div v-for="row in rows" :key="row.key">
            <div class="flex items-baseline justify-between gap-3">
                <dt class="truncate text-[13px] text-slate-700 dark:text-slate-300">
                    {{ t(`${labels}.${row.key}`) }}
                </dt>
                <dd class="flex shrink-0 items-baseline gap-2">
                    <span class="text-[13px] font-medium tabular-nums text-slate-900 dark:text-slate-100">
                        {{ money(row.amount) }}
                    </span>
                    <span class="w-10 text-right text-[11px] tabular-nums text-slate-400">{{ row.share }} %</span>
                </dd>
            </div>

            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <!-- A negative amount gets no length: its value reports it. -->
                <div
                    class="h-full rounded-full transition-[width] duration-300"
                    :style="{
                        width: `${row.amount > 0 ? Math.max(1, row.share) : 0}%`,
                        backgroundColor: resolve(MAGNITUDE, isDark),
                    }"
                />
            </div>
        </div>
    </dl>
</template>
