<script setup lang="ts">
defineProps<{
    id: string;
    modelValue: boolean;
    label: string;
}>();

defineEmits<{ 'update:modelValue': [value: boolean] }>();
</script>

<template>
    <div class="flex items-center gap-2.5">
        <input
            :id="id"
            type="checkbox"
            :checked="modelValue"
            class="h-4 w-4 rounded border-slate-300 text-brand-700 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:border-slate-600 dark:bg-slate-900 dark:focus-visible:ring-offset-slate-950"
            @change="$emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
        />

        <label :for="id" class="select-none text-sm text-slate-600 dark:text-slate-300">
            {{ label }}
        </label>
    </div>
</template>
