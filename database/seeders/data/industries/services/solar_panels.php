<?php

return [
    'key' => 'solar_panels',
    'icon' => '☀️',
    'required_fields' => [
        0 => 'solar_subtype',
        1 => 'roof_type',
        2 => 'monthly_bill',
    ],
    'optional_fields' => [
        0 => 'ownership',
        1 => 'battery_interest',
        2 => 'panel_count_estimate',
    ],
    'field_definitions' => [
        'solar_subtype' => [
            'type' => 'select',
            'options' => [
                0 => 'instalar_termico',
                1 => 'instalar_fotovoltaico',
                2 => 'manutencao',
                3 => 'reparacao',
                4 => 'outro',
            ],
        ],
        'roof_type' => [
            'type' => 'select',
            'options' => [
                0 => 'tile',
                1 => 'flat',
                2 => 'metal',
                3 => 'slate',
                4 => 'other',
            ],
        ],
        'monthly_bill' => [
            'type' => 'select',
            'options' => [
                0 => 'under_50',
                1 => '50_to_100',
                2 => '100_to_200',
                3 => 'over_200',
            ],
        ],
        'ownership' => [
            'type' => 'select',
            'options' => [
                0 => 'owner',
                1 => 'renter',
                2 => 'business',
            ],
        ],
        'battery_interest' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'not_sure',
            ],
        ],
        'panel_count_estimate' => [
            'type' => 'select',
            'options' => [
                0 => '4_to_6',
                1 => '8_to_12',
                2 => '14_or_more',
                3 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'solar_subtype' => 'Que tipo de serviço de painéis solares precisa? Instalação térmica, fotovoltaica, manutenção ou reparação?',
                'roof_type' => 'Que tipo de telhado tem? Telha, plano, metálico?',
                'monthly_bill' => 'Qual é o valor médio da sua fatura de eletricidade?',
                'ownership' => 'É proprietário do imóvel?',
                'battery_interest' => 'Tem interesse em baterias para armazenar energia?',
                'panel_count_estimate' => 'Tem ideia de quantos painéis precisa?',
            ],
            'field_options' => [
                'solar_subtype' => [
                    'instalar_termico' => 'Instalar painéis solares térmicos (aquecimento de água)',
                    'instalar_fotovoltaico' => 'Instalar painéis solares fotovoltaicos (eletricidade)',
                    'manutencao' => 'Manutenção de painéis solares',
                    'reparacao' => 'Reparação de painéis solares',
                    'outro' => 'Outro',
                ],
                'roof_type' => [
                    'tile' => 'Telha',
                    'flat' => 'Plano',
                    'metal' => 'Metálico',
                    'slate' => 'Ardósia',
                    'other' => 'Outro',
                ],
                'monthly_bill' => [
                    'under_50' => '< 50€',
                    '50_to_100' => '50-100€',
                    '100_to_200' => '100-200€',
                    'over_200' => '> 200€',
                ],
                'ownership' => [
                    'owner' => 'Proprietário',
                    'renter' => 'Inquilino',
                    'business' => 'Empresa',
                ],
                'battery_interest' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'not_sure' => 'Não sei',
                ],
                'panel_count_estimate' => [
                    '4_to_6' => '4-6',
                    '8_to_12' => '8-12',
                    '14_or_more' => '14+',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Painéis Solares',
            'keywords' => [
                0 => 'painéis solares',
                1 => 'paineis solares',
                2 => 'solar',
                3 => 'fotovoltaico',
                4 => 'autoconsumo',
                5 => 'bateria',
                6 => 'energia',
            ],
            'synonyms' => [
                'solar_subtype' => [
                    'instalar_termico' => [
                        0 => 'térmico',
                        1 => 'termico',
                        2 => 'aquecimento',
                        3 => 'água quente',
                        4 => 'aguas',
                        5 => 'painel térmico',
                    ],
                    'instalar_fotovoltaico' => [
                        0 => 'fotovoltaico',
                        1 => 'eletricidade',
                        2 => 'eletrico',
                        3 => 'elétrico',
                        4 => 'luz',
                        5 => 'autoconsumo',
                    ],
                    'manutencao' => [
                        0 => 'manutenção',
                        1 => 'manutencao',
                        2 => 'revisão',
                        3 => 'revisao',
                        4 => 'verificar',
                    ],
                    'reparacao' => [
                        0 => 'reparação',
                        1 => 'reparacao',
                        2 => 'reparar',
                        3 => 'arranjar',
                        4 => 'avariado',
                        5 => 'estragado',
                    ],
                ],
                'monthly_bill' => [
                    'under_50' => [
                        0 => 'pouco',
                        1 => '50',
                        2 => 'baixo',
                        3 => '40',
                        4 => '30',
                    ],
                    '50_to_100' => [
                        0 => 'médio',
                        1 => 'medio',
                        2 => '80',
                        3 => '70',
                        4 => '90',
                    ],
                    '100_to_200' => [
                        0 => 'alto',
                        1 => '150',
                        2 => '120',
                        3 => 'elevado',
                    ],
                    'over_200' => [
                        0 => 'muito alto',
                        1 => '200',
                        2 => '250',
                        3 => '300',
                        4 => 'gasto muito',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para serviços de painéis solares. Recolhe informações sobre o tipo de sistema (térmico ou fotovoltaico), telhado e consumo energético. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON. Valores:
- solar_subtype: instalar_termico, instalar_fotovoltaico, manutencao, reparacao, outro
- roof_type: tile, flat, metal, slate, other
- monthly_bill: under_50, 50_to_100, 100_to_200, over_200',
            ],
        ],
    ],
];
