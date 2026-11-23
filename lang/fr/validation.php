<?php

declare(strict_types=1);

/*
 * Only the rules this application actually uses are translated here. Anything
 * missing falls back to the English messages, as configured by
 * APP_FALLBACK_LOCALE.
 */
return [

    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'exists' => 'La valeur du champ :attribute est invalide.',
    'in' => 'La valeur du champ :attribute est invalide.',
    'max' => [
        'string' => 'Le champ :attribute ne peut pas contenir plus de :max caractères.',
    ],
    'min' => [
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'required' => 'Le champ :attribute est obligatoire.',
    'same' => 'Les champs :attribute et :other doivent être identiques.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'unique' => 'Cette valeur du champ :attribute est déjà utilisée.',

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    'attributes' => [
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'name' => 'nom',
        'token' => 'jeton',
    ],

];
