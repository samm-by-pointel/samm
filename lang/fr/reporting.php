<?php

return [

    'anonymous_person' => 'Personne anonyme',

    'confidentiality' => [
        'anonymous' => [
            'label' => 'Anonyme',
            'description' => 'Aucune identité demandée. Vous recevez un code de suivi et un secret pour retrouver votre dossier.',
        ],
        'pseudonymous' => [
            'label' => 'Pseudonyme',
            'description' => 'Vous choisissez un nom d\'emprunt pour échanger avec les personnes qui traitent votre dossier.',
        ],
        'confidential' => [
            'label' => 'Confidentiel',
            'description' => 'Vous donnez votre identité. Elle est conservée à part et visible uniquement par les personnes autorisées.',
        ],
    ],

    'consents' => [
        'processing' => [
            'label' => 'Traitement de mon signalement',
            'description' => 'J\'accepte que les personnes habilitées de l\'organisation traitent les informations que je transmets.',
        ],
        'contact' => [
            'label' => 'Échanges dans l\'espace de suivi',
            'description' => 'J\'accepte que la personne en charge de mon dossier me réponde dans l\'espace de suivi.',
        ],
        'identity_disclosure' => [
            'label' => 'Accès à mon identité',
            'description' => 'J\'accepte que les personnes explicitement autorisées puissent consulter mon identité, chaque consultation étant enregistrée.',
        ],
    ],

];
