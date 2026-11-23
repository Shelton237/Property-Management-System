<script setup lang="ts">
import LocaleFlag from '@/components/LocaleFlag.vue';
import { useTranslations } from '@/composables/useTranslations';
import { Link } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';
import { onBeforeUnmount, onMounted, ref } from 'vue';

const { t, locale, locales } = useTranslations();

const open = ref(false);
const container = ref<HTMLElement | null>(null);

function close(): void {
    open.value = false;
}

function onDocumentClick(event: MouseEvent): void {
    if (container.value && !container.value.contains(event.target as Node)) {
        close();
    }
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') {
        close();
    }
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <div ref="container" class="relative">
        <button
            type="button"
            :aria-label="t('common.language')"
            :aria-expanded="open"
            aria-haspopup="menu"
            class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 transition hover:border-slate-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 dark:border-slate-700 dark:bg-slate-900 dark:focus-visible:ring-offset-slate-950"
            @click="open = !open"
        >
            <LocaleFlag :code="locale" class="h-[22px] w-[22px]" />
            <span class="text-sm font-semibold uppercase text-brand-800 dark:text-slate-100">{{ locale }}</span>
            <ChevronDown class="h-4 w-4 text-slate-400" aria-hidden="true" />
        </button>

        <div
            v-if="open"
            role="menu"
            class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-900"
        >
            <Link
                v-for="code in locales"
                :key="code"
                :href="route('locale.update', { locale: code })"
                role="menuitem"
                :aria-current="code === locale ? 'true' : undefined"
                class="flex items-center gap-2.5 px-3 py-2 text-sm transition hover:bg-slate-50 focus:outline-none focus-visible:bg-slate-50 dark:hover:bg-slate-800"
                :class="code === locale ? 'font-semibold text-brand-800 dark:text-white' : 'text-slate-600 dark:text-slate-300'"
                @click="close"
            >
                <LocaleFlag :code="code" class="h-[18px] w-[18px]" />
                {{ t(`common.language_${code}_name`) }}
            </Link>
        </div>
    </div>
</template>
