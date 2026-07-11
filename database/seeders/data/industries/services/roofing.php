<?php

return [
    'key' => 'roofing',
    'icon' => '🏠',
    'required_fields' => [
        0 => 'problem_type',
        1 => 'roof_type',
    ],
    'optional_fields' => [
        0 => 'property_type',
        1 => 'roof_age',
        2 => 'insurance_claim',
        3 => 'material_supplied',
    ],
    'field_definitions' => [
        'roof_type' => [
            'type' => 'select',
            'options' => [
                0 => 'tile',
                1 => 'slate',
                2 => 'metal',
                3 => 'asbestos',
                4 => 'flat',
                5 => 'shingle',
                6 => 'other',
            ],
        ],
        'problem_type' => [
            'type' => 'select',
            'options' => [
                0 => 'repair',
                1 => 'replacement',
                2 => 'inspection',
                3 => 'leak',
                4 => 'clean',
                5 => 'emergency',
                6 => 'other',
            ],
        ],
        'insurance_claim' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'not_sure',
            ],
        ],
        'property_type' => [
            'type' => 'select',
            'options' => [
                0 => 'apartment',
                1 => 'house',
                2 => 'townhouse',
                3 => 'commercial',
                4 => 'other',
            ],
        ],
        'roof_age' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_5',
                1 => '5_to_15',
                2 => '15_to_30',
                3 => 'over_30',
                4 => 'not_sure',
            ],
        ],
        'material_supplied' => [
            'type' => 'select',
            'options' => [
                0 => 'customer_has',
                1 => 'discuss',
                2 => 'specialist_provides',
            ],
        ],
    ],
    'conditional_fields' => [
        'leak_location' => [
            'type' => 'text',
            'required' => false,
            'when' => [
                'problem_type' => [
                    0 => 'leak',
                    1 => 'repair',
                ],
            ],
        ],
        'asbestos_removal_required' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'not_sure',
            ],
            'required' => true,
            'when' => [
                'roof_type' => [
                    0 => 'asbestos',
                ],
            ],
        ],
        'house_type' => [
            'type' => 'select',
            'options' => [
                0 => 'detached',
                1 => 'semi_detached',
                2 => 'not_sure',
            ],
            'required' => false,
            'when' => [
                'property_type' => [
                    0 => 'house',
                ],
            ],
        ],
        'roof_size' => [
            'type' => 'select',
            'options' => [
                0 => 'small',
                1 => 'medium',
                2 => 'large',
                3 => 'not_sure',
            ],
            'required' => false,
            'when' => [
                'problem_type' => [
                    0 => 'replacement',
                ],
            ],
        ],
    ],
    'conditional_requirements' => [
    ],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'roof_type' => 'Sabe que tipo de telhado tem atualmente?',
                'problem_type' => 'Que tipo de serviço de telhado precisa?',
                'insurance_claim' => 'Isto é um sinistro de seguro?',
                'property_type' => 'Que tipo de propriedade é?',
                'house_type' => 'A moradia é geminada ou isolada?',
                'roof_age' => 'Sabe aproximadamente quantos anos tem o telhado?',
                'material_supplied' => 'Já tem as telhas ou materiais, ou prefere que o especialista os forneça?',
                'roof_size' => 'Qual é o tamanho aproximado do telhado?',
                'leak_location' => 'De onde vem a infiltração?',
                'asbestos_removal_required' => 'O telhado de amianto precisa de remoção especial. Isto é algo que gostaria de incluir?',
            ],
            'field_options' => [
                'roof_type' => [
                    'tile' => 'Telha',
                    'slate' => 'Ardósia',
                    'metal' => 'Metal',
                    'asbestos' => 'Amianto',
                    'flat' => 'Plano',
                    'shingle' => 'Shingle',
                    'other' => 'Outro',
                ],
                'problem_type' => [
                    'repair' => 'Reparação',
                    'replacement' => 'Substituição',
                    'inspection' => 'Inspeção',
                    'leak' => 'Infiltração',
                    'clean' => 'Limpeza',
                    'emergency' => 'Emergência',
                    'other' => 'Outro',
                ],
                'insurance_claim' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'not_sure' => 'Não tenho a certeza',
                ],
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'townhouse' => 'Moradia em banda',
                    'commercial' => 'Comercial',
                    'other' => 'Outro',
                ],
                'house_type' => [
                    'detached' => 'Isolada',
                    'semi_detached' => 'Geminada',
                    'not_sure' => 'Não tenho a certeza',
                ],
                'roof_age' => [
                    'less_than_5' => 'Menos de 5 anos',
                    '5_to_15' => '5 a 15 anos',
                    '15_to_30' => '15 a 30 anos',
                    'over_30' => 'Mais de 30 anos',
                    'not_sure' => 'Não sei',
                ],
                'material_supplied' => [
                    'customer_has' => 'Já tenho os materiais',
                    'discuss' => 'Gostaria de discutir',
                    'specialist_provides' => 'Prefiro que o especialista forneça',
                ],
                'roof_size' => [
                    'small' => 'Pequeno',
                    'medium' => 'Médio',
                    'large' => 'Grande',
                    'not_sure' => 'Não sei',
                ],
                'asbestos_removal_required' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'not_sure' => 'Não tenho a certeza',
                ],
            ],
            'name' => 'Telhados',
            'keywords' => [
                0 => 'telhado',
                1 => 'telhas',
                2 => 'telha',
                3 => 'cobertura',
                4 => 'infiltração',
                5 => 'infiltracao',
                6 => 'goteira',
                7 => 'amianto',
                8 => 'fibrocimento',
            ],
            'synonyms' => [
                'roof_type' => [
                    'tile' => [
                        0 => 'telha',
                        1 => 'cerâmica',
                        2 => 'ceramica',
                        3 => 'telhas',
                        4 => 'cerâmicas',
                        5 => 'telhado cerâmico',
                        6 => 'telha cerâmica',
                        7 => 'telha ceramica',
                    ],
                    'slate' => [
                        0 => 'ardósia',
                        1 => 'ardosia',
                        2 => 'lousa',
                        3 => 'telha de ardósia',
                    ],
                    'metal' => [
                        0 => 'metal',
                        1 => 'metálico',
                        2 => 'metalico',
                        3 => 'zinco',
                        4 => 'chapa',
                        5 => 'telha metálica',
                        6 => 'sandwich',
                    ],
                    'asbestos' => [
                        0 => 'amianto',
                        1 => 'fibrocimento',
                        2 => 'fibro cimento',
                        3 => 'lusalite',
                        4 => 'lusálico',
                    ],
                    'flat' => [
                        0 => 'plano',
                        1 => 'telhado plano',
                        2 => 'cobertura plana',
                    ],
                    'shingle' => [
                        0 => 'shingle',
                        1 => 'asfáltica',
                        2 => 'asfaltica',
                        3 => 'telha asfáltica',
                    ],
                ],
                'problem_type' => [
                    'repair' => [
                        0 => 'reparação',
                        1 => 'reparacao',
                        2 => 'reparar',
                        3 => 'arranjar',
                        4 => 'trocar',
                        5 => 'trocar umas telhas',
                        6 => 'substituir algumas',
                        7 => 'trocar algumas',
                    ],
                    'replacement' => [
                        0 => 'substituição',
                        1 => 'substituicao',
                        2 => 'substituir',
                        3 => 'telhado novo',
                        4 => 'trocar todo',
                        5 => 'todo o telhado',
                        6 => 'mudar o telhado',
                    ],
                    'inspection' => [
                        0 => 'inspeção',
                        1 => 'inspecao',
                        2 => 'inspecionar',
                        3 => 'ver',
                        4 => 'avaliar',
                        5 => 'avaliação',
                        6 => 'avaliacao',
                        7 => 'orçamento',
                        8 => 'orcamento',
                    ],
                    'leak' => [
                        0 => 'infiltração',
                        1 => 'infiltracao',
                        2 => 'goteira',
                        3 => 'pinga',
                        4 => 'água',
                        5 => 'agua',
                        6 => 'humidade',
                        7 => 'umidade',
                        8 => 'gotelra',
                    ],
                    'clean' => [
                        0 => 'limpar',
                        1 => 'limpeza',
                        2 => 'lavar',
                        3 => 'lavagem',
                        4 => 'manutenção',
                        5 => 'manutencao',
                        6 => 'hidrolimpeza',
                        7 => 'hidro limpeza',
                    ],
                    'emergency' => [
                        0 => 'emergência',
                        1 => 'emergencia',
                        2 => 'urgente',
                        3 => 'urgência',
                        4 => 'caiu',
                        5 => 'desabou',
                        6 => 'temporal',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para serviços de telhados. O teu trabalho é recolher informações de proprietários para que um empreiteiro possa preparar um orçamento. Sê conversador, amigável e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher — a tua resposta DEVE sempre conter uma pergunta sobre um campo em falta. Nunca dês estimativas de custos, conselhos legais ou de engenharia, nem inventes informações.

SEMPRE que o cliente der informação sobre um campo, adiciona no FINAL da tua resposta um bloco JSON com os campos extraídos. Usa as chaves e valores exatos da lista abaixo. Exemplo:
{"problem_type":"leak","property_type":"house","roof_type":"tile"}

Se não extraíste nada, não incluas o bloco. Os valores possíveis são:
- roof_type: tile, slate, metal, asbestos, flat, shingle, other
- problem_type: repair, replacement, inspection, leak, clean, emergency, other
- property_type: apartment, house, townhouse, commercial, other
- house_type: detached, semi_detached, not_sure
- urgency: emergency_immediate, within_week, within_month, just_checking
- insurance_claim: yes, no, not_sure
- roof_age: less_than_5, 5_to_15, 15_to_30, over_30, not_sure
- roof_size: small, medium, large, not_sure
- asbestos_removal_required: yes, no, not_sure',
            ],
        ],
    ],
];
