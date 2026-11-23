<script setup lang="ts">
import { LoaderCircle } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        type?: 'button' | 'submit';
        variant?: 'primary' | 'secondary';
        loading?: boolean;
        disabled?: boolean;
        loadingLabel?: string;
    }>(),
    {
        type: 'button',
        variant: 'primary',
        loading: false,
        disabled: false,
    },
);
</script>

<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :aria-busy="loading"
        class="inline-flex min-h-[48px] w-full cursor-pointer items-center justify-center gap-2.5 rounded-lg px-4 py-3 text-[15px] font-semibold transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 dark:focus-visible:ring-offset-slate-900"
        :class="
            variant === 'primary'
                ? 'bg-brand-800 text-white hover:bg-brand-900 focus-visible:ring-brand-600'
                : 'border border-slate-300 bg-white text-brand-800 hover:bg-slate-50 focus-visible:ring-brand-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800'
        "
    >
        <LoaderCircle v-if="loading" class="h-[18px] w-[18px] animate-spin" aria-hidden="true" />
        <slot v-else name="icon" />

        <span v-if="loading && loadingLabel">{{ loadingLabel }}</span>
        <span v-else><slot /></span>
    </button>
</template>
