<script setup lang="ts">
import { ROOM_STATUS, resolve } from '@/charts/palette';
import Alert from '@/components/ui/Alert.vue';
import { useDarkMode } from '@/composables/useDarkMode';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { BedDouble, CalendarRange, LayoutGrid, LogIn, LogOut, Search, SprayCan, Wrench } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface RoomCard {
    id: number;
    number: string;
    floor: string | null;
    roomTypeId: number;
    roomType: string | null;
    status: string;
    housekeeping: string;
    guest: string | null;
    code: string | null;
    checkOut: string | null;
    canCheckIn: boolean;
    canCheckOut: boolean;
    isArrival: boolean;
    isDeparture: boolean;
    isStayOver: boolean;
}

interface Board {
    businessDate: string;
    rooms: RoomCard[];
    counts: Record<string, number>;
    floors: string[];
    roomTypes: Array<{ id: number; name: string }>;
}

interface Planner {
    dates: string[];
    rows: Array<{
        id: number;
        number: string;
        type: string | null;
        outOfOrder: boolean;
        cells: Array<{ date: string; guest: string | null; code: string | null; status: string | null; arrival: boolean }>;
    }>;
}

const props = defineProps<{ board: Board | null; planner: Planner | null }>();

const { t } = useTranslations();
const { isDark } = useDarkMode();
const { longDate, weekday, shortDay } = useFormatters();

const page = usePage();

const flash = computed(() => (page.props.flash as { status?: string } | undefined)?.status);
const actionError = computed(() => (page.props.errors as Record<string, string>)?.room);

const view = ref<'today' | 'planner'>('today');
const showFilters = ref(true);
const activeFilter = ref('all');
const search = ref('');
const floor = ref('');
const roomType = ref('');

/** The room whose action is in flight, so its buttons can be disabled. */
const busyRoom = ref<number | null>(null);

const FILTERS = ['all', 'vacant', 'occupied', 'stay_over', 'arrivals', 'departures', 'dirty', 'out_of_order'] as const;

const FILTER_DOTS: Record<string, string | null> = {
    all: null,
    vacant: 'vacant_clean',
    occupied: 'occupied',
    stay_over: 'reserved',
    arrivals: 'arriving',
    departures: 'departing',
    dirty: 'vacant_dirty',
    out_of_order: 'out_of_order',
};

function matchesFilter(room: RoomCard): boolean {
    switch (activeFilter.value) {
        case 'vacant':
            return room.status === 'vacant_clean' || room.status === 'vacant_dirty';
        case 'occupied':
            return room.status === 'occupied';
        case 'stay_over':
            return room.isStayOver;
        case 'arrivals':
            return room.isArrival;
        case 'departures':
            return room.isDeparture;
        case 'dirty':
            return room.housekeeping === 'dirty';
        case 'out_of_order':
            return room.status === 'out_of_order';
        default:
            return true;
    }
}

const visibleRooms = computed<RoomCard[]>(() => {
    if (props.board === null) return [];

    const needle = search.value.trim().toLowerCase();

    return props.board.rooms.filter((room) => {
        if (!matchesFilter(room)) return false;
        if (floor.value !== '' && room.floor !== floor.value) return false;
        if (roomType.value !== '' && String(room.roomTypeId) !== roomType.value) return false;

        if (needle !== '') {
            const haystack = `${room.number} ${room.guest ?? ''} ${room.code ?? ''}`.toLowerCase();
            if (!haystack.includes(needle)) return false;
        }

        return true;
    });
});

function send(room: RoomCard, url: string, data: Record<string, unknown> = {}): void {
    busyRoom.value = room.id;

    router.post(url, data, {
        preserveScroll: true,
        onFinish: () => {
            busyRoom.value = null;
        },
    });
}

const checkIn = (room: RoomCard) => send(room, route('front-desk.check-in', { room: room.id }));
const checkOut = (room: RoomCard) => send(room, route('front-desk.check-out', { room: room.id }));
const markClean = (room: RoomCard) => send(room, route('front-desk.housekeeping', { room: room.id }), { state: 'clean' });
const markDirty = (room: RoomCard) => send(room, route('front-desk.housekeeping', { room: room.id }), { state: 'dirty' });
const toggleService = (room: RoomCard) =>
    send(room, route('front-desk.service', { room: room.id }), {
        out_of_order: room.status !== 'out_of_order',
    });

function statusColour(status: string): string {
    return resolve(ROOM_STATUS[status] ?? ROOM_STATUS.out_of_order, isDark.value);
}
</script>

