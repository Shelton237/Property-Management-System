<script setup lang="ts">
import { MAGNITUDE, resolve } from '@/charts/palette';
import { useDarkMode } from '@/composables/useDarkMode';
import { computed } from 'vue';

const props = withDefaults(defineProps<{ values: number[]; height?: number }>(), { height: 40 });

const { isDark } = useDarkMode();

const WIDTH = 160;

const colour = computed(() => resolve(MAGNITUDE, isDark.value));

/** A flat series still needs a line, so a zero range becomes a mid-height line. */
const points = computed(() => {
    const values = props.values.length > 1 ? props.values : [0, 0];
    const min = Math.min(...values);
    const max = Math.max(...values);
    const range = max - min;
    const step = WIDTH / (values.length - 1);

    return values.map((value, index) => {
        const ratio = range === 0 ? 0.5 : (value - min) / range;
        const y = props.height - 2 - ratio * (props.height - 4);

        return [index * step, y] as const;
    });
});

const line = computed(() => points.value.map(([x, y]) => `${x},${y}`).join(' '));

const area = computed(() => `${line.value} ${WIDTH},${props.height} 0,${props.height}`);
</script>

<template>
    <!-- Decorative: the card states the figure and its change in text. -->
    <svg
        :viewBox="`0 0 ${WIDTH} ${height}`"
        preserveAspectRatio="none"
        class="w-full"
        :style="{ height: `${height}px` }"
        aria-hidden="true"
        focusable="false"
    >
        <polygon :points="area" :fill="colour" opacity="0.1" />
        <polyline
            :points="line"
            fill="none"
            :stroke="colour"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            vector-effect="non-scaling-stroke"
        />
    </svg>
</template>
