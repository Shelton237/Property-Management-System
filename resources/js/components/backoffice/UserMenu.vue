<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ChevronDown, LogOut, User } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const { t } = useTranslations();

const page = usePage();

const user = computed(() => page.props.auth as { user?: { name?: string; role?: string } } | undefined);

const name = computed(() => user.value?.user?.name ?? '');
const role = computed(() => user.value?.user?.role ?? '');

const roleLabel = computed(() => (role.value ? t(`navigation.roles.${role.value}`) : ''));

const initials = computed(() =>
    name.value
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join(''),
);

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

const logoutForm = useForm({});

const logout = () => logoutForm.post(route('logout'));
</script>

<template>
    <div ref="container" class="relative">
        <button
            type="button"
            :aria-label="t('navigation.header.open_user_menu')"
            :aria-expanded="open"
            aria-haspopup="menu"
            class="flex min-h-[44px] cursor-pointer items-center gap-2 rounded-lg px-1.5 py-1 transition-colors duration-200 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:hover:bg-slate-800"
            @click="open = !open"
        >
            <span
                class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-200 text-xs font-semibold text-slate-700 dark:bg-slate-700 dark:text-slate-100"
                aria-hidden="true"
            >
                {{ initials }}
            </span>

            <span class="hidden min-w-0 text-left sm:block">
                <span class="block truncate text-sm font-medium text-slate-900 dark:text-slate-50">{{ name }}</span>
                <span class="block truncate text-xs text-slate-500 dark:text-slate-400">{{ roleLabel }}</span>
            </span>

            <ChevronDown class="h-4 w-4 shrink-0 text-slate-400" aria-hidden="true" />
        </button>

        <div
            v-if="open"
            role="menu"
            class="absolute right-0 z-50 mt-2 w-52 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-800 dark:bg-slate-900"
        >
            <Link
                :href="route('profile.edit')"
                role="menuitem"
                class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-100 focus:outline-none focus-visible:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                @click="close"
            >
                <User class="h-4 w-4" aria-hidden="true" />
                {{ t('navigation.header.profile') }}
            </Link>

            <form @submit.prevent="logout">
                <button
                    type="submit"
                    role="menuitem"
                    class="flex w-full items-center gap-2.5 px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-100 focus:outline-none focus-visible:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <LogOut class="h-4 w-4" aria-hidden="true" />
                    {{ t('navigation.header.logout') }}
                </button>
            </form>
        </div>
    </div>
</template>
