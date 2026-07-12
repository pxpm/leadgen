<?php

return [
    'default_locale' => 'pt',
    'services' => [
        0 => 'electrical_emergency',
        1 => 'electrical_install',
        2 => 'rewiring',
        3 => 'solar_panels',
        4 => 'ev_charger',
    ],
    'shared_fields' => [
        'qualification' => [
            0 => 'property_type',
            1 => 'urgency',
        ],
        'contact' => [
            0 => 'contact_name',
            1 => 'phone',
            2 => 'email',
            3 => 'property_address',
            4 => 'postal_code',
        ],
    ],
    'field_definitions' => [
        'contact_name' => [
            'type' => 'text',
        ],
        'phone' => [
            'type' => 'text',
        ],
        'email' => [
            'type' => 'text',
        ],
        'property_address' => [
            'type' => 'text',
        ],
        'postal_code' => [
            'type' => 'text',
            'pattern' => '^\\d{4}-\\d{3}$',
        ],
        'notes' => [
            'type' => 'text',
        ],
        'urgency' => [
            'type' => 'select',
            'options' => [
                0 => 'emergency',
                1 => 'within_week',
                2 => 'within_month',
                3 => 'planning',
            ],
        ],
        'property_type' => [
            'type' => 'select',
            'options' => [
                0 => 'apartment',
                1 => 'house',
                2 => 'office',
                3 => 'commercial',
                4 => 'other',
            ],
        ],
    ],
    'locales' => [
        'pt' => [
            'ai_prompt' => [
                'greeting_message' => 'Olá! Estou aqui para ajudar com os seus serviços elétricos. Em que podemos ajudar?',
                'tone' => 'professional_friendly',
                'response_length' => 'concise',
            ],
            'field_prompts' => [
                'contact_name' => 'Qual é o seu nome?',
                'phone' => 'Qual é o melhor número de telefone para o contactar?',
                'email' => 'Qual é o seu email?',
                'property_address' => 'Qual é a morada da propriedade?',
                'postal_code' => 'Qual é o código postal?',
                'notes' => 'Há mais alguma informação que queira acrescentar?',
                'urgency' => 'Qual é a urgência deste serviço?',
                'property_type' => 'Que tipo de propriedade é?',
            ],
            'field_options' => [
                'urgency' => [
                    'emergency' => 'Emergência — preciso de ajuda agora',
                    'within_week' => 'Esta semana',
                    'within_month' => 'Este mês',
                    'planning' => 'Só a planear',
                ],
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'office' => 'Escritório',
                    'commercial' => 'Loja / Comercial',
                    'other' => 'Outro',
                ],
            ],
            'missed_call' => [
                'welcome_message' => 'Olá! Não pudemos atender a sua chamada. Como podemos ajudar?',
                'intents' => [
                    'budget' => 'Quero um orçamento',
                    'report' => 'Reportar uma avaria',
                    'other' => 'Outro assunto',
                ],
            ],
            'name' => 'Eletricidade',
        ],
    ],
];
