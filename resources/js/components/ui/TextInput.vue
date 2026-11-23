<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        id: string;
        modelValue: string;
        label: string;
        type?: string;
        placeholder?: string;
        autocomplete?: string;
        required?: boolean;
        autofocus?: boolean;
        error?: string;
    }>(),
    {
        type: 'text',
        required: false,
        autofocus: false,
    },
);

defineEmits<{ 'update:modelValue': [value: string] }>();

const errorId = computed(() => `${props.id}-error`);
</script>

<template>
    <div class="grid gap-1.5">
        <label :for="id" class="text-sm font-medium text-slate-700 dark:text-slate-200">
            {{ label }}
        </label>

        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            :required="required"
            :autofocus="autofocus"
            :aria-invalid="error ? 'true' : undefined"
            :aria-describedby="error ? errorId : undefined"
            class="w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:bg-slate-900 dark:text-slate-50 dark:placeholder:text-slate-500 dark:focus-visible:ring-offset-slate-950"
            :class="
                error
                    ? 'border-red-500 dark:border-red-500'
                    : 'border-slate-300 hover:border-slate-400 dark:border-slate-700 dark:hover:border-slate-600'
            "
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />

        <p v-if="error" :id="errorId" class="text-sm font-medium text-red-600 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>
