<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { t } = useTranslations();

const page = usePage();

const userName = computed(() => (page.props.auth as { user?: { name?: string } } | undefined)?.user?.name ?? '');

const hotel = computed(() => page.props.hotel as { name: string; city: string | null } | null);
</script>

<template>
    <Head :title="t('dashboard.page_title')" />

    <BackofficeLayout>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">
            {{ t('dashboard.welcome', { name: userName }) }}
        </h1>
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
            {{ hotel ? hotel.name : t('dashboard.no_hotel') }}
        </p>
    </BackofficeLayout>
</template>
