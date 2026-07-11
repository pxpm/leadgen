<?php

return [
    'key' => 'ac_install',
    'icon' => '🌬️',
    'required_fields' => [
        0 => 'property_type',
        1 => 'room_count',
    ],
    'optional_fields' => [
        0 => 'has_ductwork',
        1 => 'budget_range',
    ],
    'field_definitions' => [
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
                1 => '2',
                2 => '3',
                3 => '4',
                4 => '5_or_more',
            ],
        ],
        'has_ductwork' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'not_sure',
            ],
        ],
        'budget_range' => [
            'type' => 'select',
            'options' => [
                0 => 'economy',
                1 => 'mid_range',
                2 => 'premium',
                3 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'property_type' => 'Que tipo de propriedade é? Apartamento, moradia, escritório?',
                'room_count' => 'Em quantas divisões pretende instalar ar condicionado?',
                'has_ductwork' => 'Já tem sistema de condutas instalado?',
                'budget_range' => 'Que gama de orçamento tem em mente?',
            ],
            'field_options' => [
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'office' => 'Escritório',
                    'commercial' => 'Comercial',
                    'other' => 'Outro',
                ],
                'room_count' => [
                    1 => '1 divisão',
                    2 => '2 divisões',
                    3 => '3 divisões',
                    4 => '4 divisões',
                    '5_or_more' => '5+ divisões',
                ],
                'has_ductwork' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'not_sure' => 'Não sei',
                ],
                'budget_range' => [
                    'economy' => 'Económico',
                    'mid_range' => 'Médio',
                    'premium' => 'Premium',
                    'not_sure' => 'A decidir',
                ],
            ],
            'name' => 'Instalação de AC',
            'keywords' => [
                0 => 'instalar ac',
                1 => 'instalar ar condicionado',
                2 => 'meto ar condicionado',
                3 => 'quero ac',
                4 => 'orçamento ac',
            ],
            'synonyms' => [
                'property_type' => [
                    'apartment' => [
                        0 => 'apartamento',
                        1 => 'ap',
                        2 => 'andar',
                        3 => 'condomínio',
                        4 => 'condominio',
                    ],
                    'house' => [
                        0 => 'moradia',
                        1 => 'casa',
                        2 => 'vivenda',
                    ],
                    'office' => [
                        0 => 'escritório',
                        1 => 'escritorio',
                        2 => 'empresa',
                    ],
                    'commercial' => [
                        0 => 'loja',
                        1 => 'restaurante',
                        2 => 'espaço comercial',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para instalação de ar condicionado. Recolhe informações sobre o espaço e necessidades. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. NUNCA dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON:
- property_type: apartment, house, office, commercial, other
- room_count: 1, 2, 3, 4, 5_or_more
- has_ductwork: yes, no, not_sure
- budget_range: economy, mid_range, premium, not_sure',
            ],
        ],
    ],
];
