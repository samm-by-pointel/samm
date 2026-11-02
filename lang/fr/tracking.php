<?php

return [

    'session_expired' => 'Votre session de suivi a expiré. Saisissez à nouveau vos codes pour continuer.',
    'logged_out' => 'Vous avez quitté le suivi de votre dossier.',
    'message_sent' => 'Votre message a été transmis. Vous verrez la réponse ici lors de votre prochaine visite.',

    'login' => [
        'title' => 'Suivre mon dossier',
        'lead' => 'Saisissez les deux codes reçus lors du dépôt de votre signalement.',
        'reference' => 'Identifiant du dossier',
        'secret' => 'Secret de récupération',
        'help' => 'Les tirets, les espaces et les majuscules n\'ont pas d\'importance. Après plusieurs essais infructueux, l\'accès est suspendu quelques minutes.',
        'submit' => 'Accéder à mon dossier',
        'failed_title' => 'Accès impossible',
        'failed' => 'Identifiant ou secret incorrect, ou accès temporairement suspendu. Vérifiez vos codes et réessayez dans quelques minutes.',
    ],

    'show' => [
        'title' => 'Mon dossier',
        'reference' => 'Identifiant du dossier',
        'status' => 'État du dossier',
        'category' => 'Situation',
        'submitted_at' => 'Déposé le',
        'logout' => 'Quitter le suivi',
        'logout_help' => 'Le suivi se ferme automatiquement après :minutes minutes sans activité.',
    ],

    'thread' => [
        'title' => 'Échanges avec la personne en charge',
        'lead' => 'Vous pouvez poser une question, apporter des précisions ou compléter votre signalement. Les réponses s\'affichent ici.',
        'label' => 'Messages',
        'empty' => 'Aucun message pour le moment. La personne en charge de votre dossier pourra vous écrire ici.',
        'compose' => 'Votre message',
        'compose_help' => 'Vous pouvez compléter votre signalement ou répondre à une question. Ne joignez aucune information que vous ne souhaitez pas transmettre.',
        'send' => 'Envoyer le message',
    ],

];
