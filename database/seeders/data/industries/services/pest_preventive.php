<?php

return [
    'key' => 'pest_preventive',
    'icon' => '🛡️',
    'required_fields' => [
        0 => 'property_type',
        1 => 'contract_interest',
    ],
    'optional_fields' => [
        0 => 'previous_treatment',
        1 => 'specific_concerns',
    ],
    'field_definitions' => [
        'property_type' => [
            'type' => 'select',
            'options' => [
                0 => 'apartment',
                1 => 'house',
                2 => 'restaurant',
                3 => 'hotel',
                4 => 'warehouse',
                5 => 'office',
                6 => 'other',
            ],
        ],
        'contract_interest' => [
            'type' => 'select',
            'options' => [
                0 => 'annual',
                1 => 'quarterly',
                2 => 'one_time',
                3 => 'not_sure',
            ],
        ],
        'previous_treatment' => [
            'type' => 'select',
            'options' => [
                0 => 'never',
                1 => 'long_ago',
                2 => 'recently',
            ],
        ],
        'specific_concerns' => [
            'type' => 'select',
            'options' => [
                0 => 'rodents',
                1 => 'insects',
                2 => 'termites',
                3 => 'all',
                4 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'property_type' => 'Que tipo de propriedade precisa de proteção?',
                'contract_interest' => 'Tem interesse num contrato anual, trimestral, ou intervenção pontual?',
                'previous_treatment' => 'Já fez tratamentos de controlo de pragas antes?',
                'specific_concerns' => 'Preocupa-o algum tipo de praga em específico? Roedores, insetos, térmitas?',
            ],
            'field_options' => [
                'property_type' => [
                    'apartment' => 'Apartamento',
                    'house' => 'Moradia',
                    'restaurant' => 'Restaurante',
                    'hotel' => 'Hotel',
                    'warehouse' => 'Armazém',
                    'office' => 'Escritório',
                    'other' => 'Outro',
                ],
                'contract_interest' => [
                    'annual' => 'Contrato anual',
                    'quarterly' => 'Trimestral',
                    'one_time' => 'Pontual',
                    'not_sure' => 'Não sei/Aconselhem-me',
                ],
                'previous_treatment' => [
                    'never' => 'Nunca',
                    'long_ago' => 'Há muito tempo',
                    'recently' => 'Recentemente',
                ],
                'specific_concerns' => [
                    'rodents' => 'Roedores',
                    'insects' => 'Insetos',
                    'termites' => 'Térmitas',
                    'all' => 'Todos',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Prevenção de Pragas',
            'keywords' => [
                0 => 'prevenção',
                1 => 'prevencao',
                2 => 'contrato',
                3 => 'manutenção pragas',
                4 => 'manutencao pragas',
                5 => 'desinfestação regular',
                6 => 'desinfestacao regular',
            ],
            'synonyms' => [],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para contratos de prevenção de pragas. Recolhe informações sobre o tipo de propriedade e necessidades. Sê profissional e informativo. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
