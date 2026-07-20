<?php

return [
    'default_locale' => 'pt',
    'services' => [
        0 => 'roofing',
        1 => 'waterproofing',
        2 => 'painting',
        3 => 'insulation',
        4 => 'facades',
        5 => 'terraces',
        6 => 'gutters',
        7 => 'remodeling',
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
        'photos' => [
            'type' => 'file',
        ],
        'documents' => [
            'type' => 'file',
        ],
    ],
    'locales' => [
        'pt' => [
            'ai_prompt' => [
                'greeting_message' => 'Olá! Estou aqui para ajudar com o seu projeto. Em que podemos ajudar?',
                'tone' => 'professional_friendly',
                'response_length' => 'concise',
            ],
            'orchestration' => [
                'service_activation' => 'Perfeito! Vou ajudar com o serviço de :service. :question',
                'postal_code_question' => 'E qual é o código postal dessa morada?',
                'service_selection_prompt' => 'Olá! Em que podemos ajudar? Temos estes serviços: :services.',
                'service_transition' => 'Agora vamos falar sobre :service.',
                'summary_header' => 'Perfeito! Já tenho todos os dados para o seu orçamento de :service.',
                'summary_footer' => 'Está tudo correto? Quer acrescentar alguma nota adicional?',
            ],
            'name_acknowledgment_variants' => [
                'Obrigado, :name!',
                'Perfeito, :name!',
                'Certo, :name.',
                'Ok, :name!',
                'Entendido, :name.',
            ],
            'acknowledgment_variants' => [
                'Obrigado! Já registei.',
                'Entendido!',
                'Certo, tomei nota.',
                'Ok, registado.',
                'Perfeito, continue.',
                'Já anotei essa.',
                'Obrigado pela informação.',
                'Tudo bem, siga.',
            ],
            'field_prompts' => [
                'contact_name' => 'Qual é o seu nome?',
                'phone' => 'Qual é o melhor número de telefone para o contactar?',
                'email' => 'Qual é o seu email?',
                'property_address' => 'Qual é a morada da propriedade?',
                'postal_code' => 'Qual é o código postal?',
                'notes' => 'Há mais alguma informação ou nota adicional que queira acrescentar?',
                'urgency' => 'Qual é a urgência deste serviço?',
                'property_type' => 'Que tipo de propriedade é?',
                'photos' => 'Pode enviar algumas fotos do local? Isso ajuda-nos a preparar um orçamento mais preciso.',
                'documents' => 'Tem algum documento, projeto ou especificação que possa partilhar? (PDF, Word, Excel)',
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
                    'report' => 'Reportar um problema',
                    'other' => 'Outro assunto',
                ],
            ],
            'name' => 'Construção Civil',
        ],
    ],
];
