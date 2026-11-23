<script setup lang="ts">
import AppHeader from '@/components/backoffice/AppHeader.vue';
import AppSidebar from '@/components/backoffice/AppSidebar.vue';
import { useSidebar } from '@/composables/useSidebar';

const { collapsed, mobileOpen, toggle, closeMobile } = useSidebar();
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
        <AppSidebar :collapsed="collapsed" :mobile-open="mobileOpen" @close="closeMobile" />

        <div class="transition-[padding] duration-200" :class="collapsed ? 'lg:pl-[72px]' : 'lg:pl-64'">
            <AppHeader :collapsed="collapsed" @toggle="toggle" />

            <main class="p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
