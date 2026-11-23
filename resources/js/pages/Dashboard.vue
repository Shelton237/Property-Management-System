<script setup lang="ts">
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import Button from '@/components/ui/Button.vue';
import { useTranslations } from '@/composables/useTranslations';
import { Head, useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    hotel: { name: string; city: string | null } | null;
}>();

const { t, brandName } = useTranslations();

const page = usePage();
const userName = (page.props.auth as { user?: { name?: string } } | undefined)?.user?.name ?? '';

const logoutForm = useForm({});

const logout = () => logoutForm.post(route('logout'));
</script>

<template>
    <Head :title="t('dashboard.page_title')" />

    <div class="min-h-screen bg-white dark:bg-slate-950">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-6 py-4 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-sm bg-brand-800 dark:bg-brand-500" aria-hidden="true" />
                <span class="text-base font-semibold tracking-tight text-slate-900 dark:text-slate-50">
                    {{ brandName }}
                </span>
            </div>

            <div class="flex items-center gap-4">
                <LanguageSwitcher />

                <form @submit.prevent="logout">
                    <Button type="submit" variant="secondary" :loading="logoutForm.processing" class="w-auto">
                        {{ t('dashboard.logout') }}
                    </Button>
                </form>
            </div>
        </header>

        <main class="px-6 py-12">
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">
                {{ t('dashboard.welcome', { name: userName }) }}
            </h1>
            <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
                {{ hotel ? hotel.name : t('dashboard.no_hotel') }}
            </p>
        </main>
    </div>
</template>
