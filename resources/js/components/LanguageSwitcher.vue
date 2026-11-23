<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import { Link } from '@inertiajs/vue3';

const { t, locale, locales } = useTranslations();
</script>

<template>
    <nav :aria-label="t('common.language')" class="flex items-center gap-1 text-xs font-semibold">
        <Link
            v-for="code in locales"
            :key="code"
            :href="route('locale.update', { locale: code })"
            :aria-label="t(`common.language_${code}_full`)"
            :aria-current="code === locale ? 'true' : undefined"
            class="rounded-md px-2 py-1 uppercase transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950"
            :class="
                code === locale
                    ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'
                    : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100'
            "
        >
            {{ t(`common.language_${code}`) }}
        </Link>
    </nav>
</template>
