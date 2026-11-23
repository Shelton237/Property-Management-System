<?php

declare(strict_types=1);

return [

    'page_title' => 'Réception',
    'title' => 'Réception',

    'views' => [
        'today' => 'Aujourd’hui',
        'planner' => 'Planning 7 jours',
    ],

    'filters' => [
        'show' => 'Afficher les filtres',
        'hide' => 'Masquer les filtres',
        'search' => 'Rechercher une chambre ou un client',
        'all_floors' => 'Tous les étages',
        'floor' => 'Étage :floor',
        'all_types' => 'Tous les types',
        'all' => 'Toutes',
        'vacant' => 'Libres',
        'occupied' => 'Occupées',
        'stay_over' => 'En séjour',
        'arrivals' => 'Arrivées',
        'departures' => 'Départs',
        'dirty' => 'À nettoyer',
        'out_of_order' => 'Hors service',
    ],

    'room' => [
        'number' => 'Chambre :number',
        'free' => 'Libre',
        'until' => 'Jusqu’au :date',
        'no_guest' => 'Aucun client',
    ],

    'status' => [
        'vacant_clean' => 'Libre et propre',
        'vacant_dirty' => 'Libre à nettoyer',
        'occupied' => 'Occupée',
        'out_of_order' => 'Hors service',
    ],

    'housekeeping' => [
        'clean' => 'Propre',
        'in_progress' => 'En cours',
        'to_inspect' => 'À inspecter',
        'dirty' => 'Sale',
    ],

    'actions' => [
        'check_in' => 'Arrivée',
        'check_out' => 'Départ',
        'mark_clean' => 'Marquer propre',
        'mark_dirty' => 'Marquer sale',
        'out_of_order' => 'Mettre hors service',
        'back_in_service' => 'Remettre en service',
        'more' => 'Autres actions',
    ],

    'flash' => [
        'checked_in' => 'Arrivée enregistrée pour la chambre :room.',
        'checked_out' => 'Départ enregistré pour la chambre :room.',
        'housekeeping' => 'État de ménage mis à jour pour la chambre :room.',
        'out_of_order' => 'Chambre :room mise hors service.',
        'back_in_service' => 'Chambre :room remise en service.',
    ],

    'errors' => [
        'no_arrival' => 'Aucune arrivée attendue aujourd’hui sur la chambre :room.',
        'no_departure' => 'Aucun départ à enregistrer sur la chambre :room.',
        'out_of_order' => 'La chambre :room est hors service.',
        'occupied' => 'La chambre :room est occupée : impossible de la mettre hors service.',
        'unknown_state' => 'État de ménage inconnu.',
    ],

    'activity' => [
        'checkin' => 'Arrivée enregistrée · chambre :room',
        'checkout' => 'Départ enregistré · chambre :room',
        'housekeeping_clean' => 'Chambre :room passée en propre',
        'housekeeping_in_progress' => 'Ménage en cours · chambre :room',
        'housekeeping_to_inspect' => 'Chambre :room à inspecter',
        'housekeeping_dirty' => 'Chambre :room passée en sale',
        'out_of_order' => 'Chambre :room mise hors service',
        'back_in_service' => 'Chambre :room remise en service',
    ],

    'folio' => [
        'night' => 'Nuitée',
    ],

    'planner' => [
        'room' => 'Chambre',
        'free' => 'Libre',
        'arrival' => 'Arrivée',
    ],

    'empty' => [
        'title' => 'Aucune chambre ne correspond',
        'body' => 'Modifiez les filtres pour voir d’autres chambres.',
    ],

    'no_rooms' => [
        'title' => 'Aucune chambre configurée',
        'body' => 'Créez vos types de chambres et vos chambres pour commencer à travailler depuis la réception.',
    ],

];
