<?php

return [

    'progress' => 'Progress',
    'step_of' => 'Step :current of :total - :label',
    'continue' => 'Continue',
    'back' => 'Back',
    'abandon' => 'Abandon this report',
    'abandoned' => 'Your draft has been erased. Nothing was sent.',
    'back_home' => 'Back to home',
    'optional' => '(optional)',
    'yes' => 'Yes',
    'no' => 'No',
    'unknown' => 'I don\'t know',

    'steps' => [
        'situation' => 'Situation',
        'confidentialite' => 'Confidentiality level',
        'description' => 'Description',
        'personnes' => 'People involved',
        'pieces-jointes' => 'Attachments',
        'consentement' => 'Consent',
    ],

    'errors' => [
        'title' => 'Some answers need a second look',
        'help' => 'The fields concerned are marked below. Your answers have been kept.',
        'pseudonym_required' => 'Choose a pseudonym for this confidentiality level.',
        'name_required' => 'Enter your name for this confidentiality level, or choose another level.',
        'processing_consent_required' => 'Your agreement to the handling of the report is required to submit.',
        'identity_consent_required' => 'For the Confidential level, the agreement on access to your identity is required. Otherwise, go back and choose another level.',
    ],

    'start' => [
        'title' => 'Make a report',
        'lead' => 'You will describe a situation in a few short steps. You can go back at any time, and nothing is sent before the last step.',
        'steps_title' => 'The six steps',
        'step_help' => [
            'situation' => 'You choose, from the list offered, the situation that fits best.',
            'confidentialite' => 'Anonymous, pseudonym or confidential: you decide what is known about you.',
            'description' => 'You describe what happened in your own words. Dates, place and context are optional.',
            'personnes' => 'The people involved and any witnesses, only if you wish to name them.',
            'pieces-jointes' => 'Documents, images or screenshots, only if you have any.',
            'consentement' => 'You review and confirm the submission. Nothing is sent before that.',
        ],
        'collected_title' => 'What we collect',
        'collected_1' => 'The kind of situation and your description, with the details you choose to give.',
        'collected_2' => 'The confidentiality level you choose, and a pseudonym or your identity only if you decide so.',
        'collected_3' => 'The interface language and the submission date.',
        'not_collected_title' => 'What we do not collect',
        'not_collected_1' => 'Your IP address, device or browser: no technical trace is linked to your report.',
        'not_collected_2' => 'No account, no mandatory email address.',
        'limits' => 'The content of your account, the files you attach or the context may still make you recognizable. You choose what you write.',
        'session_title' => 'Your answers are kept during the session',
        'session_help' => 'After two hours of inactivity, the draft is erased. The "Leave quickly" button redirects you to another page without sending anything.',
        'begin' => 'Begin',
        'resume' => 'Resume my report',
    ],

    'situation' => [
        'lead' => 'Choose the situation that fits best. You will be able to give details afterwards.',
        'legend' => 'Kind of situation',
    ],

    'confidentiality' => [
        'lead' => 'Each level is explained. None is better than another: choose the one that suits you.',
        'legend' => 'Confidentiality level',
        'pseudonym_title' => 'If you choose "Pseudonym"',
        'pseudonym_help' => 'This alias will only be used for exchanges in your case.',
        'pseudonym' => 'Pseudonym',
        'identity_title' => 'If you choose "Confidential"',
        'identity_help' => 'Your identity is stored apart from the report content and visible only to authorized people, each access being recorded.',
        'identity_full_name' => 'Your name',
        'identity_email' => 'Contact email address',
        'identity_phone' => 'Contact phone number',
    ],

    'description' => [
        'lead' => 'Describe what happened in your own words. Only the description is needed; the other questions are optional.',
        'description' => 'Description of the facts',
        'description_help' => 'You can write freely. There is no right or wrong way to tell it.',
        'facts_date' => 'Date of the facts',
        'facts_period' => 'Period of the facts',
        'facts_period_help' => 'If you do not know the exact date, you can give an approximate period.',
        'location' => 'Place',
        'is_recurring' => 'Did the facts happen repeatedly?',
        'is_ongoing' => 'Is the situation still ongoing?',
        'immediate_danger' => 'Are you, or is someone, in immediate danger?',
    ],

    'people' => [
        'lead' => 'This information helps to handle the situation. You may leave it empty.',
        'people_involved' => 'People or positions involved',
        'people_involved_help' => 'You may give a name, a position or a department, or nothing at all.',
        'witnesses' => 'Possible witnesses',
        'witnesses_help' => 'People who may have seen or heard the facts, if you wish.',
    ],

    'attachments' => [
        'lead' => 'You may attach elements to your report. It is never mandatory.',
        'soon_title' => 'File upload will be available soon',
        'soon_help' => 'In this version, you can continue without an attachment. The person in charge of your case may offer you to send some later, in the tracking space.',
        'metadata_title' => 'Before attaching a file',
        'metadata_help' => 'A photo or a document may contain hidden information: date, place, device, author name. If you wish to stay anonymous, check this information before sending a file.',
        'continue_without' => 'Continue without an attachment',
    ],

    'consent' => [
        'lead' => 'Last step. Each agreement is asked separately: read them and tick the ones you accept.',
        'summary_title' => 'Summary of your choices',
        'summary_category' => 'Situation',
        'summary_level' => 'Confidentiality level',
        'summary_danger' => 'Immediate danger reported',
        'summary_help' => 'Your account is not repeated here. You can go back to the previous steps to read or change it.',
        'before_submit' => 'By submitting, you create a case. You will then receive two codes to keep in order to follow it.',
        'submit' => 'Submit the report',
    ],

    'confirmation' => [
        'title' => 'Your report has been submitted',
        'lead' => 'Thank you for your trust. A case has been created and will be handled by an authorized person.',
        'codes_title' => 'Your tracking codes',
        'codes_help' => 'Keep these two codes: they are the only way to find your case again and to exchange with the person handling it. We cannot send them to you again.',
        'reference' => 'Case identifier',
        'secret' => 'Recovery secret',
        'copy' => 'Copy the codes',
        'copied' => 'Codes copied',
        'download' => 'Download (.txt)',
        'download_content' => "Sàmm - Tracking codes of your report\n\nCase identifier: :reference\nRecovery secret: :secret\n\nKeep this file in a safe place. These codes cannot be sent again.\nTracking space: :url",
        'next_title' => 'What now?',
        'next_help' => 'You will be able to check the state of your case and reply to the person in charge from "Follow my case", with your two codes.',
    ],

];
