<script setup lang="ts">
import { MAGNITUDE, resolve } from '@/charts/palette';
import { useDarkMode } from '@/composables/useDarkMode';
import { useFormatters } from '@/composables/useFormatters';
import { computed, ref } from 'vue';

const props = defineProps<{
    points: Array<{ day: string; value: number }>;
    label: string;
}>();

const { isDark } = useDarkMode();
const { percent, longDate, shortDay } = useFormatters();

const WIDTH = 600;
const HEIGHT = 190;

const colour = computed(() => resolve(MAGNITUDE, isDark.value));
const hovered = ref<number | null>(null);

const coords = computed(() => {
    const count = Math.max(1, props.points.length - 1);

    return props.points.map((point, index) => ({
        ...point,
        x: (index / count) * WIDTH,
        y: HEIGHT - (Math.min(100, Math.max(0, point.value)) / 100) * HEIGHT,
    }));
});

const line = computed(() => coords.value.map((p) => `${p.x},${p.y}`).join(' '));

const ticks = [0, 25, 50, 75, 100].map((value) => ({ value, y: HEIGHT - (value / 100) * HEIGHT }));

/** Only the first, middle and last dates are labelled: a label per point is noise. */
const axisLabels = computed(() => {
    const items = coords.value;
    if (items.length === 0) return [];

    return [items[0], items[Math.floor(items.length / 2)], items[items.length - 1]];
});

function onMove(event: MouseEvent) {
    const target = event.currentTarget as HTMLElement;
    const ratio = (event.clientX - target.getBoundingClientRect().left) / target.offsetWidth;
    hovered.value = Math.min(props.points.length - 1, Math.max(0, Math.round(ratio * (props.points.length - 1))));
}
</script>

<template>
    <div class="flex">
        <div class="w-10 shrink-0">
            <div
                v-for="tick in [...ticks].reverse()"
                :key="tick.value"
                class="flex h-[47.5px] items-start justify-end pr-2 text-[10px] text-slate-400"
            >
                {{ tick.value }} %
            </div>
        </div>

        <div class="relative flex-1" @mousemove="onMove" @mouseleave="hovered = null">
            <svg
                :viewBox="`0 0 ${WIDTH} ${HEIGHT}`"
                preserveAspectRatio="none"
                class="w-full"
                :style="{ height: `${HEIGHT}px` }"
                role="img"
                :aria-label="label"
            >
                <line
                    v-for="tick in ticks"
                    :key="tick.value"
                    x1="0"
                    :x2="WIDTH"
                    :y1="tick.y"
                    :y2="tick.y"
                    stroke="currentColor"
                    class="text-slate-200 dark:text-slate-800"
                    stroke-width="1"
                    vector-effect="non-scaling-stroke"
                />

                <polyline
                    :points="line"
                    fill="none"
                    :stroke="colour"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    vector-effect="non-scaling-stroke"
                />

                <template v-if="hovered !== null && coords[hovered]">
                    <line
                        :x1="coords[hovered].x"
                        :x2="coords[hovered].x"
                        y1="0"
                        :y2="HEIGHT"
                        stroke="currentColor"
                        class="text-slate-300 dark:text-slate-600"
                        stroke-width="1"
                        vector-effect="non-scaling-stroke"
                    />
                    <circle
                        :cx="coords[hovered].x"
                        :cy="coords[hovered].y"
                        r="5"
                        :fill="colour"
                        stroke="white"
                        stroke-width="2"
                        vector-effect="non-scaling-stroke"
                    />
                </template>
            </svg>

            <div class="flex justify-between pt-2">
                <span v-for="point in axisLabels" :key="point.day" class="text-[10px] text-slate-400">
                    {{ shortDay(point.day) }}
                </span>
            </div>

            <div
                v-if="hovered !== null && coords[hovered]"
                class="pointer-events-none absolute top-0 z-10 rounded-lg border border-slate-200 bg-white px-3 py-2 shadow-lg dark:border-slate-700 dark:bg-slate-900"
                :style="{
                    left: `clamp(70px, ${(coords[hovered].x / WIDTH) * 100}%, calc(100% - 70px))`,
                    transform: 'translateX(-50%)',
                }"
            >
                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                    {{ longDate(coords[hovered].day) }}
                </p>
                <p class="text-xs font-semibold tabular-nums text-slate-900 dark:text-slate-100">
                    {{ percent(coords[hovered].value, 1) }}
                </p>
            </div>
        </div>
    </div>
</template>
