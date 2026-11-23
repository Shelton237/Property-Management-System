<script setup lang="ts">
import { Check } from 'lucide-vue-next';

defineProps<{
    id: string;
    modelValue: boolean;
    label: string;
}>();

defineEmits<{ 'update:modelValue': [value: boolean] }>();
</script>

<template>
    <label :for="id" class="flex cursor-pointer items-center gap-3">
        <input
            :id="id"
            type="checkbox"
            class="peer sr-only"
            :checked="modelValue"
            @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
        />

        <span
            class="grid h-[22px] w-[22px] shrink-0 place-items-center rounded-md border-2 transition peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600 peer-focus-visible:ring-offset-2 dark:peer-focus-visible:ring-offset-slate-900"
            :class="
                modelValue
                    ? 'border-brand-800 bg-brand-800'
                    : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800'
            "
            aria-hidden="true"
        >
            <Check v-if="modelValue" class="h-3.5 w-3.5 text-white" :stroke-width="3.5" />
        </span>

        <span class="select-none text-[15px] text-slate-600 dark:text-slate-300">{{ label }}</span>
    </label>
</template>
