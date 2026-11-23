<script setup lang="ts">
import { REVENUE_SERIES, resolve } from '@/charts/palette';
import { useDarkMode } from '@/composables/useDarkMode';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import { computed, ref } from 'vue';

type SeriesKey = 'rooms' | 'food_beverage' | 'other';

const props = defineProps<{
    days: Array<{ day: string; rooms: number; food_beverage: number; other: number }>;
}>();

const { t } = useTranslations();
const { isDark } = useDarkMode();
const { compactMoney, compactNumber, money, shortDay, longDate } = useFormatters();

const SERIES: SeriesKey[] = ['rooms', 'food_beverage', 'other'];
const HEIGHT = 190;
const GAP = 2; // Surface gap between stacked segments.

const hovered = ref<number | null>(null);

const max = computed(() => Math.max(1, ...props.days.map((d) => d.rooms + d.food_beverage + d.other)));

/** Four gridlines, rounded to a readable step. */
const ticks = computed(() => {
    const step = max.value / 4;

    return [0, 1, 2, 3, 4].map((i) => ({
        value: step * i,
        y: HEIGHT - ((step * i) / max.value) * HEIGHT,
    }));
});

function segments(day: (typeof props.days)[number]) {
    let offset = 0;

    return SERIES.map((key) => {
        const value = day[key];
        const height = (value / max.value) * HEIGHT;
        const y = HEIGHT - offset - height;
        offset += height;

        return { key, value, y, height: Math.max(0, height - GAP) };
    }).filter((segment) => segment.value > 0);
}

const totalOf = (day: (typeof props.days)[number]) => day.rooms + day.food_beverage + day.other;
</script>

<template>
    <div>
        <!-- Legend: identity is never carried by colour alone. -->
        <ul class="mb-4 flex flex-wrap items-center gap-x-5 gap-y-2">
            <li v-for="key in SERIES" :key="key" class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 shrink-0 rounded-sm" :style="{ backgroundColor: resolve(REVENUE_SERIES[key], isDark) }" aria-hidden="true" />
                <span class="text-xs text-slate-600 dark:text-slate-400">
                    {{ t(`dashboard.revenue.series.${key}`) }}
                </span>
            </li>
        </ul>

        <div class="relative">
            <!-- Value axis -->
            <div class="flex">
                <div class="w-16 shrink-0">
                    <div
                        v-for="tick in [...ticks].reverse()"
                        :key="tick.value"
                        class="flex h-[47.5px] items-start justify-end pr-2 text-[10px] text-slate-400"
                    >
                        {{ compactNumber(tick.value) }}
                    </div>
                </div>

                <div class="relative flex-1">
                    <svg
                        :viewBox="`0 0 ${props.days.length * 40} ${HEIGHT}`"
                        preserveAspectRatio="none"
                        class="w-full"
                        :style="{ height: `${HEIGHT}px` }"
                        role="img"
                        :aria-label="t('dashboard.revenue.title')"
                    >
                        <line
                            v-for="tick in ticks"
                            :key="`grid-${tick.value}`"
                            x1="0"
                            :x2="props.days.length * 40"
                            :y1="tick.y"
                            :y2="tick.y"
                            stroke="currentColor"
                            class="text-slate-200 dark:text-slate-800"
                            stroke-width="1"
                            vector-effect="non-scaling-stroke"
                        />

                        <g v-for="(day, index) in props.days" :key="day.day">
                            <rect
                                :x="index * 40"
                                y="0"
                                width="40"
                                :height="HEIGHT"
                                fill="transparent"
                                @mouseenter="hovered = index"
                                @mouseleave="hovered = null"
                            />
                            <rect
                                v-for="segment in segments(day)"
                                :key="segment.key"
                                :x="index * 40 + 9"
                                :y="segment.y"
                                width="22"
                                :height="segment.height"
                                rx="4"
                                :fill="resolve(REVENUE_SERIES[segment.key], isDark)"
                                :opacity="hovered === null || hovered === index ? 1 : 0.45"
                                class="pointer-events-none transition-opacity duration-200"
                            />
                        </g>
                    </svg>

                    <!-- Day axis -->
                    <div class="flex">
                        <div v-for="day in props.days" :key="`label-${day.day}`" class="flex-1 pt-2 text-center text-[10px] text-slate-400">
                            {{ shortDay(day.day) }}
                        </div>
                    </div>

                    <!-- Tooltip, bounded to the plot: unclamped, it widens the page on the last bar. -->
                    <div
                        v-if="hovered !== null"
                        class="pointer-events-none absolute -top-2 z-10 w-48 -translate-y-full rounded-lg border border-slate-200 bg-white p-3 shadow-lg dark:border-slate-700 dark:bg-slate-900"
                        :style="{
                            left: `clamp(96px, ${((hovered + 0.5) / props.days.length) * 100}%, calc(100% - 96px))`,
                            transform: 'translate(-50%, -100%)',
                        }"
                    >
                        <p class="text-xs font-semibold text-slate-900 dark:text-slate-100">
                            {{ longDate(props.days[hovered].day) }}
                        </p>
                        <dl class="mt-2 grid gap-1">
                            <div v-for="key in SERIES" :key="key" class="flex items-center justify-between gap-3">
                                <dt class="flex items-center gap-1.5 text-[11px] text-slate-600 dark:text-slate-400">
                                    <span class="h-2 w-2 rounded-sm" :style="{ backgroundColor: resolve(REVENUE_SERIES[key], isDark) }" />
                                    {{ t(`dashboard.revenue.series.${key}`) }}
                                </dt>
                                <dd class="text-[11px] font-medium tabular-nums text-slate-900 dark:text-slate-100">
                                    {{ compactMoney(props.days[hovered][key]) }}
                                </dd>
                            </div>
                            <div class="mt-1 flex items-center justify-between gap-3 border-t border-slate-200 pt-1 dark:border-slate-700">
                                <dt class="text-[11px] font-semibold text-slate-900 dark:text-slate-100">
                                    {{ t('dashboard.total') }}
                                </dt>
                                <dd class="text-[11px] font-semibold tabular-nums text-slate-900 dark:text-slate-100">
                                    {{ money(totalOf(props.days[hovered])) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
