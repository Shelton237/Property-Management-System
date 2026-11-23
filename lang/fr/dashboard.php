<?php

declare(strict_types=1);

return [

    'page_title' => 'Tableau de bord',
    'welcome' => 'Bienvenue, :name',
    'no_hotel' => 'Aucun hôtel rattaché à ce compte',
    'logout' => 'Se déconnecter',

    'greeting' => [
        'morning' => 'Bonjour, :name',
        'afternoon' => 'Bon après-midi, :name',
        'evening' => 'Bonsoir, :name',
    ],

    'month_to_date' => 'Depuis le début du mois',
    'total' => 'Total',
    'day' => 'Jour',
    'today' => 'Auj.',
    'footnote' => 'Les chiffres portent sur l’établissement sélectionné et sa date d’exploitation.',

    'view' => [
        'chart' => 'Graphique',
        'table' => 'Tableau',
    ],

    'kpi' => [
        'occupancy' => 'Taux d’occupation',
        'adr' => 'Prix moyen par nuit',
        'revpar' => 'RevPAR',
        'revenue_today' => 'Recettes du jour',
        'movements' => 'Arrivées / Départs',
        'guests_in_house' => 'Clients présents',
        'basis_yesterday' => 'vs hier',
        'basis_week' => 'vs moyenne 7 jours',
        'movements_caption' => ':in attendues · :out attendus',
        'rooms_caption' => ':count chambres',
    ],

    'revenue' => [
        'title' => 'Recettes des 14 derniers jours',
        'subtitle' => ':total au total, hors taxes',
        'series' => [
            'rooms' => 'Hébergement',
            'food_beverage' => 'Restauration',
            'other' => 'Autres services',
        ],
    ],

    'room_status' => [
        'title' => 'État des chambres',
        'subtitle' => ':occupied chambres occupées sur :total vendables',
        'vacant_clean' => 'Libre et propre',
        'vacant_dirty' => 'Libre à nettoyer',
        'occupied' => 'Occupée',
        'arriving' => 'Arrivée aujourd’hui',
        'departing' => 'Départ aujourd’hui',
        'reserved' => 'Réservée',
        'out_of_order' => 'Hors service',
    ],

    'occupancy' => [
        'title' => 'Tendance d’occupation',
        'subtitle' => 'Sur 30 jours',
    ],

    'on_the_books' => [
        'title' => 'Les 14 prochains jours',
        'subtitle' => 'Chambres déjà vendues',
        'rooms_sold' => ':count chambres',
    ],

    'channels' => [
        'title' => 'Canaux de réservation',
        'walk_in' => 'Passage',
        'direct_phone' => 'Direct · téléphone',
        'corporate' => 'Entreprise',
        'ota' => 'Agence en ligne',
    ],

    'departments' => [
        'title' => 'Recettes par département',
        'rooms' => 'Hébergement',
        'cancellation_fees' => 'Frais d’annulation et non-présentation',
        'restaurant' => 'Restaurant',
        'room_service' => 'Service en chambre',
        'laundry' => 'Blanchisserie',
        'front_office' => 'Réception',
    ],

    'payments' => [
        'title' => 'Moyens de paiement',
        'cash' => 'Espèces',
        'card' => 'Carte bancaire',
        'orange_money' => 'Orange Money',
        'mtn_momo' => 'MTN MoMo',
    ],

    'movements' => [
        'title' => 'Arrivées et départs',
        'subtitle' => 'Mouvements attendus le :date',
        'arrivals' => 'Arrivées',
        'departures' => 'Départs',
        'done' => ':count traités',
        'done_badge' => 'Fait',
        'room' => 'Chambre :number',
        'no_room' => 'Pas encore de chambre',
        'empty_arrivals' => 'Aucune arrivée aujourd’hui.',
        'empty_departures' => 'Aucun départ aujourd’hui.',
    ],

    'activity' => [
        'title' => 'Activité récente',
        'empty' => 'Aucune activité enregistrée.',
    ],

    'outstanding' => [
        'title' => 'Soldes impayés',
        'subtitle' => ':accounts comptes ouverts · :total',
        'empty' => 'Aucun solde impayé.',
    ],

    'reservation_status' => [
        'tentative' => 'Provisoire',
        'confirmed' => 'Confirmée',
        'in_house' => 'En séjour',
        'departed' => 'Partie',
        'cancelled' => 'Annulée',
        'no_show' => 'Non présentée',
    ],

    'nationalities' => [
        'title' => 'Nationalités',
        'subtitle' => 'Répartition de :count clients ce mois-ci',
        'unknown' => 'Non renseignée',
    ],

    'housekeeping' => [
        'title' => 'Ménage',
        'subtitle' => ':clean chambres propres sur :total · :done % fait',
        'clean' => 'Propre',
        'in_progress' => 'En cours',
        'to_inspect' => 'À inspecter',
        'dirty' => 'Sale',
    ],

];
