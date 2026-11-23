<script setup lang="ts">
import BarChart from '@/charts/BarChart.vue';
import LineChart from '@/charts/LineChart.vue';
import { ROOM_STATUS, resolve } from '@/charts/palette';
import ShareBars from '@/charts/ShareBars.vue';
import StackedBars from '@/charts/StackedBars.vue';
import StackedProgress from '@/charts/StackedProgress.vue';
import Panel from '@/components/dashboard/Panel.vue';
import StatCard from '@/components/dashboard/StatCard.vue';
import { useDarkMode } from '@/composables/useDarkMode';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ArrowRight, LogIn, LogOut } from 'lucide-vue-next';
import { computed } from 'vue';

interface Metrics {
    currency: string;
    kpis: Array<Parameters<typeof StatCard>[0] extends never ? never : any>;
    revenueSeries: { days: Array<{ day: string; rooms: number; food_beverage: number; other: number }>; total: number };
    roomStatus: { rows: Array<{ key: string; count: number; share: number }>; occupied: number; sellable: number };
    occupancyTrend: Array<{ day: string; value: number }>;
    onTheBooks: Array<{ day: string; sold: number; share: number }>;
    bookingChannels: Array<{ key: string; amount: number; share: number }>;
    revenueByDepartment: Array<{ key: string; amount: number; share: number }>;
    paymentMethods: Array<{ key: string; amount: number; share: number }>;
    movements: {
        arrivals: Array<Movement>;
        departures: Array<Movement>;
        arrivalsDone: number;
        departuresDone: number;
    };
    activity: Array<{ kind: string; description: string; reference: string | null; author: string | null; at: string }>;
    outstanding: { accounts: number; total: number; rows: Array<{ code: string; guest: string; status: string; balance: number }> };
    nationalities: { guests: number; rows: Array<{ country: string; count: number; share: number }> };
    housekeeping: { rows: Array<{ key: string; count: number }>; total: number; clean: number; done: number };
}

interface Movement {
    code: string;
    guest: string;
    initials: string;
    room: string | null;
    roomType: string | null;
    done: boolean;
}

defineProps<{ metrics: Metrics | null }>();

const { t, locale } = useTranslations();
const { isDark } = useDarkMode();
const { money, compactMoney, number, percent, fromNow, longDate } = useFormatters();

const page = usePage();

const firstName = computed(() => {
    const name = (page.props.auth as { user?: { name?: string } } | undefined)?.user?.name ?? '';

    return name.split(' ')[0] ?? '';
});

const businessDate = computed(() => (page.props.businessDate as { label: string; iso: string } | null)?.label ?? '');

const greeting = computed(() => {
    const hour = new Date().getHours();

    if (hour < 12) return 'morning';
    if (hour < 18) return 'afternoon';

    return 'evening';
});

const regionNames = computed(() => {
    try {
        return new Intl.DisplayNames([locale.value === 'en' ? 'en-GB' : 'fr-FR'], { type: 'region' });
    } catch {
        return null;
    }
});

function countryName(code: string): string {
    if (code === 'ZZ') return t('dashboard.nationalities.unknown');

    try {
        return regionNames.value?.of(code) ?? code;
    } catch {
        return code;
    }
}

/** Activity dot colour, reusing validated hues. */
const ACTIVITY_COLOURS: Record<string, string> = {
    payment: '#1baf7a',
    folio: '#2a78d6',
    checkin: '#008300',
    checkout: '#e87ba4',
    housekeeping: '#eda100',
    room: '#6250d6',
};
</script>

