import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type TranslationTree = { [key: string]: string | TranslationTree };

/**
 * Reads the translation lines shared by HandleInertiaRequests, so components
 * never hold a visible string of their own.
 *
 * The product name is always available as the `:brand` placeholder.
 */
export function useTranslations() {
    const page = usePage();

    const translations = computed<TranslationTree>(() => (page.props.translations ?? {}) as TranslationTree);
    const brandName = computed<string>(() => (page.props.brand as { name?: string } | undefined)?.name ?? '');
    const locale = computed<string>(() => (page.props.locale as string | undefined) ?? 'fr');
    const locales = computed<string[]>(() => (page.props.locales as string[] | undefined) ?? ['fr', 'en']);

    function t(key: string, replacements: Record<string, string | number> = {}): string {
        const line = key.split('.').reduce<string | TranslationTree | undefined>((carry, segment) => {
            if (carry && typeof carry === 'object') {
                return carry[segment];
            }

            return undefined;
        }, translations.value);

        if (typeof line !== 'string') {
            // Showing the key makes a missing translation obvious instead of silent.
            return key;
        }

        return Object.entries({ brand: brandName.value, ...replacements }).reduce(
            (text, [name, replacement]) => text.split(`:${name}`).join(String(replacement)),
            line,
        );
    }

    return { t, locale, locales, brandName };
}
