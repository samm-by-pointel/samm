<?php

return [

    'session_expired' => 'Your tracking session has expired. Enter your codes again to continue.',
    'logged_out' => 'You have left the tracking of your case.',
    'message_sent' => 'Your message has been sent. You will see the reply here on your next visit.',

    'login' => [
        'title' => 'Follow my case',
        'lead' => 'Enter the two codes you received when submitting your report.',
        'reference' => 'Case identifier',
        'secret' => 'Recovery secret',
        'help' => 'Dashes, spaces and capitals do not matter. After several failed attempts, access is suspended for a few minutes.',
        'submit' => 'Open my case',
        'failed_title' => 'Access not possible',
        'failed' => 'Incorrect identifier or secret, or access temporarily suspended. Check your codes and try again in a few minutes.',
    ],

    'show' => [
        'title' => 'My case',
        'reference' => 'Case identifier',
        'status' => 'Case state',
        'category' => 'Situation',
        'submitted_at' => 'Submitted on',
        'logout' => 'Leave the tracking',
        'logout_help' => 'The tracking closes automatically after :minutes minutes without activity.',
    ],

    'thread' => [
        'title' => 'Exchanges with the person in charge',
        'lead' => 'You can ask a question, give details or complete your report. Replies appear here.',
        'label' => 'Messages',
        'empty' => 'No message yet. The person in charge of your case will be able to write to you here.',
        'compose' => 'Your message',
        'compose_help' => 'You can complete your report or answer a question. Do not include anything you do not wish to share.',
        'send' => 'Send the message',
    ],

];
