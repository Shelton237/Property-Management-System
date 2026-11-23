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
import { ArrowRight, BedDouble, CalendarDays, Check, Headset, Lock, LogIn, Mail, ReceiptText } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const { t } = useTranslations();

const currentYear = new Date().getFullYear();

const stats = [
    { icon: BedDouble, value: 'occupancy_value', label: 'occupancy_label' },
    { icon: ReceiptText, value: 'rate_value', label: 'rate_label' },
    { icon: CalendarDays, value: 'movements_value', label: 'movements_label' },
] as const;

const features = ['planning', 'outlets', 'accounting', 'payments'] as const;

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

    <div class="relative min-h-screen overflow-hidden bg-page dark:bg-slate-950">
        <!-- Decorative corner curves, desktop only. -->
        <div
            class="pointer-events-none absolute -left-[88px] -top-[88px] hidden h-[176px] w-[176px] rounded-full bg-brand-800 lg:block"
            aria-hidden="true"
        />
        <div
            class="pointer-events-none absolute -left-[124px] -top-[124px] hidden h-[248px] w-[248px] rounded-full border-[6px] border-gold-500 lg:block"
            aria-hidden="true"
        />
        <div
            class="pointer-events-none absolute -bottom-[132px] -left-[132px] hidden h-[264px] w-[264px] rounded-full border-[6px] border-gold-500 lg:block"
            aria-hidden="true"
        />

        <div class="relative z-10 min-h-screen lg:grid lg:grid-cols-[44fr_56fr]">
            <!-- Form column: the only one shown on small screens. -->
            <div class="flex min-h-screen flex-col px-4 py-5 sm:px-7 sm:py-7">
                <div
                    class="mx-auto flex w-full max-w-[740px] flex-1 flex-col justify-center rounded-[28px] bg-white px-6 py-9 shadow-[0_30px_80px_-45px_rgba(8,43,85,0.4)] dark:bg-slate-900 sm:px-14 sm:py-12"
                >
                    <header class="flex items-center justify-between gap-4">
                        <BrandLogo />
                        <LanguageSwitcher />
                    </header>

                    <h1 class="mt-11 text-[42px] font-extrabold leading-none tracking-tight text-brand-800 dark:text-white sm:text-[50px]">
                        {{ t('login.title') }}
                    </h1>
                    <span class="mt-5 block h-1 w-[90px] rounded-full bg-gold-500" aria-hidden="true" />
                    <p class="mt-4 text-base text-ink dark:text-slate-400">
                        {{ t('login.subtitle') }}
                    </p>

                    <div v-if="status" class="mt-6">
                        <Alert variant="success" :message="status" />
                    </div>

                    <div v-if="form.errors.auth" class="mt-6">
                        <Alert variant="error" :title="t('login.error_title')" :message="form.errors.auth" />
                    </div>

                    <form class="mt-8 grid gap-6" novalidate @submit.prevent="submit">
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            autofocus
                            required
                            :icon="Mail"
                            :label="t('login.email_label')"
                            :placeholder="t('login.email_placeholder')"
                            :error="form.errors.email"
                        />

                        <PasswordInput
                            id="password"
                            v-model="form.password"
                            autocomplete="current-password"
                            required
                            :icon="Lock"
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
                                    class="rounded text-[15px] font-medium text-brand-700 underline-offset-4 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:text-brand-300 dark:focus-visible:ring-offset-slate-900"
                                >
                                    {{ t('login.forgot') }}
                                </Link>
                            </template>
                        </PasswordInput>

                        <Checkbox id="remember" v-model="form.remember" :label="t('login.remember')" />

                        <Button type="submit" class="mt-1" :loading="form.processing" :loading-label="t('login.submitting')">
                            <template #icon>
                                <LogIn class="h-[18px] w-[18px]" aria-hidden="true" />
                            </template>
                            {{ t('login.submit') }}
                        </Button>
                    </form>

                    <div class="mt-8 flex items-center gap-4 rounded-2xl bg-field px-5 py-4 dark:bg-slate-800">
                        <span
                            class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white text-brand-800 dark:bg-slate-900 dark:text-slate-100"
                            aria-hidden="true"
                        >
                            <Headset class="h-5 w-5" />
                        </span>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-brand-800 dark:text-slate-100">
                                {{ t('login.trial_question') }}
                            </p>
                            <Link
                                :href="route('register')"
                                class="mt-0.5 inline-flex items-center gap-1.5 rounded text-sm font-semibold text-brand-700 underline underline-offset-4 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:text-brand-300 dark:focus-visible:ring-offset-slate-800"
                            >
                                {{ t('login.trial_cta') }}
                                <ArrowRight class="h-4 w-4" aria-hidden="true" />
                            </Link>
                        </div>
                    </div>
                </div>

                <footer class="pt-5 text-center text-xs text-ink/70 dark:text-slate-500">
                    {{ t('common.footer', { year: currentYear }) }}
                </footer>
            </div>

            <!-- Presentation panel: desktop only, decorative. -->
            <aside class="relative hidden overflow-hidden lg:block" aria-hidden="true">
                <div class="absolute inset-0 bg-brand-900" />
                <!--
                    The photograph only covers the right hand side: the file in
                    the asset pack is a narrow crop, so stretching it across the
                    whole panel would show how soft it is.
                -->
                <div class="absolute inset-y-0 right-0 w-[46%] bg-[url('/images/login-cover.jpg')] bg-cover bg-center" />
                <div class="absolute inset-0 bg-gradient-to-r from-brand-950 via-brand-950/95 to-brand-900/55" />

                <!-- Curved boundary with the form column. -->
                <svg
                    class="absolute inset-y-0 -left-px z-10 h-full w-[80px] text-page dark:text-slate-950"
                    viewBox="0 0 80 1000"
                    preserveAspectRatio="none"
                    fill="currentColor"
                >
                    <path d="M80 0 H0 V1000 H80 C44 800 44 200 80 0 Z" />
                </svg>

                <!-- Gold curves, bottom right. -->
                <div
                    class="pointer-events-none absolute -bottom-[150px] -right-[90px] h-[300px] w-[300px] rounded-full border-[6px] border-gold-500/90"
                />
                <div
                    class="pointer-events-none absolute -bottom-[96px] -right-[170px] h-[264px] w-[264px] rounded-full border-[6px] border-gold-500/55"
                />

                <div class="relative z-20 flex h-full flex-col justify-center px-16 py-14 2xl:px-20">
                    <h2 class="max-w-[820px] text-[40px] font-extrabold leading-[1.1] tracking-tight text-white 2xl:text-[48px]">
                        {{ t('login.panel.headline_before') }}<br />
                        <span class="text-gold-400">{{ t('login.panel.headline_highlight') }}</span
                        ><br />
                        {{ t('login.panel.headline_after') }}
                    </h2>

                    <span class="mt-8 block h-1 w-[110px] rounded-full bg-gold-500" />

                    <p class="mt-7 max-w-[470px] text-[15px] leading-relaxed text-white/75">
                        {{ t('login.panel.intro') }}
                    </p>

                    <dl class="mt-10 grid max-w-[720px] grid-cols-3 gap-5">
                        <div
                            v-for="stat in stats"
                            :key="stat.label"
                            class="rounded-2xl border border-white/15 bg-white/10 px-5 py-5 backdrop-blur-sm"
                        >
                            <component :is="stat.icon" class="h-6 w-6 text-gold-400" :stroke-width="1.8" />
                            <dd class="mt-5 text-[26px] font-extrabold leading-tight text-white 2xl:text-[30px]">
                                {{ t(`login.panel.stats.${stat.value}`) }}
                            </dd>
                            <dt class="mt-1.5 text-[13px] leading-snug text-white/70">
                                {{ t(`login.panel.stats.${stat.label}`) }}
                            </dt>
                        </div>
                    </dl>

                    <ul class="mt-10 grid gap-4">
                        <li v-for="feature in features" :key="feature" class="flex items-center gap-3.5">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-gold-500">
                                <Check class="h-3.5 w-3.5 text-white" :stroke-width="3.5" />
                            </span>
                            <span class="text-[15px] text-white/90">
                                {{ t(`login.panel.features.${feature}`) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</template>
