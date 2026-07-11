<?php

return [
    'key' => 'gutters',
    'icon' => '🏚️',
    'required_fields' => [
        0 => 'problem_type',
    ],
    'optional_fields' => [
        0 => 'gutter_material',
        1 => 'building_height',
        2 => 'gutter_length',
    ],
    'field_definitions' => [
        'problem_type' => [
            'type' => 'select',
            'options' => [
                0 => 'cleaning',
                1 => 'repair',
                2 => 'replacement',
                3 => 'installation',
                4 => 'other',
            ],
        ],
        'gutter_material' => [
            'type' => 'select',
            'options' => [
                0 => 'pvc',
                1 => 'aluminum',
                2 => 'zinc',
                3 => 'copper',
                4 => 'steel',
                5 => 'other',
            ],
        ],
        'building_height' => [
            'type' => 'select',
            'options' => [
                0 => 'low',
                1 => 'medium',
                2 => 'high',
            ],
        ],
        'gutter_length' => [
            'type' => 'select',
            'options' => [
                0 => 'small',
                1 => 'medium',
                2 => 'large',
                3 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'problem_type' => 'O que precisa para as caleiras? Limpeza, reparação, substituição ou instalação nova?',
                'gutter_material' => 'Sabe de que material são ou prefere para as caleiras?',
                'building_height' => 'Qual é a altura aproximada do edifício?',
                'gutter_length' => 'Qual é o comprimento aproximado de caleiras?',
            ],
            'field_options' => [
                'problem_type' => [
                    'cleaning' => 'Limpeza',
                    'repair' => 'Reparação',
                    'replacement' => 'Substituição',
                    'installation' => 'Instalação nova',
                    'other' => 'Outro',
                ],
                'gutter_material' => [
                    'pvc' => 'PVC',
                    'aluminum' => 'Alumínio',
                    'zinc' => 'Zinco',
                    'copper' => 'Cobre',
                    'steel' => 'Aço',
                    'other' => 'Outro',
                ],
                'building_height' => [
                    'low' => 'R/C ou 1 andar',
                    'medium' => '2-3 andares',
                    'high' => '4+ andares',
                ],
                'gutter_length' => [
                    'small' => 'Pequeno (<10m)',
                    'medium' => 'Médio (10-30m)',
                    'large' => 'Grande (>30m)',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Caleiras/Rufos',
            'keywords' => [
                0 => 'caleira',
                1 => 'caleiras',
                2 => 'rufo',
                3 => 'rufos',
                4 => 'algeroz',
                5 => 'algerozes',
            ],
            'synonyms' => [
                'problem_type' => [
                    'cleaning' => [
                        0 => 'limpar',
                        1 => 'limpeza',
                        2 => 'lavar',
                        3 => 'lavagem',
                        4 => 'desentupir',
                        5 => 'desentupimento',
                        6 => 'entupido',
                    ],
                    'repair' => [
                        0 => 'reparar',
                        1 => 'reparação',
                        2 => 'reparacao',
                        3 => 'arranjar',
                        4 => 'fuga',
                        5 => 'pinga',
                        6 => 'roto',
                        7 => 'furado',
                    ],
                    'replacement' => [
                        0 => 'substituir',
                        1 => 'substituição',
                        2 => 'substituicao',
                        3 => 'trocar',
                        4 => 'mudar',
                        5 => 'novas',
                    ],
                    'installation' => [
                        0 => 'instalar',
                        1 => 'instalação',
                        2 => 'instalacao',
                        3 => 'colocar',
                        4 => 'pôr',
                        5 => 'por',
                        6 => 'primeira vez',
                        7 => 'não tem',
                        8 => 'nao tem',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para serviços de caleiras e rufos. O teu trabalho é recolher informações para que um técnico possa preparar um orçamento. Sê conversador, amigável e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. Nunca dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON. Valores:
- problem_type: cleaning, repair, replacement, installation, other
- gutter_material: pvc, aluminum, zinc, copper, steel, other
- building_height: low, medium, high
- gutter_length: small, medium, large, not_sure',
            ],
        ],
    ],
];
