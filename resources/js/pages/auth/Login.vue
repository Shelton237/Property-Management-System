<script setup lang="ts">
import BrandLogo from '@/components/BrandLogo.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import Alert from '@/components/ui/Alert.vue';
import Button from '@/components/ui/Button.vue';
import Checkbox from '@/components/ui/Checkbox.vue';
import PasswordInput from '@/components/ui/PasswordInput.vue';
import TextInput from '@/components/ui/TextInput.vue';
import { useTranslations } from '@/composables/useTranslations';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const { t } = useTranslations();

const currentYear = new Date().getFullYear();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="t('login.page_title')" />

    <div class="relative min-h-screen overflow-hidden bg-slate-200 dark:bg-slate-950">
        <!--
            Full bleed photograph. The file available today is a narrow, low
            resolution crop, so it is softened on purpose and sits under a light
            wash: a deliberate backdrop rather than a visibly stretched image.
            With a wide photograph, drop `blur-[2px] scale-105` and the wash
            opacity goes back down.
        -->
        <div class="absolute inset-0 scale-105 bg-[url('/images/login-cover.jpg')] bg-cover bg-center blur-[2px]" aria-hidden="true" />
        <div
            class="absolute inset-0 bg-gradient-to-r from-white/90 via-white/60 to-white/25 dark:from-slate-950/90 dark:via-slate-950/75 dark:to-slate-950/50"
            aria-hidden="true"
        />

        <!-- Logo and language, laid over the photograph. -->
        <div class="relative z-20 flex items-center justify-between gap-4 px-6 py-6 sm:px-10">
            <BrandLogo class="drop-shadow-sm" />
            <LanguageSwitcher />
        </div>

        <div class="relative z-10 flex min-h-[calc(100vh-104px)] items-center px-6 pb-10 sm:px-10 lg:px-[8%]">
            <div class="w-full max-w-[400px]">
                <div class="rounded-2xl bg-white p-7 shadow-[0_24px_60px_-30px_rgba(15,23,42,0.45)] dark:bg-slate-900 sm:p-8">
                    <h1 class="text-center text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ t('login.title') }}
                    </h1>
                    <p class="mt-1.5 text-center text-[13px] text-slate-500 dark:text-slate-400">
                        {{ t('login.subtitle') }}
                    </p>

                    <div v-if="status" class="mt-5">
                        <Alert variant="success" :message="status" />
                    </div>

                    <div v-if="form.errors.auth" class="mt-5">
                        <Alert variant="error" :title="t('login.error_title')" :message="form.errors.auth" />
                    </div>

                    <form class="mt-6 grid gap-4" novalidate @submit.prevent="submit">
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            autofocus
                            required
                            :label="t('login.email_label')"
                            :placeholder="t('login.email_placeholder')"
                            :error="form.errors.email"
                        />

                        <PasswordInput
                            id="password"
                            v-model="form.password"
                            autocomplete="current-password"
                            required
                            :label="t('login.password_label')"
                            :placeholder="t('login.password_placeholder')"
                            :show-label="t('login.password_show')"
                            :hide-label="t('login.password_hide')"
                            :error="form.errors.password"
                        />

                        <div class="flex items-center justify-between gap-3">
                            <Checkbox id="remember" v-model="form.remember" :label="t('login.remember')" />

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="rounded text-[13px] text-slate-600 underline underline-offset-4 transition-colors duration-200 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:text-slate-300 dark:hover:text-white dark:focus-visible:ring-offset-slate-900"
                            >
                                {{ t('login.forgot') }}
                            </Link>
                        </div>

                        <Button type="submit" :loading="form.processing" :loading-label="t('login.submitting')">
                            {{ t('login.submit') }}
                        </Button>
                    </form>
                </div>

                <p class="mt-5 text-center text-[13px] text-slate-700 dark:text-slate-300">
                    {{ t('login.trial_question') }}
                    <Link
                        :href="route('register')"
                        class="rounded font-semibold text-slate-900 underline underline-offset-4 transition-colors duration-200 hover:text-brand-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:text-white dark:focus-visible:ring-offset-slate-900"
                    >
                        {{ t('login.trial_cta') }}
                    </Link>
                </p>

                <p class="mt-8 text-center text-xs text-slate-600 dark:text-slate-400">
                    {{ t('common.footer', { year: currentYear }) }}
                </p>
            </div>
        </div>
    </div>
</template>
