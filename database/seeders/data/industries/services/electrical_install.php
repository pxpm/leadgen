<?php

return [
    'key' => 'electrical_install',
    'icon' => '💡',
    'required_fields' => [
        0 => 'install_type',
        1 => 'property_type',
    ],
    'optional_fields' => [
        0 => 'room_count',
        1 => 'timeline',
        2 => 'certification_required',
    ],
    'field_definitions' => [
        'install_type' => [
            'type' => 'select',
            'options' => [
                0 => 'lights',
                1 => 'outlets',
                2 => 'switches',
                3 => 'panel_upgrade',
                4 => 'home_automation',
                5 => 'other',
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
        'room_count' => [
            'type' => 'select',
            'options' => [
                0 => '1',
                1 => '2_to_3',
                2 => '4_to_6',
                3 => '7_or_more',
            ],
        ],
        'timeline' => [
            'type' => 'select',
            'options' => [
                0 => 'urgent',
                1 => 'within_week',
                2 => 'within_month',
                3 => 'planning',
            ],
        ],
        'certification_required' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'unsure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'install_type' => 'O que precisa instalar? Luzes, tomadas, quadro elétrico, domótica?',
                'property_type' => 'Que tipo de propriedade é?',
                'room_count' => 'Em quantas divisões?',
                'timeline' => 'Para quando precisa do trabalho feito?',
                'certification_required' => 'Precisa de certificado da instalação elétrica?',
            ],
            'field_options' => [
                'install_type' => [
                    'lights' => 'Luzes/Iluminação',
                    'outlets' => 'Tomadas',
                    'switches' => 'Interruptores',
                    'panel_upgrade' => 'Quadro elétrico',
                    'home_automation' => 'Domótica',
                    'other' => 'Outro',
                ],
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'office' => 'Escritório',
                    'commercial' => 'Comercial',
                    'other' => 'Outro',
                ],
                'room_count' => [
                    1 => '1',
                    '2_to_3' => '2-3',
                    '4_to_6' => '4-6',
                    '7_or_more' => '7+',
                ],
                'timeline' => [
                    'urgent' => 'Urgente',
                    'within_week' => 'Esta semana',
                    'within_month' => 'Este mês',
                    'planning' => 'Só a planear',
                ],
                'certification_required' => [
                    'yes' => 'Sim, preciso de certificado',
                    'no' => 'Não é necessário',
                    'unsure' => 'Não tenho a certeza',
                ],
            ],
            'name' => 'Instalações Elétricas',
            'keywords' => [
                0 => 'instalação elétrica',
                1 => 'instalacao eletrica',
                2 => 'tomadas',
                3 => 'luzes',
                4 => 'lustre',
                5 => 'focos',
                6 => 'led',
                7 => 'projetor',
            ],
            'synonyms' => [
                'install_type' => [
                    'lights' => [
                        0 => 'luz',
                        1 => 'lustre',
                        2 => 'candeeiro',
                        3 => 'foco',
                        4 => 'led',
                        5 => 'iluminação',
                        6 => 'iluminacao',
                        7 => 'projetor',
                    ],
                    'outlets' => [
                        0 => 'tomada',
                        1 => 'tomadas',
                        2 => 'ficha',
                        3 => 'pontos',
                    ],
                    'switches' => [
                        0 => 'interruptor',
                        1 => 'interruptores',
                        2 => 'comutador',
                    ],
                    'panel_upgrade' => [
                        0 => 'quadro',
                        1 => 'potência',
                        2 => 'potencia',
                        3 => 'aumentar',
                        4 => 'disjuntor',
                    ],
                    'home_automation' => [
                        0 => 'domótica',
                        1 => 'domotica',
                        2 => 'inteligente',
                        3 => 'smart',
                        4 => 'automação',
                        5 => 'automacao',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para instalações elétricas. Recolhe informações sobre o tipo de trabalho. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
