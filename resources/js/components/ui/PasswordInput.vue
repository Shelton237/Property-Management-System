<script setup lang="ts">
import { Eye, EyeOff } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { Component } from 'vue';

const props = withDefaults(
    defineProps<{
        id: string;
        modelValue: string;
        label: string;
        showLabel: string;
        hideLabel: string;
        icon?: Component;
        placeholder?: string;
        autocomplete?: string;
        required?: boolean;
        error?: string;
    }>(),
    {
        required: false,
    },
);

defineEmits<{ 'update:modelValue': [value: string] }>();

const visible = ref(false);

const errorId = computed(() => `${props.id}-error`);
const toggleLabel = computed(() => (visible.value ? props.hideLabel : props.showLabel));
</script>

<template>
    <div class="grid gap-1.5">
        <div class="flex items-baseline justify-between gap-3">
            <label :for="id" class="flex items-center gap-2 text-[13px] text-slate-600 dark:text-slate-300">
                <component :is="icon" v-if="icon" class="h-4 w-4 shrink-0" aria-hidden="true" />
                {{ label }}
            </label>
            <slot name="action" />
        </div>

        <div class="relative">
            <input
                :id="id"
                :type="visible ? 'text' : 'password'"
                :value="modelValue"
                :placeholder="placeholder"
                :autocomplete="autocomplete"
                :required="required"
                :aria-invalid="error ? 'true' : undefined"
                :aria-describedby="error ? errorId : undefined"
                class="h-11 w-full rounded-lg border bg-white pl-3.5 pr-12 text-[15px] text-slate-900 transition placeholder:text-slate-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:bg-slate-800 dark:text-slate-50 dark:placeholder:text-slate-400 dark:focus-visible:ring-offset-slate-900"
                :class="
                    error
                        ? 'border-red-400 dark:border-red-500'
                        : 'border-slate-200 hover:border-slate-300 dark:border-slate-700 dark:hover:border-slate-600'
                "
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            />

            <button
                type="button"
                :aria-label="toggleLabel"
                :aria-pressed="visible"
                class="absolute inset-y-0 right-0 flex w-11 cursor-pointer items-center justify-center rounded-r-lg text-slate-500 transition-colors duration-200 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:hover:text-slate-100"
            @click="visible = !visible"
            >
                <EyeOff v-if="visible" class="h-[18px] w-[18px]" aria-hidden="true" />
                <Eye v-else class="h-[18px] w-[18px]" aria-hidden="true" />
            </button>
        </div>

        <p v-if="error" :id="errorId" class="text-[13px] font-medium text-red-600 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>
