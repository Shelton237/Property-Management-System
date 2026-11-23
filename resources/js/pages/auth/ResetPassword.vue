<script setup lang="ts">
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import Button from '@/components/ui/Button.vue';
import PasswordInput from '@/components/ui/PasswordInput.vue';
import TextInput from '@/components/ui/TextInput.vue';
import { useTranslations } from '@/composables/useTranslations';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const { t, brandName } = useTranslations();

const currentYear = new Date().getFullYear();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="t('password_reset.reset_page_title')" />

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
                    {{ t('password_reset.reset_title') }}
                </h1>
                <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
                    {{ t('password_reset.reset_subtitle') }}
                </p>

                <form class="mt-6 grid gap-5" novalidate @submit.prevent="submit">
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        :label="t('password_reset.email_label')"
                        :error="form.errors.email"
                    />

                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        autocomplete="new-password"
                        required
                        :label="t('password_reset.password_label')"
                        :show-label="t('password_reset.password_show')"
                        :hide-label="t('password_reset.password_hide')"
                        :error="form.errors.password"
                    />

                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                        required
                        :label="t('password_reset.password_confirmation_label')"
                        :show-label="t('password_reset.password_show')"
                        :hide-label="t('password_reset.password_hide')"
                        :error="form.errors.password_confirmation"
                    />

                    <Button type="submit" :loading="form.processing" :loading-label="t('password_reset.saving')">
                        {{ t('password_reset.save') }}
                    </Button>
                </form>
            </div>
        </main>

        <footer class="text-xs text-slate-500 dark:text-slate-500">
            {{ t('common.footer', { year: currentYear }) }}
        </footer>
    </div>
</template>
