<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';

const { t, brandName } = useTranslations();
</script>

<template>
    <!--
        Official logo from the asset pack. It is a raster extraction from the
        mockup: a vector version would be sharper on high density screens.
        Its colours are fixed, so it sits on a white chip in dark mode.
    -->
    <img
        src="/images/pms-logo.png"
        :alt="`${brandName} — ${t('common.tagline')}`"
        class="h-12 w-auto dark:rounded-lg dark:bg-white dark:px-2 dark:py-1"
        width="295"
        height="90"
    />
</template>
