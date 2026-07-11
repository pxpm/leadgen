<?php

return [
    'key' => 'garden_design',
    'icon' => '🌳',
    'required_fields' => [
        0 => 'garden_size',
        1 => 'design_style',
    ],
    'optional_fields' => [
        0 => 'current_state',
        1 => 'budget_range',
        2 => 'timeline',
    ],
    'field_definitions' => [
        'garden_size' => [
            'type' => 'select',
            'options' => [
                0 => 'small_balcony',
                1 => 'small_yard',
                2 => 'medium',
                3 => 'large',
                4 => 'estate',
            ],
        ],
        'design_style' => [
            'type' => 'select',
            'options' => [
                0 => 'contemporary',
                1 => 'mediterranean',
                2 => 'tropical',
                3 => 'minimalist',
                4 => 'natural',
                5 => 'vegetable_garden',
                6 => 'not_sure',
            ],
        ],
        'current_state' => [
            'type' => 'select',
            'options' => [
                0 => 'empty_plot',
                1 => 'overgrown',
                2 => 'old_garden',
                3 => 'new_build',
                4 => 'other',
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
        'timeline' => [
            'type' => 'select',
            'options' => [
                0 => 'within_month',
                1 => 'within_3_months',
                2 => 'within_6_months',
                3 => 'planning',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'garden_size' => 'Qual é o tamanho aproximado do espaço? Varanda, pequeno jardim, grande terreno?',
                'design_style' => 'Que estilo de jardim gosta? Contemporâneo, mediterrânico, tropical, minimalista?',
                'current_state' => 'Como está o espaço atualmente? Vazio, cheio de mato, tem um jardim antigo?',
                'budget_range' => 'Que gama de orçamento tem em mente?',
                'timeline' => 'Para quando gostaria do projeto?',
            ],
            'field_options' => [
                'garden_size' => [
                    'small_balcony' => 'Varanda',
                    'small_yard' => 'Pequeno (< 50m²)',
                    'medium' => 'Médio (50-200m²)',
                    'large' => 'Grande (200-1000m²)',
                    'estate' => 'Propriedade (> 1000m²)',
                ],
                'design_style' => [
                    'contemporary' => 'Contemporâneo',
                    'mediterranean' => 'Mediterrânico',
                    'tropical' => 'Tropical',
                    'minimalist' => 'Minimalista',
                    'natural' => 'Natural/selvagem',
                    'vegetable_garden' => 'Horta',
                    'not_sure' => 'Não sei/Aconselhem-me',
                ],
                'current_state' => [
                    'empty_plot' => 'Terreno vazio',
                    'overgrown' => 'Cheio de mato',
                    'old_garden' => 'Jardim antigo',
                    'new_build' => 'Construção nova',
                    'other' => 'Outro',
                ],
                'budget_range' => [
                    'economy' => 'Económico',
                    'mid_range' => 'Médio',
                    'premium' => 'Premium',
                    'not_sure' => 'A decidir',
                ],
                'timeline' => [
                    'within_month' => 'Este mês',
                    'within_3_months' => '3 meses',
                    'within_6_months' => '6 meses',
                    'planning' => 'Só a planear',
                ],
            ],
            'name' => 'Design de Jardins',
            'keywords' => [
                0 => 'jardim',
                1 => 'paisagismo',
                2 => 'design jardim',
                3 => 'projeto jardim',
                4 => 'projeto paisagístico',
                5 => 'arquiteto',
                6 => 'relva',
                7 => 'plantas',
            ],
            'synonyms' => [],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para design de jardins. Recolhe informações sobre o espaço e estilo pretendido. Sê conversador, criativo e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