<template>
    <Head :title="t('front_desk.page_title')" />

    <BackofficeLayout>
        <div v-if="board === null" class="rounded-xl border border-slate-200 bg-white p-8 text-center dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ t('dashboard.no_hotel') }}</p>
        </div>

        <template v-else>
            <header class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50">
                    {{ t('front_desk.title') }}
                </h1>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex rounded-lg border border-slate-200 p-0.5 dark:border-slate-700" role="tablist">
                        <button
                            v-for="mode in ['today', 'planner'] as const"
                            :key="mode"
                            type="button"
                            role="tab"
                            :aria-selected="view === mode"
                            class="flex cursor-pointer items-center gap-2 rounded-md px-3 py-1.5 text-[13px] font-medium transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                            :class="
                                view === mode
                                    ? 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100'
                                    : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100'
                            "
                            @click="view = mode"
                        >
                            <component :is="mode === 'today' ? LayoutGrid : CalendarRange" class="h-4 w-4" aria-hidden="true" />
                            {{ t(`front_desk.views.${mode}`) }}
                        </button>
                    </div>

                    <button
                        v-if="view === 'today'"
                        type="button"
                        class="cursor-pointer rounded-lg border border-slate-200 px-3 py-2 text-[13px] font-medium text-slate-600 transition-colors duration-200 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="showFilters = !showFilters"
                    >
                        {{ showFilters ? t('front_desk.filters.hide') : t('front_desk.filters.show') }}
                    </button>
                </div>
            </header>

            <div v-if="flash" class="mb-4">
                <Alert variant="success" :message="flash" />
            </div>

            <div v-if="actionError" class="mb-4">
                <Alert variant="error" :message="actionError" />
            </div>

            <template v-if="view === 'today'">
                <section v-if="showFilters" class="mb-4 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button
                            v-for="key in FILTERS"
                            :key="key"
                            type="button"
                            :aria-pressed="activeFilter === key"
                            class="flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-[13px] transition-colors duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600"
                            :class="
                                activeFilter === key
                                    ? 'bg-slate-100 font-medium text-slate-900 dark:bg-slate-800 dark:text-slate-100'
                                    : 'text-slate-600 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-slate-800'
                            "
                            @click="activeFilter = key"
                        >
                            <span
                                v-if="FILTER_DOTS[key]"
                                class="h-2 w-2 shrink-0 rounded-full"
                                :style="{ backgroundColor: statusColour(FILTER_DOTS[key] as string) }"
                                aria-hidden="true"
                            />
                            {{ t(`front_desk.filters.${key}`) }}
                            <span class="tabular-nums text-slate-400">{{ board.counts[key] }}</span>
                        </button>
                    </div>

                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="relative">
                            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                            <input
                                v-model="search"
                                type="search"
                                :placeholder="t('front_desk.filters.search')"
                                :aria-label="t('front_desk.filters.search')"
                                class="h-11 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-[13px] text-slate-900 placeholder:text-slate-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            />
                        </div>

                        <select
                            v-model="floor"
                            :aria-label="t('front_desk.filters.all_floors')"
                            class="h-11 w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 text-[13px] text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <option value="">{{ t('front_desk.filters.all_floors') }}</option>
                            <option v-for="value in board.floors" :key="value" :value="value">
                                {{ t('front_desk.filters.floor', { floor: value }) }}
                            </option>
                        </select>

                        <select
                            v-model="roomType"
                            :aria-label="t('front_desk.filters.all_types')"
                            class="h-11 w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 text-[13px] text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        >
                            <option value="">{{ t('front_desk.filters.all_types') }}</option>
                            <option v-for="type in board.roomTypes" :key="type.id" :value="String(type.id)">
                                {{ type.name }}
                            </option>
                        </select>
                    </div>
                </section>

                <!-- No rooms at all is a setup problem, not an empty filter. -->
                <div
                    v-if="board.rooms.length === 0"
                    class="rounded-xl border border-slate-200 bg-white p-10 text-center dark:border-slate-800 dark:bg-slate-900"
                >
                    <BedDouble class="mx-auto h-8 w-8 text-slate-300" aria-hidden="true" />
                    <p class="mt-3 text-[15px] font-semibold text-slate-900 dark:text-slate-100">
                        {{ t('front_desk.no_rooms.title') }}
                    </p>
                    <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">{{ t('front_desk.no_rooms.body') }}</p>
                </div>

                <div
                    v-else-if="visibleRooms.length === 0"
                    class="rounded-xl border border-slate-200 bg-white p-10 text-center dark:border-slate-800 dark:bg-slate-900"
                >
                    <p class="text-[15px] font-semibold text-slate-900 dark:text-slate-100">
                        {{ t('front_desk.empty.title') }}
                    </p>
                    <p class="mt-1 text-[13px] text-slate-500 dark:text-slate-400">{{ t('front_desk.empty.body') }}</p>
                </div>

                <ul v-else class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                    <li
                        v-for="room in visibleRooms"
                        :key="room.id"
                        class="flex flex-col rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-lg font-bold tracking-tight text-slate-900 dark:text-slate-50">
                                    {{ room.number }}
                                </p>
                                <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ room.roomType }}
                                </p>
                            </div>

                            <span
                                class="flex shrink-0 items-center gap-1.5 rounded-full bg-slate-50 px-2 py-1 text-[11px] text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            >
                                <span class="h-2 w-2 rounded-full" :style="{ backgroundColor: statusColour(room.status) }" aria-hidden="true" />
                                {{ t(`front_desk.status.${room.status}`) }}
                            </span>
                        </div>

                        <div class="mt-3 min-h-[38px]">
                            <template v-if="room.guest">
                                <p class="truncate text-[13px] font-medium text-slate-900 dark:text-slate-100">
                                    {{ room.guest }}
                                </p>
                                <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ room.code }}
                                    <template v-if="room.checkOut"> · {{ t('front_desk.room.until', { date: longDate(room.checkOut) }) }} </template>
                                </p>
                            </template>
                            <p v-else class="text-[13px] text-slate-400">{{ t('front_desk.room.no_guest') }}</p>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 dark:border-slate-800">
                            <button
                                v-if="room.canCheckIn"
                                type="button"
                                :disabled="busyRoom === room.id"
                                class="flex min-h-[36px] cursor-pointer items-center gap-1.5 rounded-lg bg-brand-800 px-3 text-[13px] font-semibold text-white transition-colors duration-200 hover:bg-brand-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 disabled:cursor-not-allowed disabled:opacity-60"
                                @click="checkIn(room)"
                            >
                                <LogIn class="h-4 w-4" aria-hidden="true" />
                                {{ t('front_desk.actions.check_in') }}
                            </button>

                            <button
                                v-if="room.canCheckOut"
                                type="button"
                                :disabled="busyRoom === room.id"
                                class="flex min-h-[36px] cursor-pointer items-center gap-1.5 rounded-lg bg-brand-800 px-3 text-[13px] font-semibold text-white transition-colors duration-200 hover:bg-brand-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 disabled:cursor-not-allowed disabled:opacity-60"
                                @click="checkOut(room)"
                            >
                                <LogOut class="h-4 w-4" aria-hidden="true" />
                                {{ t('front_desk.actions.check_out') }}
                            </button>

                            <button
                                v-if="room.housekeeping !== 'clean' && room.status !== 'out_of_order'"
                                type="button"
                                :disabled="busyRoom === room.id"
                                class="flex min-h-[36px] cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 px-3 text-[13px] font-medium text-slate-700 transition-colors duration-200 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="markClean(room)"
                            >
                                <SprayCan class="h-4 w-4" aria-hidden="true" />
                                {{ t('front_desk.actions.mark_clean') }}
                            </button>

                            <button
                                v-else-if="room.status === 'vacant_clean'"
                                type="button"
                                :disabled="busyRoom === room.id"
                                class="flex min-h-[36px] cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 px-3 text-[13px] font-medium text-slate-700 transition-colors duration-200 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="markDirty(room)"
                            >
                                <SprayCan class="h-4 w-4" aria-hidden="true" />
                                {{ t('front_desk.actions.mark_dirty') }}
                            </button>

                            <button
                                v-if="room.status !== 'occupied'"
                                type="button"
                                :disabled="busyRoom === room.id"
                                :title="
                                    room.status === 'out_of_order' ? t('front_desk.actions.back_in_service') : t('front_desk.actions.out_of_order')
                                "
                                :aria-label="
                                    room.status === 'out_of_order' ? t('front_desk.actions.back_in_service') : t('front_desk.actions.out_of_order')
                                "
                                class="ml-auto grid h-9 w-9 cursor-pointer place-items-center rounded-lg border border-slate-200 text-slate-500 transition-colors duration-200 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                                @click="toggleService(room)"
                            >
                                <Wrench class="h-4 w-4" aria-hidden="true" />
                            </button>
                        </div>
                    </li>
                </ul>
            </template>

            <!-- Seven-day planner: read-only for now. -->
            <section v-else-if="planner" class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="w-full min-w-[720px] text-left text-[13px]">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800">
                            <th scope="col" class="px-4 py-3 text-xs font-medium text-slate-500 dark:text-slate-400">
                                {{ t('front_desk.planner.room') }}
                            </th>
                            <th
                                v-for="date in planner.dates"
                                :key="date"
                                scope="col"
                                class="px-2 py-3 text-center text-xs font-medium text-slate-500 dark:text-slate-400"
                            >
                                <span class="block">{{ weekday(date) }}</span>
                                <span class="block text-slate-400">{{ shortDay(date) }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="row in planner.rows" :key="row.id">
                            <th scope="row" class="whitespace-nowrap px-4 py-2 font-medium text-slate-900 dark:text-slate-100">
                                {{ row.number }}
                                <span class="ml-1 text-[11px] font-normal text-slate-400">{{ row.type }}</span>
                            </th>
                            <td v-for="cell in row.cells" :key="cell.date" class="px-1 py-1.5">
                                <div
                                    v-if="cell.guest"
                                    class="truncate rounded px-2 py-1 text-[11px] text-white"
                                    :style="{
                                        backgroundColor: statusColour(cell.status === 'in_house' ? 'occupied' : 'reserved'),
                                    }"
                                    :title="`${cell.guest} · ${cell.code}`"
                                >
                                    {{ cell.guest }}
                                </div>
                                <div
                                    v-else-if="row.outOfOrder"
                                    class="rounded bg-slate-100 px-2 py-1 text-center text-[11px] text-slate-400 dark:bg-slate-800"
                                >
                                    —
                                </div>
                                <div v-else class="px-2 py-1 text-center text-[11px] text-slate-300 dark:text-slate-600">
                                    {{ t('front_desk.planner.free') }}
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </template>
    </BackofficeLayout>
</template>
