import { useTranslations } from '@/composables/useTranslations';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Figures are formatted for the display language and the property currency:
 * 42 500 FCFA in French, XAF 42,500 in English.
 */
export function useFormatters() {
    const page = usePage();
    const { locale } = useTranslations();

    const currency = computed<string>(() => (page.props.metrics as { currency?: string } | null)?.currency ?? 'XAF');

    const tag = computed(() => (locale.value === 'en' ? 'en-GB' : 'fr-FR'));

    function number(value: number, digits = 0): string {
        return new Intl.NumberFormat(tag.value, {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits,
        }).format(value);
    }

    /** Full amount, for totals and balances. */
    function money(value: number): string {
        return `${number(value)} ${currency.value === 'XAF' ? 'FCFA' : currency.value}`;
    }

    /** Shortened amount, for axis labels and dense cards. */
    function compactMoney(value: number): string {
        const abs = Math.abs(value);
        const unit = currency.value === 'XAF' ? 'FCFA' : currency.value;

        if (abs >= 1_000_000) return `${number(value / 1_000_000, 1)} M ${unit}`;
        if (abs >= 1_000) return `${number(value / 1_000)} k ${unit}`;

        return `${number(value)} ${unit}`;
    }

    function percent(value: number, digits = 0): string {
        return `${number(value, digits)} %`;
    }

    /** Short weekday and day, for chart axes. */
    function shortDay(iso: string): string {
        return new Intl.DateTimeFormat(tag.value, { day: 'numeric' }).format(new Date(iso));
    }

    function weekday(iso: string): string {
        return new Intl.DateTimeFormat(tag.value, { weekday: 'short' }).format(new Date(iso));
    }

    function longDate(iso: string): string {
        return new Intl.DateTimeFormat(tag.value, { day: 'numeric', month: 'long' }).format(new Date(iso));
    }

    /** Relative time for the activity feed, without pulling in a library. */
    function fromNow(iso: string): string {
        const minutes = Math.round((Date.now() - new Date(iso).getTime()) / 60000);
        const formatter = new Intl.RelativeTimeFormat(tag.value, { numeric: 'auto' });

        if (Math.abs(minutes) < 60) return formatter.format(-minutes, 'minute');
        if (Math.abs(minutes) < 1440) return formatter.format(-Math.round(minutes / 60), 'hour');

        return formatter.format(-Math.round(minutes / 1440), 'day');
    }

    return { number, money, compactMoney, percent, shortDay, weekday, longDate, fromNow, currency };
}
