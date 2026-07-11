<?php

return [
    'key' => 'bathroom_plumbing',
    'icon' => '🚽',
    'required_fields' => [
        0 => 'project_scope',
        1 => 'bathroom_count',
    ],
    'optional_fields' => [
        0 => 'timeline',
        1 => 'budget_range',
        2 => 'has_drawings',
        3 => 'water_source',
        4 => 'problem_start',
    ],
    'field_definitions' => [
        'project_scope' => [
            'type' => 'select',
            'options' => [
                0 => 'full_remodel',
                1 => 'partial',
                2 => 'new_install',
                3 => 'fixtures_only',
                4 => 'accessibility',
                5 => 'other',
            ],
        ],
        'bathroom_count' => [
            'type' => 'select',
            'options' => [
                0 => '1',
                1 => '2',
                2 => '3_or_more',
            ],
        ],
        'timeline' => [
            'type' => 'select',
            'options' => [
                0 => 'urgent',
                1 => 'within_month',
                2 => 'within_3_months',
                3 => 'planning',
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
        'has_drawings' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'need_help',
            ],
        ],
        'water_source' => [
            'type' => 'select',
            'options' => [
                0 => 'municipal',
                1 => 'well',
                2 => 'septic',
                3 => 'both',
                4 => 'unsure',
            ],
        ],
        'problem_start' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_week',
                1 => 'few_weeks',
                2 => 'month_or_more',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'project_scope' => 'O que pretende fazer? Remodelação completa, parcial, instalação nova?',
                'bathroom_count' => 'Quantas casas de banho são?',
                'timeline' => 'Para quando gostaria de ter o trabalho concluído?',
                'budget_range' => 'Que gama de orçamento tem em mente? (opcional)',
                'has_drawings' => 'Já tem projeto ou desenhos?',
                'water_source' => 'A propriedade está ligada à rede municipal, poço ou fossa séptica?',
                'problem_start' => 'Quando é que este problema ou necessidade surgiu?',
            ],
            'field_options' => [
                'project_scope' => [
                    'full_remodel' => 'Remodelação total',
                    'partial' => 'Parcial',
                    'new_install' => 'Instalação nova',
                    'fixtures_only' => 'Só louças',
                    'accessibility' => 'Acessibilidade',
                    'other' => 'Outro',
                ],
                'bathroom_count' => [
                    1 => '1',
                    2 => '2',
                    '3_or_more' => '3+',
                ],
                'timeline' => [
                    'urgent' => 'Urgente',
                    'within_month' => 'Este mês',
                    'within_3_months' => '3 meses',
                    'planning' => 'Só a planear',
                ],
                'budget_range' => [
                    'economy' => 'Económico',
                    'mid_range' => 'Médio',
                    'premium' => 'Premium',
                    'not_sure' => 'A decidir',
                ],
                'has_drawings' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'need_help' => 'Preciso de ajuda',
                ],
                'water_source' => [
                    'municipal' => 'Rede municipal',
                    'well' => 'Poço',
                    'septic' => 'Fossa séptica',
                    'both' => 'Ambos',
                    'unsure' => 'Não tenho a certeza',
                ],
                'problem_start' => [
                    'less_than_week' => 'Há menos de uma semana',
                    'few_weeks' => 'Há algumas semanas',
                    'month_or_more' => 'Há um mês ou mais',
                ],
            ],
            'name' => 'Canalização de Casa de Banho',
            'keywords' => [
                0 => 'casa de banho',
                1 => 'banheiro',
                2 => 'wc',
                3 => 'remodelar banho',
                4 => 'sanita',
                5 => 'chuveiro',
                6 => 'banheira',
                7 => 'moveis',
            ],
            'synonyms' => [],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para projetos de canalização de casa de banho. Recolhe informações sobre o âmbito e preferências. Sê conversador e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
