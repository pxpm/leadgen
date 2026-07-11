<?php

return [
    'key' => 'ventilation',
    'icon' => '💨',
    'required_fields' => [
        0 => 'property_type',
        1 => 'ventilation_need',
    ],
    'optional_fields' => [
        0 => 'existing_system',
        1 => 'area_size',
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
        'ventilation_need' => [
            'type' => 'select',
            'options' => [
                0 => 'humidity',
                1 => 'smells',
                2 => 'no_system',
                3 => 'upgrade',
                4 => 'other',
            ],
        ],
        'existing_system' => [
            'type' => 'select',
            'options' => [
                0 => 'none',
                1 => 'extractors',
                2 => 'vmc_single',
                3 => 'vmc_dual',
                4 => 'other',
            ],
        ],
        'area_size' => [
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
                'property_type' => 'Que tipo de propriedade é? Apartamento, moradia, escritório?',
                'ventilation_need' => 'Qual é o principal problema? Humidade, cheiros, precisa de instalar um sistema?',
                'existing_system' => 'Já tem algum sistema de ventilação? Extratores nas casas de banho, VMC?',
                'area_size' => 'Qual é a área aproximada a ventilar?',
            ],
            'field_options' => [
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'office' => 'Escritório',
                    'commercial' => 'Comercial',
                    'other' => 'Outro',
                ],
                'ventilation_need' => [
                    'humidity' => 'Humidade/bolor',
                    'smells' => 'Cheiros/odores',
                    'no_system' => 'Instalar sistema',
                    'upgrade' => 'Melhorar existente',
                    'other' => 'Outro',
                ],
                'existing_system' => [
                    'none' => 'Nenhum',
                    'extractors' => 'Extratores',
                    'vmc_single' => 'VMC fluxo simples',
                    'vmc_dual' => 'VMC duplo fluxo',
                    'other' => 'Outro',
                ],
                'area_size' => [
                    'small' => '< 100m²',
                    'medium' => '100-300m²',
                    'large' => '> 300m²',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Ventilação',
            'keywords' => [
                0 => 'ventilação',
                1 => 'ventilacao',
                2 => 'vmc',
                3 => 'extração',
                4 => 'extracao',
                5 => 'renovação ar',
                6 => 'renovacao ar',
                7 => 'humidade',
                8 => 'condensação',
            ],
            'synonyms' => [
                'ventilation_need' => [
                    'humidity' => [
                        0 => 'humidade',
                        1 => 'umidade',
                        2 => 'bolor',
                        3 => 'mofo',
                        4 => 'condensação',
                        5 => 'condensacao',
                        6 => 'janelas molhadas',
                    ],
                    'smells' => [
                        0 => 'cheiro',
                        1 => 'cheiros',
                        2 => 'odores',
                        3 => 'mau cheiro',
                        4 => 'cozinha',
                        5 => 'fumos',
                    ],
                    'no_system' => [
                        0 => 'não tem',
                        1 => 'nao tem',
                        2 => 'nunca teve',
                        3 => 'precisa de',
                        4 => 'instalar',
                    ],
                    'upgrade' => [
                        0 => 'melhorar',
                        1 => 'atualizar',
                        2 => 'substituir',
                        3 => 'trocar',
                        4 => 'moderno',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para sistemas de ventilação. Recolhe informações sobre o espaço e necessidades de ventilação. Sê conversador e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. NUNCA dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON:
- property_type: apartment, house, office, commercial, other
- ventilation_need: humidity, smells, no_system, upgrade, other
- existing_system: none, extractors, vmc_single, vmc_dual, other
- area_size: small, medium, large, not_sure',
            ],
        ],
    ],
];
