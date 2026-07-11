<?php

return [
    'key' => 'garden_maintenance',
    'icon' => '🌿',
    'required_fields' => [
        0 => 'garden_size',
        1 => 'frequency',
    ],
    'optional_fields' => [
        0 => 'specific_tasks',
        1 => 'has_equipment',
    ],
    'field_definitions' => [
        'garden_size' => [
            'type' => 'select',
            'options' => [
                0 => 'small',
                1 => 'medium',
                2 => 'large',
            ],
        ],
        'frequency' => [
            'type' => 'select',
            'options' => [
                0 => 'weekly',
                1 => 'biweekly',
                2 => 'monthly',
                3 => 'one_time',
                4 => 'not_sure',
            ],
        ],
        'specific_tasks' => [
            'type' => 'select',
            'options' => [
                0 => 'lawn_only',
                1 => 'full_garden',
                2 => 'hedges',
                3 => 'trees',
                4 => 'cleaning',
                5 => 'other',
            ],
        ],
        'has_equipment' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'partial',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'garden_size' => 'Qual é o tamanho do jardim? Pequeno, médio, grande?',
                'frequency' => 'Com que frequência precisa de manutenção? Semanal, quinzenal, mensal?',
                'specific_tasks' => 'Que tarefas específicas precisa? Cortar relva, podar sebes, limpeza geral?',
                'has_equipment' => 'Tem equipamentos de jardim ou precisa que o jardineiro traga tudo?',
            ],
            'field_options' => [
                'garden_size' => [
                    'small' => 'Pequeno',
                    'medium' => 'Médio',
                    'large' => 'Grande',
                ],
                'frequency' => [
                    'weekly' => 'Semanal',
                    'biweekly' => 'Quinzenal',
                    'monthly' => 'Mensal',
                    'one_time' => 'Só uma vez',
                    'not_sure' => 'A definir',
                ],
                'specific_tasks' => [
                    'lawn_only' => 'Só relva',
                    'full_garden' => 'Jardim completo',
                    'hedges' => 'Sebes',
                    'trees' => 'Árvores',
                    'cleaning' => 'Limpeza',
                    'other' => 'Outro',
                ],
                'has_equipment' => [
                    'yes' => 'Sim, tenho',
                    'no' => 'Não',
                    'partial' => 'Algum',
                ],
            ],
            'name' => 'Manutenção de Jardins',
            'keywords' => [
                0 => 'jardineiro',
                1 => 'cortar relva',
                2 => 'manutenção jardim',
                3 => 'manutencao jardim',
                4 => 'podar',
                5 => 'cortar erva',
                6 => 'erva',
            ],
            'synonyms' => [],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para manutenção de jardins. Recolhe informações sobre o espaço e frequência desejada. Sê conversador e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
