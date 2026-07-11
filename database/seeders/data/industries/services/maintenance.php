<?php

return [
    'key' => 'maintenance',
    'icon' => '🔧',
    'required_fields' => [
        0 => 'equipment_type',
        1 => 'last_service',
    ],
    'optional_fields' => [
        0 => 'unit_count',
        1 => 'contract_interest',
    ],
    'field_definitions' => [
        'equipment_type' => [
            'type' => 'select',
            'options' => [
                0 => 'ac_only',
                1 => 'heating_only',
                2 => 'both',
                3 => 'vmc',
                4 => 'other',
            ],
        ],
        'last_service' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_1y',
                1 => '1_to_2y',
                2 => 'over_2y',
                3 => 'never',
                4 => 'not_sure',
            ],
        ],
        'unit_count' => [
            'type' => 'select',
            'options' => [
                0 => '1',
                1 => '2_to_3',
                2 => '4_to_6',
                3 => '7_or_more',
            ],
        ],
        'contract_interest' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'maybe',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'equipment_type' => 'Que tipo de equipamento precisa de manutenção? AC, aquecimento, ambos?',
                'last_service' => 'Quando foi a última manutenção?',
                'unit_count' => 'Quantas unidades/interiores tem?',
                'contract_interest' => 'Tem interesse num contrato de manutenção anual?',
            ],
            'field_options' => [
                'equipment_type' => [
                    'ac_only' => 'Só AC',
                    'heating_only' => 'Só aquecimento',
                    'both' => 'Ambos',
                    'vmc' => 'VMC',
                    'other' => 'Outro',
                ],
                'last_service' => [
                    'less_than_1y' => '< 1 ano',
                    '1_to_2y' => '1-2 anos',
                    'over_2y' => '> 2 anos',
                    'never' => 'Nunca',
                    'not_sure' => 'Não sei',
                ],
                'unit_count' => [
                    1 => '1',
                    '2_to_3' => '2-3',
                    '4_to_6' => '4-6',
                    '7_or_more' => '7+',
                ],
                'contract_interest' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'maybe' => 'Talvez',
                ],
            ],
            'name' => 'Manutenção de Climatização',
            'keywords' => [
                0 => 'manutenção',
                1 => 'manutencao',
                2 => 'revisão',
                3 => 'revisao',
                4 => 'contrato',
                5 => 'limpeza ac',
                6 => 'check-up',
                7 => 'checkup',
            ],
            'synonyms' => [
                'last_service' => [
                    'less_than_1y' => [
                        0 => 'ano passado',
                        1 => 'recentemente',
                        2 => '6 meses',
                        3 => 'há pouco tempo',
                    ],
                    '1_to_2y' => [
                        0 => '1 ano',
                        1 => '2 anos',
                        2 => 'há um ano',
                        3 => 'há dois anos',
                    ],
                    'over_2y' => [
                        0 => 'muito tempo',
                        1 => 'anos',
                        2 => 'não me lembro',
                        3 => 'há muito',
                    ],
                    'never' => [
                        0 => 'nunca',
                        1 => 'primeira vez',
                        2 => 'nova',
                        3 => 'acabado de instalar',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para manutenção de sistemas de climatização. Recolhe informações sobre os equipamentos e histórico. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. NUNCA dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON:
- equipment_type: ac_only, heating_only, both, vmc, other
- last_service: less_than_1y, 1_to_2y, over_2y, never, not_sure
- unit_count: 1, 2_to_3, 4_to_6, 7_or_more
- contract_interest: yes, no, maybe',
            ],
        ],
    ],
];
