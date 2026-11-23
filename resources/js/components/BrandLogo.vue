<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';

withDefaults(defineProps<{ tagline?: boolean }>(), { tagline: true });

const { t, brandName } = useTranslations();
</script>

<template>
    <div class="flex items-center gap-3.5">
        <svg viewBox="0 0 48 42" class="h-12 w-[54px] shrink-0" role="img" :aria-label="brandName">
            <!-- Roof, in the accent colour. -->
            <path d="M7 17 L24 4 L41 17" fill="none" stroke="#f0a11a" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round" />
            <!-- Headboard, pillow, mattress and legs. -->
            <path d="M9 36 V21" fill="none" stroke="#16295b" stroke-width="3.4" stroke-linecap="round" />
            <rect x="13" y="22" width="10" height="7" rx="2.2" fill="#16295b" />
            <rect x="9" y="29" width="32" height="7" rx="2.6" fill="#f0a11a" />
            <path d="M11 36 V39 M39 36 V39" fill="none" stroke="#16295b" stroke-width="3" stroke-linecap="round" />
        </svg>

        <div class="leading-none">
            <p class="text-[36px] font-extrabold tracking-tight text-brand-800 dark:text-white">
                {{ brandName }}
            </p>
            <p v-if="tagline" class="mt-1.5 text-[13px] font-medium text-slate-500 dark:text-slate-400">
                {{ t('common.tagline') }}
            </p>
        </div>
    </div>
</template>
