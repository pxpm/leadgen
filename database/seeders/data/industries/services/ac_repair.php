<?php

return [
    'key' => 'ac_repair',
    'icon' => '❄️',
    'required_fields' => [
        0 => 'ac_type',
        1 => 'problem_symptom',
    ],
    'optional_fields' => [
        0 => 'ac_brand',
        1 => 'unit_age',
    ],
    'field_definitions' => [
        'ac_type' => [
            'type' => 'select',
            'options' => [
                0 => 'split',
                1 => 'multi_split',
                2 => 'duct',
                3 => 'portable',
                4 => 'window',
                5 => 'other',
            ],
        ],
        'problem_symptom' => [
            'type' => 'select',
            'options' => [
                0 => 'not_cooling',
                1 => 'noisy',
                2 => 'leaking',
                3 => 'smell',
                4 => 'not_turning_on',
                5 => 'other',
            ],
        ],
        'ac_brand' => [
            'type' => 'text',
        ],
        'unit_age' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_2',
                1 => '2_to_5',
                2 => '5_to_10',
                3 => 'over_10',
                4 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [
    ],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'ac_type' => 'Que tipo de ar condicionado tem? Split, multi-split, conduta, portátil?',
                'problem_symptom' => 'O que se passa com o AC? Não arrefece, faz barulho, pinga água?',
                'ac_brand' => 'Sabe a marca do equipamento?',
                'unit_age' => 'Há quanto tempo tem o equipamento?',
            ],
            'field_options' => [
                'ac_type' => [
                    'split' => 'Split (mural)',
                    'multi_split' => 'Multi-split',
                    'duct' => 'Conduta',
                    'portable' => 'Portátil',
                    'window' => 'Janela',
                    'other' => 'Outro',
                ],
                'problem_symptom' => [
                    'not_cooling' => 'Não arrefece',
                    'noisy' => 'Faz barulho',
                    'leaking' => 'Pinga água',
                    'smell' => 'Mau cheiro',
                    'not_turning_on' => 'Não liga',
                    'other' => 'Outro',
                ],
                'unit_age' => [
                    'less_than_2' => '< 2 anos',
                    '2_to_5' => '2-5 anos',
                    '5_to_10' => '5-10 anos',
                    'over_10' => '> 10 anos',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Reparação de AC',
            'keywords' => [
                0 => 'ac',
                1 => 'ar condicionado',
                2 => 'avariado',
                3 => 'não arrefece',
                4 => 'nao arrefece',
                5 => 'quente',
                6 => 'fuga de gás',
                7 => 'fuga de gas',
            ],
            'synonyms' => [
                'ac_type' => [
                    'split' => [
                        0 => 'split',
                        1 => 'mural',
                        2 => 'parede',
                    ],
                    'multi_split' => [
                        0 => 'multi',
                        1 => 'multi split',
                        2 => 'várias unidades',
                        3 => 'varias unidades',
                    ],
                    'duct' => [
                        0 => 'conduta',
                        1 => 'duto',
                        2 => 'central',
                        3 => 'canalizado',
                    ],
                    'portable' => [
                        0 => 'portátil',
                        1 => 'portatil',
                        2 => 'móvel',
                        3 => 'movel',
                    ],
                    'window' => [
                        0 => 'janela',
                        1 => 'parede externa',
                    ],
                ],
                'problem_symptom' => [
                    'not_cooling' => [
                        0 => 'não arrefece',
                        1 => 'nao arrefece',
                        2 => 'quente',
                        3 => 'pouco frio',
                        4 => 'não gela',
                    ],
                    'noisy' => [
                        0 => 'barulho',
                        1 => 'ruído',
                        2 => 'ruido',
                        3 => 'barulhento',
                        4 => 'faz barulho',
                    ],
                    'leaking' => [
                        0 => 'pinga',
                        1 => 'água',
                        2 => 'agua',
                        3 => 'fuga',
                        4 => 'molhado',
                        5 => 'goteira',
                    ],
                    'smell' => [
                        0 => 'cheiro',
                        1 => 'mau cheiro',
                        2 => 'fedor',
                        3 => 'queimado',
                    ],
                    'not_turning_on' => [
                        0 => 'não liga',
                        1 => 'nao liga',
                        2 => 'morto',
                        3 => 'não funciona',
                        4 => 'nao funciona',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para reparação de ar condicionado. Recolhe informações sobre o equipamento e a avaria. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. NUNCA dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON:
- ac_type: split, multi_split, duct, portable, window, other
- problem_symptom: not_cooling, noisy, leaking, smell, not_turning_on, other
- ac_brand: texto livre
- unit_age: less_than_2, 2_to_5, 5_to_10, over_10, not_sure
- urgency: emergency, within_week, within_month',
            ],
        ],
    ],
];
