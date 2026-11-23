/**
 * Chart colours, validated with the design-system checker for lightness band,
 * chroma floor, colour-vision separation and contrast, in light and dark mode.
 *
 * Do not reorder these lists: the validator checks adjacent pairs, so the order
 * is part of what passed. Changing a value means running the checker again.
 */

export interface ChartColour {
    light: string;
    dark: string;
}

/** Stacked revenue series, in stacking order. */
export const REVENUE_SERIES: Record<'rooms' | 'food_beverage' | 'other', ChartColour> = {
    rooms: { light: '#2a78d6', dark: '#3987e5' },
    food_beverage: { light: '#eb6834', dark: '#d95926' },
    other: { light: '#1baf7a', dark: '#199e70' },
};

/** Room occupancy states, in the order the widget lists them. */
export const ROOM_STATUS: Record<string, ChartColour> = {
    vacant_clean: { light: '#1baf7a', dark: '#199e70' },
    vacant_dirty: { light: '#eda100', dark: '#c98500' },
    occupied: { light: '#2a78d6', dark: '#3987e5' },
    arriving: { light: '#008300', dark: '#0ca30c' },
    departing: { light: '#e87ba4', dark: '#d55181' },
    reserved: { light: '#6250d6', dark: '#9085e9' },
    // Out of service is deliberately neutral: it is an absence, not a category.
    out_of_order: { light: '#898781', dark: '#898781' },
};

/** Cleaning progress. Reuses the validated hues above. */
export const HOUSEKEEPING: Record<string, ChartColour> = {
    clean: { light: '#1baf7a', dark: '#199e70' },
    in_progress: { light: '#eb6834', dark: '#d95926' },
    to_inspect: { light: '#2a78d6', dark: '#3987e5' },
    dirty: { light: '#eda100', dark: '#c98500' },
};

/** Single hue for magnitude-only charts. */
export const MAGNITUDE: ChartColour = { light: '#2a78d6', dark: '#3987e5' };

export function resolve(colour: ChartColour, dark: boolean): string {
    return dark ? colour.dark : colour.light;
}
