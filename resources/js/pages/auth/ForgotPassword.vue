<script setup lang="ts">
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import Alert from '@/components/ui/Alert.vue';
import Button from '@/components/ui/Button.vue';
import TextInput from '@/components/ui/TextInput.vue';
import { useTranslations } from '@/composables/useTranslations';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const { t, brandName } = useTranslations();

const currentYear = new Date().getFullYear();

const form = useForm({
    email: '',
});

const submit = () => form.post(route('password.email'));
</script>

<template>
    <Head :title="t('password_reset.request_page_title')" />

    <div class="flex min-h-screen flex-col bg-white px-6 py-8 dark:bg-slate-950 sm:px-10">
        <header class="flex items-center justify-between gap-4">
            <Link
                :href="route('login')"
                class="flex items-center gap-2 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950"
            >
                <span class="h-2.5 w-2.5 rounded-sm bg-brand-800 dark:bg-brand-500" aria-hidden="true" />
                <span class="text-base font-semibold tracking-tight text-slate-900 dark:text-slate-50">
                    {{ brandName }}
                </span>
            </Link>

            <LanguageSwitcher />
        </header>

        <main class="flex flex-1 items-center justify-center py-10">
            <div class="w-full max-w-[400px]">
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-50">
                    {{ t('password_reset.request_title') }}
                </h1>
                <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
                    {{ t('password_reset.request_subtitle') }}
                </p>

                <div v-if="status" class="mt-6">
                    <Alert variant="success" :message="status" />
                </div>

                <form class="mt-6 grid gap-5" novalidate @submit.prevent="submit">
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        autofocus
                        required
                        :label="t('password_reset.email_label')"
                        :placeholder="t('password_reset.email_placeholder')"
                        :error="form.errors.email"
                    />

                    <Button type="submit" :loading="form.processing" :loading-label="t('password_reset.sending')">
                        {{ t('password_reset.send') }}
                    </Button>
                </form>

                <Link
                    :href="route('login')"
                    class="mt-6 inline-block rounded text-sm font-medium text-brand-800 underline-offset-4 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:text-brand-400 dark:focus-visible:ring-offset-slate-950"
                >
                    {{ t('password_reset.back_to_login') }}
                </Link>
            </div>
        </main>

        <footer class="text-xs text-slate-500 dark:text-slate-500">
            {{ t('common.footer', { year: currentYear }) }}
        </footer>
    </div>
</template>
