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
    <div class="grid gap-2">
        <div class="flex items-baseline justify-between gap-3">
            <label :for="id" class="flex items-center gap-2.5 text-[15px] font-semibold text-brand-800 dark:text-slate-100">
                <component :is="icon" v-if="icon" class="h-[18px] w-[18px] shrink-0" aria-hidden="true" />
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
                class="w-full rounded-xl border bg-slate-100/80 py-3.5 pl-4 pr-12 text-[15px] text-brand-900 transition placeholder:text-slate-400 focus:bg-white focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:bg-slate-800 dark:text-slate-50 dark:placeholder:text-slate-500 dark:focus-visible:ring-offset-slate-900"
                :class="
                    error
                        ? 'border-red-400 dark:border-red-500'
                        : 'border-transparent hover:border-slate-200 dark:hover:border-slate-700'
                "
                @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            />

            <button
                type="button"
                :aria-label="toggleLabel"
                :aria-pressed="visible"
                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-slate-400 transition hover:text-brand-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:hover:text-slate-100"
                @click="visible = !visible"
            >
                <EyeOff v-if="visible" class="h-[18px] w-[18px]" aria-hidden="true" />
                <Eye v-else class="h-[18px] w-[18px]" aria-hidden="true" />
            </button>
        </div>

        <p v-if="error" :id="errorId" class="text-sm font-medium text-red-600 dark:text-red-400">
            {{ error }}
        </p>
    </div>
</template>
