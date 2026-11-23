<script setup lang="ts">
import Sparkline from '@/charts/Sparkline.vue';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import { TrendingDown, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    stat: {
        key: string;
        value: number;
        secondaryValue?: number;
        format: string;
        delta: number | null;
        deltaFormat?: string;
        deltaBasis?: string;
        spark: number[];
    };
}>();

const { t } = useTranslations();
const { number, compactMoney, percent } = useFormatters();

const display = computed(() => {
    switch (props.stat.format) {
        case 'percent':
            return percent(props.stat.value, 0);
        case 'money':
            return compactMoney(props.stat.value);
        case 'pair':
            return `${number(props.stat.value)} / ${number(props.stat.secondaryValue ?? 0)}`;
        default:
            return number(props.stat.value);
    }
});

const caption = computed(() => {
    if (props.stat.key === 'movements') {
        return t('dashboard.kpi.movements_caption', {
            in: props.stat.value,
            out: props.stat.secondaryValue ?? 0,
        });
    }

    if (props.stat.key === 'guests_in_house') {
        return t('dashboard.kpi.rooms_caption', { count: props.stat.secondaryValue ?? 0 });
    }

    return null;
});

const improving = computed(() => (props.stat.delta ?? 0) >= 0);
</script>

<template>
    <article class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ t(`dashboard.kpi.${stat.key}`) }}</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums tracking-tight text-slate-900 dark:text-slate-50">
                {{ display }}
            </p>

            <p v-if="stat.delta !== null" class="mt-1 flex items-center gap-1.5 text-[11px]">
                <component
                    :is="improving ? TrendingUp : TrendingDown"
                    class="h-3.5 w-3.5"
                    :class="improving ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'"
                    aria-hidden="true"
                />
                <span
                    class="font-medium tabular-nums"
                    :class="improving ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400'"
                >
                    {{ improving ? '+' : '' }}{{ number(stat.delta, 1) }}{{ stat.deltaFormat === 'points' ? ' pts' : ' %' }}
                </span>
                <span class="text-slate-400">{{ t(`dashboard.kpi.basis_${stat.deltaBasis}`) }}</span>
            </p>

            <p v-else-if="caption" class="mt-1 text-[11px] text-slate-400">{{ caption }}</p>
        </div>

        <Sparkline :values="stat.spark" />
    </article>
</template>
