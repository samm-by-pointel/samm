<?php

return [

    'progress' => 'Progression',
    'step_of' => 'Étape :current sur :total - :label',
    'continue' => 'Continuer',
    'back' => 'Retour',
    'abandon' => 'Abandonner ce signalement',
    'abandoned' => 'Votre brouillon a été effacé. Rien n\'a été transmis.',
    'back_home' => 'Retour à l\'accueil',
    'optional' => '(facultatif)',
    'yes' => 'Oui',
    'no' => 'Non',
    'unknown' => 'Je ne sais pas',

    'steps' => [
        'situation' => 'Situation',
        'confidentialite' => 'Niveau de confidentialité',
        'description' => 'Description',
        'personnes' => 'Personnes concernées',
        'pieces-jointes' => 'Pièces jointes',
        'consentement' => 'Consentement',
    ],

    'errors' => [
        'title' => 'Certaines réponses doivent être revues',
        'help' => 'Les champs concernés sont signalés ci-dessous. Votre saisie a été conservée.',
        'pseudonym_required' => 'Choisissez un pseudonyme pour ce niveau de confidentialité.',
        'name_required' => 'Indiquez votre nom pour ce niveau de confidentialité, ou choisissez un autre niveau.',
        'processing_consent_required' => 'Votre accord pour le traitement du signalement est nécessaire pour transmettre.',
        'identity_consent_required' => 'Pour le niveau Confidentiel, l\'accord sur l\'accès à votre identité est nécessaire. Sinon, revenez en arrière et choisissez un autre niveau.',
    ],

    'start' => [
        'title' => 'Faire un signalement',
        'lead' => 'Vous allez décrire une situation en quelques étapes courtes. Vous pouvez revenir en arrière à tout moment, et rien n\'est transmis avant la dernière étape.',
        'collected_title' => 'Ce que nous recueillons',
        'collected_1' => 'Le type de situation et votre description, avec les précisions que vous choisissez de donner.',
        'collected_2' => 'Le niveau de confidentialité que vous choisissez, et un pseudonyme ou votre identité seulement si vous le décidez.',
        'collected_3' => 'La langue de l\'interface et la date du dépôt.',
        'not_collected_title' => 'Ce que nous ne recueillons pas',
        'not_collected_1' => 'Votre adresse IP, votre appareil ou votre navigateur : aucune trace technique n\'est associée à votre signalement.',
        'not_collected_2' => 'Aucun compte, aucune adresse e-mail obligatoire.',
        'limits' => 'Le contenu de votre récit, les fichiers que vous joindrez ou le contexte peuvent malgré tout permettre de vous reconnaître. Vous choisissez ce que vous écrivez.',
        'session_title' => 'Votre saisie est conservée pendant la session',
        'session_help' => 'Si vous restez inactif plus de deux heures, le brouillon est effacé. Le bouton « Quitter rapidement » vous redirige vers une autre page sans transmettre quoi que ce soit.',
        'begin' => 'Commencer',
        'resume' => 'Reprendre mon signalement',
    ],

    'situation' => [
        'lead' => 'Choisissez la situation qui correspond le mieux. Vous pourrez préciser ensuite.',
        'legend' => 'Type de situation',
    ],

    'confidentiality' => [
        'lead' => 'Chaque niveau est expliqué. Aucun n\'est meilleur qu\'un autre : choisissez celui qui vous convient.',
        'legend' => 'Niveau de confidentialité',
        'pseudonym_title' => 'Si vous choisissez « Pseudonyme »',
        'pseudonym_help' => 'Ce nom d\'emprunt servira uniquement aux échanges dans votre dossier.',
        'pseudonym' => 'Pseudonyme',
        'identity_title' => 'Si vous choisissez « Confidentiel »',
        'identity_help' => 'Votre identité est conservée à part du contenu du signalement et visible uniquement par les personnes autorisées, chaque consultation étant enregistrée.',
        'identity_full_name' => 'Votre nom',
        'identity_email' => 'Adresse e-mail de contact',
        'identity_phone' => 'Numéro de téléphone de contact',
    ],

    'description' => [
        'lead' => 'Décrivez ce qui s\'est passé avec vos mots. Seule la description est nécessaire ; les autres questions sont facultatives.',
        'description' => 'Description des faits',
        'description_help' => 'Vous pouvez écrire librement. Il n\'y a pas de bonne ou de mauvaise façon de raconter.',
        'facts_date' => 'Date des faits',
        'facts_period' => 'Période des faits',
        'facts_period_help' => 'Si vous ne connaissez pas la date exacte, vous pouvez indiquer une période approximative.',
        'location' => 'Lieu',
        'is_recurring' => 'Les faits se sont-ils répétés ?',
        'is_ongoing' => 'La situation est-elle toujours en cours ?',
        'immediate_danger' => 'Êtes-vous, ou quelqu\'un est-il, en danger immédiat ?',
    ],

    'people' => [
        'lead' => 'Ces informations aident à traiter la situation. Vous pouvez les laisser vides.',
        'people_involved' => 'Personnes ou fonctions concernées',
        'people_involved_help' => 'Vous pouvez indiquer un nom, une fonction ou un service, ou ne rien indiquer.',
        'witnesses' => 'Témoins éventuels',
        'witnesses_help' => 'Personnes qui ont pu voir ou entendre les faits, si vous le souhaitez.',
    ],

    'attachments' => [
        'lead' => 'Vous pouvez joindre des éléments à votre signalement. Ce n\'est jamais obligatoire.',
        'soon_title' => 'Le dépôt de fichiers sera bientôt disponible',
        'soon_help' => 'Dans cette version, vous pouvez poursuivre sans pièce jointe. La personne en charge de votre dossier pourra vous proposer d\'en transmettre plus tard, dans l\'espace de suivi.',
        'metadata_title' => 'À savoir avant de joindre un fichier',
        'metadata_help' => 'Une photo ou un document peuvent contenir des informations cachées : date, lieu, appareil, nom de l\'auteur. Si vous souhaitez rester anonyme, vérifiez ces informations avant de transmettre un fichier.',
        'continue_without' => 'Poursuivre sans pièce jointe',
    ],

    'consent' => [
        'lead' => 'Dernière étape. Chaque accord est demandé séparément : lisez-les et cochez ceux que vous acceptez.',
        'summary_title' => 'Récapitulatif de vos choix',
        'summary_category' => 'Situation',
        'summary_level' => 'Niveau de confidentialité',
        'summary_danger' => 'Danger immédiat signalé',
        'summary_help' => 'Votre récit n\'est pas repris ici. Vous pouvez revenir aux étapes précédentes pour le relire ou le modifier.',
        'before_submit' => 'En transmettant, vous créez un dossier. Vous recevrez ensuite deux codes à conserver pour le suivre.',
        'submit' => 'Transmettre le signalement',
    ],

    'confirmation' => [
        'title' => 'Votre signalement a bien été déposé',
        'lead' => 'Merci pour votre confiance. Un dossier a été créé et sera pris en charge par une personne habilitée.',
        'codes_title' => 'Vos codes de suivi',
        'codes_help' => 'Conservez ces deux codes : ils sont le seul moyen de retrouver votre dossier et d\'échanger avec la personne qui le traite. Nous ne pouvons pas vous les renvoyer.',
        'reference' => 'Identifiant du dossier',
        'secret' => 'Secret de récupération',
        'copy' => 'Copier les codes',
        'copied' => 'Codes copiés',
        'download' => 'Télécharger (.txt)',
        'download_content' => "Sàmm - Codes de suivi de votre signalement\n\nIdentifiant du dossier : :reference\nSecret de récupération : :secret\n\nConservez ce fichier en lieu sûr. Ces codes ne peuvent pas être renvoyés.\nEspace de suivi : :url",
        'next_title' => 'Et maintenant ?',
        'next_help' => 'Vous pourrez consulter l\'état de votre dossier et répondre aux messages de la personne en charge depuis « Suivre mon dossier », avec vos deux codes.',
    ],

];
