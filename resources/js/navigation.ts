import {
    BadgeDollarSign,
    Boxes,
    BriefcaseBusiness,
    Building2,
    CalendarCheck,
    CalendarPlus,
    Car,
    ChartColumn,
    ConciergeBell,
    History,
    IdCard,
    KeyRound,
    Landmark,
    LayoutGrid,
    PartyPopper,
    Plug,
    ReceiptText,
    Scissors,
    Settings,
    ShieldCheck,
    Shirt,
    SprayCan,
    Ticket,
    UserRound,
    Users,
    UtensilsCrossed,
    Waves,
} from 'lucide-vue-next';
import type { Component } from 'vue';

export interface NavigationItem {
    /** Key under the `navigation.items` translation group. */
    key: string;
    icon: Component;
    /**
     * Ziggy route name. Left undefined while the module does not exist: the
     * entry is then shown, but not navigable. Adding the route here is all it
     * takes to turn the entry on.
     */
    route?: string;
}

export interface NavigationSection {
    /** Key under the `navigation.sections` translation group. */
    key: string;
    items: NavigationItem[];
}

export const navigation: NavigationSection[] = [
    {
        key: 'overview',
        items: [
            { key: 'dashboard', icon: LayoutGrid, route: 'dashboard' },
            { key: 'my_hr', icon: UserRound },
        ],
    },
    {
        key: 'front_office',
        items: [
            { key: 'front_desk', icon: ConciergeBell, route: 'front-desk.index' },
            { key: 'reservations', icon: CalendarCheck },
            { key: 'new_booking', icon: CalendarPlus },
            { key: 'group_booking', icon: Users },
            { key: 'guest_folio', icon: ReceiptText },
            { key: 'night_audit', icon: History },
            { key: 'guests', icon: IdCard },
            { key: 'security_copy', icon: ShieldCheck },
            { key: 'key_cards', icon: KeyRound },
            { key: 'meal_tokens', icon: Ticket },
        ],
    },
    {
        key: 'operations',
        items: [
            { key: 'housekeeping', icon: SprayCan },
            { key: 'restaurant_pos', icon: UtensilsCrossed },
            { key: 'laundry', icon: Shirt },
            { key: 'banquet_events', icon: PartyPopper },
            { key: 'rent_car_parking', icon: Car },
            { key: 'salon_spa', icon: Scissors },
            { key: 'pool', icon: Waves },
            { key: 'inventory', icon: Boxes },
        ],
    },
    {
        key: 'finance',
        items: [
            { key: 'accounts', icon: Landmark },
            { key: 'hr_payroll', icon: BriefcaseBusiness },
            { key: 'reports', icon: ChartColumn },
        ],
    },
    {
        key: 'system',
        items: [
            { key: 'property_setup', icon: Building2 },
            { key: 'integrations', icon: Plug },
            { key: 'subscription', icon: BadgeDollarSign },
            { key: 'settings', icon: Settings },
        ],
    },
];
