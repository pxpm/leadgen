<?php

// Portuguese translations for the conversation orchestrator.
// These are seeded into translation_defaults and serve as the base.
// Tenants can override any key via tenant_translations.

return [
    'acknowledgment_variants' => [
        'Obrigado! Já registei.',
        'Entendido!',
        'Certo, tomei nota.',
        'Ok, registado.',
        'Perfeito, continue.',
        'Já anotei essa.',
        'Obrigado pela informação.',
        'Tudo bem, vamos prosseguir.',
    ],

    'name_acknowledgment_variants' => [
        'Obrigado, :name!',
        'Perfeito, :name!',
        'Certo, :name.',
        'Ok, :name!',
        'Entendido, :name.',
    ],

    'service_activation' => 'Perfeito! Vou ajudar com o serviço de :service. :question',
    'service_activation_name_first' => 'Vou ajudar-te com o serviço de :service. Vamos primeiro apresentar-nos, como te chamas?',
    'service_activation_multi_name_first' => 'Vou ajudar-te com os serviços selecionados. Vamos primeiro apresentar-nos, como te chamas?',
    'service_selection_prompt' => 'Olá! Em que podemos ajudar? Temos estes serviços: :services.',
    'service_transition' => 'Agora vamos falar sobre :service.',
    'summary_header' => 'Perfeito! Já tenho todos os dados para o seu orçamento de :service.',
    'summary_footer' => 'Está tudo correto? Quer acrescentar alguma nota adicional?',
    'default_greeting' => 'Como posso ajudar?',
    'need_more_info' => 'Pode dar-me mais informações?',

    // Validation-failure messages (when user input is rejected by validators)
    'invalid_email' => 'Isso não parece ser um email válido. :question',
    'invalid_phone' => 'Esse número não parece ser um número de telefone válido. :question',
    'invalid_field' => 'Isso não parece estar correto. :question',

    // Shown when user tries to skip a required field
    'field_required' => 'Este campo é obrigatório. :question',
];
