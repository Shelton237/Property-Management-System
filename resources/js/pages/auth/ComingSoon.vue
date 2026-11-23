<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import { Head, Link } from '@inertiajs/vue3';

const { t, brandName } = useTranslations();
</script>

<template>
    <Head :title="t('common.coming_soon_title')" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-white px-6 dark:bg-slate-950">
        <div class="w-full max-w-[400px] text-center">
            <div class="flex items-center justify-center gap-2">
                <span class="h-2.5 w-2.5 rounded-sm bg-brand-800 dark:bg-brand-500" aria-hidden="true" />
                <span class="text-base font-semibold tracking-tight text-slate-900 dark:text-slate-50">
                    {{ brandName }}
                </span>
            </div>

            <h1 class="mt-8 text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">
                {{ t('common.coming_soon_title') }}
            </h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                {{ t('common.coming_soon_body') }}
            </p>

            <Link
                :href="route('login')"
                class="mt-8 inline-block rounded text-sm font-semibold text-brand-800 underline-offset-4 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:text-brand-400 dark:focus-visible:ring-offset-slate-950"
            >
                {{ t('common.back_to_login') }}
            </Link>
        </div>
    </div>
</template>
