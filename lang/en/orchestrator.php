<?php

// English translations for the conversation orchestrator.
// These are seeded into translation_defaults and serve as the base.
// Tenants can override any key via tenant_translations.

return [
    'acknowledgment_variants' => [
        'Thanks! Noted.',
        'Got it!',
        'Okay, I\'ve recorded that.',
        'All right, noted.',
        'Perfect, carry on.',
        'I\'ve noted that down.',
        'Thanks for the information.',
        'Great, let\'s continue.',
    ],

    'name_acknowledgment_variants' => [
        'Thanks, :name!',
        'Perfect, :name!',
        'Got it, :name.',
        'Okay, :name!',
        'Understood, :name.',
    ],

    'service_activation' => 'Great! I\'ll help with the :service service. :question',
    'service_activation_name_first' => 'I\'ll help you with the :service service. First, let me get to know you — what\'s your name?',
    'service_activation_multi_name_first' => 'I\'ll help you with the selected services. First, let me get to know you — what\'s your name?',
    'service_selection_prompt' => 'Hello! How can we help? We offer these services: :services.',
    'service_transition' => 'Now let\'s talk about :service.',
    'summary_header' => 'Great! I have all the information needed for your :service estimate.',
    'summary_footer' => 'Is everything correct? Would you like to add any additional notes?',
    'default_greeting' => 'How can I help?',
    'need_more_info' => 'Can you give me more information?',

    // Validation-failure messages (when user input is rejected by validators)
    'invalid_email' => 'That doesn\'t look like a valid email. :question',
    'invalid_phone' => 'That doesn\'t look like a valid phone number. :question',
    'invalid_field' => 'That doesn\'t seem correct. :question',

    // Shown when user tries to skip a required field
    'field_required' => 'This field is required. :question',
];
