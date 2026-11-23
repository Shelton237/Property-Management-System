<script setup lang="ts">
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import Alert from '@/components/ui/Alert.vue';
import Button from '@/components/ui/Button.vue';
import Checkbox from '@/components/ui/Checkbox.vue';
import PasswordInput from '@/components/ui/PasswordInput.vue';
import TextInput from '@/components/ui/TextInput.vue';
import { useTranslations } from '@/composables/useTranslations';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const { t, brandName } = useTranslations();

const currentYear = new Date().getFullYear();

const features = ['planning', 'outlets', 'accounting', 'payments'] as const;

const stats = [
    { label: 'occupancy_label', value: 'occupancy_value' },
    { label: 'rate_label', value: 'rate_value' },
    { label: 'movements_label', value: 'movements_value' },
] as const;

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

    <div class="min-h-screen bg-white dark:bg-slate-950 lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
        <!-- Form column: the only one shown on small screens. -->
        <div class="flex min-h-screen flex-col px-6 py-8 sm:px-10 lg:px-14">
            <header class="flex items-center justify-between gap-4">
                <Link
                    :href="route('home')"
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
                        {{ t('login.title') }}
                    </h1>
                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400">
                        {{ t('login.subtitle') }}
                    </p>

                    <div v-if="status" class="mt-6">
                        <Alert variant="success" :message="status" />
                    </div>

                    <div v-if="form.errors.auth" class="mt-6">
                        <Alert variant="error" :title="t('login.error_title')" :message="form.errors.auth" />
                    </div>

                    <form class="mt-6 grid gap-5" novalidate @submit.prevent="submit">
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
                        >
                            <template #action>
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="rounded text-sm font-medium text-brand-800 underline-offset-4 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:text-brand-400 dark:focus-visible:ring-offset-slate-950"
                                >
                                    {{ t('login.forgot') }}
                                </Link>
                            </template>
                        </PasswordInput>

                        <Checkbox id="remember" v-model="form.remember" :label="t('login.remember')" />

                        <Button type="submit" :loading="form.processing" :loading-label="t('login.submitting')">
                            {{ t('login.submit') }}
                        </Button>
                    </form>

                    <div class="mt-8 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                            {{ t('login.trial_question') }}
                        </p>
                        <Link
                            :href="route('register')"
                            class="mt-0.5 inline-block rounded text-sm font-semibold text-brand-800 underline-offset-4 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:text-brand-400 dark:focus-visible:ring-offset-slate-950"
                        >
                            {{ t('login.trial_cta') }}
                        </Link>
                    </div>
                </div>
            </main>

            <footer class="text-xs text-slate-500 dark:text-slate-500">
                {{ t('common.footer', { year: currentYear }) }}
            </footer>
        </div>

        <!-- Presentation panel: desktop only, decorative. -->
        <aside
            class="relative hidden overflow-hidden bg-gradient-to-br from-brand-900 via-brand-800 to-brand-600 lg:flex lg:flex-col lg:justify-center lg:px-14 lg:py-16"
            aria-hidden="true"
        >
            <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-white/10 blur-2xl" />
            <div class="pointer-events-none absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-black/20 blur-2xl" />

            <div class="relative max-w-xl">
                <p class="text-3xl font-semibold leading-tight tracking-tight text-white xl:text-4xl">
                    {{ t('login.panel.headline') }}
                </p>

                <dl class="mt-10 grid grid-cols-3 gap-3">
                    <div v-for="stat in stats" :key="stat.label" class="rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm">
                        <dd class="text-xl font-semibold text-white xl:text-2xl">
                            {{ t(`login.panel.stats.${stat.value}`) }}
                        </dd>
                        <dt class="mt-1 text-xs font-medium leading-snug text-brand-50/80">
                            {{ t(`login.panel.stats.${stat.label}`) }}
                        </dt>
                    </div>
                </dl>

                <p class="mt-2.5 text-[11px] text-brand-50/60">
                    {{ t('login.panel.figures_note') }}
                </p>

                <ul class="mt-10 grid gap-3.5">
                    <li v-for="feature in features" :key="feature" class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20">
                            <Check class="h-3 w-3 text-white" />
                        </span>
                        <span class="text-sm text-brand-50">
                            {{ t(`login.panel.features.${feature}`) }}
                        </span>
                    </li>
                </ul>
            </div>
        </aside>
    </div>
</template>
