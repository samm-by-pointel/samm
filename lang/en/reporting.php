<?php

return [

    'anonymous_person' => 'Anonymous person',

    'confidentiality' => [
        'anonymous' => [
            'label' => 'Anonymous',
            'description' => 'No identity requested. You receive a tracking code and a secret to find your case again.',
        ],
        'pseudonymous' => [
            'label' => 'Pseudonym',
            'description' => 'You choose an alias to exchange with the people handling your case.',
        ],
        'confidential' => [
            'label' => 'Confidential',
            'description' => 'You give your identity. It is stored apart and visible only to authorized people.',
        ],
    ],

    'consents' => [
        'processing' => [
            'label' => 'Handling of my report',
            'description' => 'I agree that authorized people of the organization process the information I submit.',
        ],
        'contact' => [
            'label' => 'Exchanges in the tracking space',
            'description' => 'I agree that the person in charge of my case replies to me in the tracking space.',
        ],
        'identity_disclosure' => [
            'label' => 'Access to my identity',
            'description' => 'I agree that explicitly authorized people may view my identity, each access being recorded.',
        ],
    ],

];