<template>
    <Head :title="t('dashboard.page_title')" />

    <BackofficeLayout>
        <!-- No property attached: a platform account has no operations to show. -->
        <div v-if="metrics === null" class="rounded-xl border border-slate-200 bg-white p-8 text-center dark:border-slate-800 dark:bg-slate-900">
            <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                {{ t('dashboard.welcome', { name: firstName }) }}
            </h1>
            <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">{{ t('dashboard.no_hotel') }}</p>
        </div>

        <template v-else>
            <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">
                    {{ t(`dashboard.greeting.${greeting}`, { name: firstName }) }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ businessDate }}</p>
            </header>

            <!-- Key figures -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
                <StatCard v-for="stat in metrics.kpis" :key="stat.key" :stat="stat" />
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
                <!-- Revenue -->
                <div class="xl:col-span-2">
                    <Panel
                        :title="t('dashboard.revenue.title')"
                        :subtitle="t('dashboard.revenue.subtitle', { total: money(metrics.revenueSeries.total) })"
                        views
                    >
                        <StackedBars :days="metrics.revenueSeries.days" />

                        <template #table>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-[13px]">
                                    <thead class="text-xs text-slate-500 dark:text-slate-400">
                                        <tr>
                                            <th scope="col" class="py-2 pr-4 font-medium">{{ t('dashboard.day') }}</th>
                                            <th scope="col" class="py-2 pr-4 text-right font-medium">{{ t('dashboard.revenue.series.rooms') }}</th>
                                            <th scope="col" class="py-2 pr-4 text-right font-medium">
                                                {{ t('dashboard.revenue.series.food_beverage') }}
                                            </th>
                                            <th scope="col" class="py-2 text-right font-medium">{{ t('dashboard.revenue.series.other') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <tr v-for="day in metrics.revenueSeries.days" :key="day.day">
                                            <th scope="row" class="py-2 pr-4 font-normal text-slate-700 dark:text-slate-300">
                                                {{ longDate(day.day) }}
                                            </th>
                                            <td class="py-2 pr-4 text-right tabular-nums text-slate-900 dark:text-slate-100">
                                                {{ compactMoney(day.rooms) }}
                                            </td>
                                            <td class="py-2 pr-4 text-right tabular-nums text-slate-900 dark:text-slate-100">
                                                {{ compactMoney(day.food_beverage) }}
                                            </td>
                                            <td class="py-2 text-right tabular-nums text-slate-900 dark:text-slate-100">
                                                {{ compactMoney(day.other) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </Panel>
                </div>

                <!-- Room status -->
                <Panel
                    :title="t('dashboard.room_status.title')"
                    :subtitle="t('dashboard.room_status.subtitle', { occupied: metrics.roomStatus.occupied, total: metrics.roomStatus.sellable })"
                >
                    <dl class="grid gap-3">
                        <div v-for="row in metrics.roomStatus.rows" :key="row.key">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="flex items-center gap-2 text-[13px] text-slate-700 dark:text-slate-300">
                                    <span
                                        class="h-2.5 w-2.5 shrink-0 rounded-full"
                                        :style="{ backgroundColor: resolve(ROOM_STATUS[row.key], isDark) }"
                                        aria-hidden="true"
                                    />
                                    {{ t(`dashboard.room_status.${row.key}`) }}
                                </dt>
                                <dd class="shrink-0 text-[13px] tabular-nums">
                                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ row.count }}</span>
                                    <span class="ml-2 text-slate-400">{{ row.share }} %</span>
                                </dd>
                            </div>
                            <div class="mt-1.5 h-1 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full"
                                    :style="{ width: `${row.share}%`, backgroundColor: resolve(ROOM_STATUS[row.key], isDark) }"
                                />
                            </div>
                        </div>
                    </dl>
                </Panel>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
                <Panel :title="t('dashboard.occupancy.title')" :subtitle="t('dashboard.occupancy.subtitle')" views>
                    <LineChart :points="metrics.occupancyTrend" :label="t('dashboard.occupancy.title')" />

                    <template #table>
                        <div class="max-h-[220px] overflow-y-auto">
                            <table class="w-full text-left text-[13px]">
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <tr v-for="point in metrics.occupancyTrend" :key="point.day">
                                        <th scope="row" class="py-1.5 font-normal text-slate-700 dark:text-slate-300">{{ longDate(point.day) }}</th>
                                        <td class="py-1.5 text-right tabular-nums text-slate-900 dark:text-slate-100">
                                            {{ percent(point.value, 1) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </Panel>

                <Panel :title="t('dashboard.on_the_books.title')" :subtitle="t('dashboard.on_the_books.subtitle')" views>
                    <BarChart :points="metrics.onTheBooks" :label="t('dashboard.on_the_books.title')" />

                    <template #table>
                        <div class="max-h-[220px] overflow-y-auto">
                            <table class="w-full text-left text-[13px]">
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    <tr v-for="point in metrics.onTheBooks" :key="point.day">
                                        <th scope="row" class="py-1.5 font-normal text-slate-700 dark:text-slate-300">{{ longDate(point.day) }}</th>
                                        <td class="py-1.5 text-right tabular-nums text-slate-900 dark:text-slate-100">
                                            {{ t('dashboard.on_the_books.rooms_sold', { count: point.sold }) }} · {{ point.share }} %
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </Panel>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
                <Panel :title="t('dashboard.channels.title')" :subtitle="t('dashboard.month_to_date')">
                    <ShareBars :rows="metrics.bookingChannels" labels="dashboard.channels" />
                </Panel>

                <Panel :title="t('dashboard.departments.title')" :subtitle="t('dashboard.month_to_date')">
                    <ShareBars :rows="metrics.revenueByDepartment" labels="dashboard.departments" />
                </Panel>

                <Panel :title="t('dashboard.payments.title')" :subtitle="t('dashboard.month_to_date')">
                    <ShareBars :rows="metrics.paymentMethods" labels="dashboard.payments" />
                </Panel>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
                <!-- Arrivals and departures -->
                <div class="xl:col-span-2">
                    <Panel :title="t('dashboard.movements.title')" :subtitle="t('dashboard.movements.subtitle', { date: businessDate })">
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div v-for="side in ['arrivals', 'departures'] as const" :key="side">
                                <div class="mb-3 flex items-center justify-between gap-3 border-b border-slate-100 pb-2 dark:border-slate-800">
                                    <h3 class="flex items-center gap-2 text-[13px] font-semibold text-slate-900 dark:text-slate-100">
                                        <component :is="side === 'arrivals' ? LogIn : LogOut" class="h-4 w-4 text-slate-400" aria-hidden="true" />
                                        {{ t(`dashboard.movements.${side}`) }}
                                    </h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ metrics.movements[side].length }} ·
                                        {{
                                            t('dashboard.movements.done', {
                                                count: side === 'arrivals' ? metrics.movements.arrivalsDone : metrics.movements.departuresDone,
                                            })
                                        }}
                                    </p>
                                </div>

                                <p v-if="metrics.movements[side].length === 0" class="py-6 text-center text-[13px] text-slate-400">
                                    {{ t(`dashboard.movements.empty_${side}`) }}
                                </p>

                                <ul v-else class="grid gap-2.5">
                                    <li v-for="movement in metrics.movements[side]" :key="movement.code" class="flex items-center gap-3">
                                        <span
                                            class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-slate-100 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            aria-hidden="true"
                                        >
                                            {{ movement.initials }}
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-[13px] font-medium text-slate-900 dark:text-slate-100">
                                                {{ movement.guest }}
                                            </span>
                                            <span class="block truncate text-[11px] text-slate-500 dark:text-slate-400">
                                                {{
                                                    movement.room
                                                        ? t('dashboard.movements.room', { number: movement.room })
                                                        : t('dashboard.movements.no_room')
                                                }}
                                                <template v-if="movement.roomType"> · {{ movement.roomType }}</template>
                                            </span>
                                        </span>
                                        <span
                                            v-if="movement.done"
                                            class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-500 dark:bg-slate-800 dark:text-slate-400"
                                        >
                                            {{ t('dashboard.movements.done_badge') }}
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </Panel>
                </div>

                <!-- Activity -->
                <Panel :title="t('dashboard.activity.title')">
                    <ol class="grid gap-3.5">
                        <li v-for="(entry, index) in metrics.activity" :key="index" class="flex gap-3">
                            <span
                                class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                :style="{ backgroundColor: ACTIVITY_COLOURS[entry.kind] ?? '#898781' }"
                                aria-hidden="true"
                            />
                            <span class="min-w-0">
                                <span class="block text-[13px] text-slate-800 dark:text-slate-200">
                                    {{ entry.description }}
                                    <template v-if="entry.reference"> · {{ entry.reference }}</template>
                                </span>
                                <span class="block text-[11px] text-slate-400"> {{ entry.author }} · {{ fromNow(entry.at) }} </span>
                            </span>
                        </li>
                    </ol>

                    <p v-if="metrics.activity.length === 0" class="py-6 text-center text-[13px] text-slate-400">
                        {{ t('dashboard.activity.empty') }}
                    </p>
                </Panel>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
                <!-- Outstanding balances -->
                <Panel
                    :title="t('dashboard.outstanding.title')"
                    :subtitle="
                        t('dashboard.outstanding.subtitle', { accounts: metrics.outstanding.accounts, total: money(metrics.outstanding.total) })
                    "
                >
                    <ul class="grid gap-3">
                        <li v-for="row in metrics.outstanding.rows" :key="row.code" class="flex items-center justify-between gap-3">
                            <span class="min-w-0">
                                <span class="block truncate text-[13px] font-medium text-slate-900 dark:text-slate-100">{{ row.guest }}</span>
                                <span class="block text-[11px] text-slate-400">
                                    {{ row.code }} · {{ t(`dashboard.reservation_status.${row.status}`) }}
                                </span>
                            </span>
                            <span class="shrink-0 text-[13px] font-medium tabular-nums text-slate-900 dark:text-slate-100">
                                {{ money(row.balance) }}
                            </span>
                        </li>
                    </ul>

                    <p v-if="metrics.outstanding.rows.length === 0" class="py-6 text-center text-[13px] text-slate-400">
                        {{ t('dashboard.outstanding.empty') }}
                    </p>
                </Panel>

                <!-- Nationalities -->
                <Panel
                    :title="t('dashboard.nationalities.title')"
                    :subtitle="t('dashboard.nationalities.subtitle', { count: metrics.nationalities.guests })"
                >
                    <dl class="grid gap-3.5">
                        <div v-for="row in metrics.nationalities.rows" :key="row.country">
                            <div class="flex items-baseline justify-between gap-3">
                                <dt class="truncate text-[13px] text-slate-700 dark:text-slate-300">{{ countryName(row.country) }}</dt>
                                <dd class="shrink-0 text-[13px] tabular-nums">
                                    <span class="font-medium text-slate-900 dark:text-slate-100">{{ number(row.count) }}</span>
                                    <span class="ml-2 text-slate-400">{{ row.share }} %</span>
                                </dd>
                            </div>
                            <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-brand-700 dark:bg-brand-400" :style="{ width: `${row.share}%` }" />
                            </div>
                        </div>
                    </dl>
                </Panel>

                <!-- Housekeeping -->
                <Panel
                    :title="t('dashboard.housekeeping.title')"
                    :subtitle="
                        t('dashboard.housekeeping.subtitle', {
                            clean: metrics.housekeeping.clean,
                            total: metrics.housekeeping.total,
                            done: metrics.housekeeping.done,
                        })
                    "
                    views
                >
                    <StackedProgress :rows="metrics.housekeeping.rows" :total="metrics.housekeeping.total" />

                    <template #table>
                        <table class="w-full text-left text-[13px]">
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="row in metrics.housekeeping.rows" :key="row.key">
                                    <th scope="row" class="py-2 font-normal text-slate-700 dark:text-slate-300">
                                        {{ t(`dashboard.housekeeping.${row.key}`) }}
                                    </th>
                                    <td class="py-2 text-right tabular-nums text-slate-900 dark:text-slate-100">{{ row.count }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                </Panel>
            </div>

            <p class="mt-6 flex items-center gap-1.5 text-[11px] text-slate-400">
                <ArrowRight class="h-3.5 w-3.5" aria-hidden="true" />
                {{ t('dashboard.footnote') }}
            </p>
        </template>
    </BackofficeLayout>
</template>
