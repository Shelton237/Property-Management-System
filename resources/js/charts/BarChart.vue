<script setup lang="ts">
import { MAGNITUDE, resolve } from '@/charts/palette';
import { useDarkMode } from '@/composables/useDarkMode';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import { computed, ref } from 'vue';

const props = defineProps<{
    points: Array<{ day: string; sold: number; share: number }>;
    label: string;
}>();

const { t } = useTranslations();
const { isDark } = useDarkMode();
const { shortDay, weekday, longDate } = useFormatters();

const HEIGHT = 150;

const colour = computed(() => resolve(MAGNITUDE, isDark.value));
const hovered = ref<number | null>(null);

const max = computed(() => Math.max(1, ...props.points.map((p) => p.share)));

function height(share: number): number {
    return Math.max(share > 0 ? 4 : 2, (share / max.value) * HEIGHT);
}
</script>

<template>
    <div>
        <div class="flex items-end gap-1.5" :style="{ height: `${HEIGHT}px` }" role="img" :aria-label="label">
            <div
                v-for="(point, index) in props.points"
                :key="point.day"
                class="relative flex-1"
                @mouseenter="hovered = index"
                @mouseleave="hovered = null"
            >
                <div
                    class="w-full rounded-t transition-opacity duration-200"
                    :style="{
                        height: `${height(point.share)}px`,
                        backgroundColor: point.share > 0 ? colour : undefined,
                        opacity: hovered === null || hovered === index ? 1 : 0.45,
                    }"
                    :class="point.share === 0 ? 'bg-slate-200 dark:bg-slate-700' : ''"
                />

                <div
                    v-if="hovered === index"
                    class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 w-max -translate-x-1/2 rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-lg dark:border-slate-700 dark:bg-slate-900"
                >
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ longDate(point.day) }}</p>
                    <p class="text-xs font-semibold tabular-nums text-slate-900 dark:text-slate-100">
                        {{ t('dashboard.on_the_books.rooms_sold', { count: point.sold }) }} · {{ point.share }} %
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-2 flex gap-1.5">
            <div v-for="(point, index) in props.points" :key="`axis-${point.day}`" class="flex-1 text-center">
                <p class="text-[10px] text-slate-400">{{ index === 0 ? t('dashboard.today') : weekday(point.day) }}</p>
                <p class="text-[10px] font-medium text-slate-500 dark:text-slate-400">{{ shortDay(point.day) }}</p>
            </div>
        </div>
    </div>
</template>
