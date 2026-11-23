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
    <!-- The whole row is the target, so the 18px box is not the touch area. -->
    <label :for="id" class="flex min-h-[44px] cursor-pointer items-center gap-2.5">
        <input
            :id="id"
            type="checkbox"
            class="peer sr-only"
            :checked="modelValue"
            @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
        />

        <span
            class="grid h-[18px] w-[18px] shrink-0 place-items-center rounded border transition peer-focus-visible:ring-2 peer-focus-visible:ring-brand-600 peer-focus-visible:ring-offset-2 dark:peer-focus-visible:ring-offset-slate-900"
            :class="
                modelValue
                    ? 'border-brand-800 bg-brand-800'
                    : 'border-slate-300 bg-white dark:border-slate-600 dark:bg-slate-800'
            "
            aria-hidden="true"
        >
            <!-- White on navy is 14:1. The gold pill used before was 1.94:1. -->
            <Check v-if="modelValue" class="h-3 w-3 text-white" :stroke-width="3.5" />
        </span>

        <span class="select-none text-[13px] text-slate-600 dark:text-slate-300">{{ label }}</span>
    </label>
</template>
