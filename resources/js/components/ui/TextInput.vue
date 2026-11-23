<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';

const props = withDefaults(
    defineProps<{
        id: string;
        modelValue: string;
        label: string;
        icon?: Component;
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
    <div class="grid gap-2">
        <div class="flex items-baseline justify-between gap-3">
            <label :for="id" class="flex items-center gap-2.5 text-[15px] font-semibold text-brand-800 dark:text-slate-100">
                <component :is="icon" v-if="icon" class="h-[18px] w-[18px] shrink-0" aria-hidden="true" />
                {{ label }}
            </label>
            <slot name="action" />
        </div>

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
            class="w-full rounded-xl border bg-slate-100/80 px-4 py-3.5 text-[15px] text-brand-900 transition placeholder:text-slate-400 focus:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:bg-slate-800 dark:text-slate-50 dark:placeholder:text-slate-500 dark:focus-visible:ring-offset-slate-900"
            :class="
                error
                    ? 'border-red-400 dark:border-red-500'
                    : 'border-transparent hover:border-slate-200 dark:hover:border-slate-700'
            "
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />

        <p v-if="error" :id="errorId" class="text-sm font-medium text-red-600 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>
