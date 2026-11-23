<script setup lang="ts">
defineProps<{ code: string }>();
</script>

<template>
    <span class="block shrink-0 overflow-hidden rounded-full ring-1 ring-slate-200 dark:ring-slate-700" aria-hidden="true">
        <svg v-if="code === 'fr'" viewBox="0 0 3 2" preserveAspectRatio="none" class="h-full w-full">
            <rect width="1" height="2" fill="#0055a4" />
            <rect x="1" width="1" height="2" fill="#ffffff" />
            <rect x="2" width="1" height="2" fill="#ef4135" />
        </svg>

        <svg v-else viewBox="0 0 60 30" preserveAspectRatio="none" class="h-full w-full">
            <rect width="60" height="30" fill="#012169" />
            <path d="M0 0 L60 30 M60 0 L0 30" stroke="#ffffff" stroke-width="7" />
            <path d="M0 0 L60 30 M60 0 L0 30" stroke="#c8102e" stroke-width="4" />
            <path d="M30 0 V30 M0 15 H60" stroke="#ffffff" stroke-width="11" />
            <path d="M30 0 V30 M0 15 H60" stroke="#c8102e" stroke-width="6" />
        </svg>
    </span>
</template>
