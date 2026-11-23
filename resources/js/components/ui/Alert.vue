<script setup lang="ts">
import { CircleAlert, CircleCheck } from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        variant?: 'error' | 'success';
        title?: string;
        message: string;
    }>(),
    {
        variant: 'error',
    },
);

const isError = computed(() => props.variant === 'error');
</script>

<template>
    <div
        role="alert"
        class="flex items-start gap-3 rounded-lg border p-3 text-sm"
        :class="
            isError
                ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200'
                : 'border-brand-200 bg-brand-50 text-brand-900 dark:border-brand-800 dark:bg-brand-950 dark:text-brand-100'
        "
    >
        <component :is="isError ? CircleAlert : CircleCheck" class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true" />

        <div class="grid gap-0.5">
            <p v-if="title" class="font-semibold">{{ title }}</p>
            <p>{{ message }}</p>
        </div>
    </div>
</template>
