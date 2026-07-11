<?php

return [
    'key' => 'remodeling',
    'icon' => '🔨',
    'required_fields' => [
        0 => 'remodel_scope',
    ],
    'optional_fields' => [
        0 => 'timeline',
        1 => 'budget_range',
        2 => 'has_project_plan',
    ],
    'field_definitions' => [
        'remodel_scope' => [
            'type' => 'select',
            'options' => [
                0 => 'kitchen',
                1 => 'bathroom',
                2 => 'full_house',
                3 => 'apartment',
                4 => 'commercial',
                5 => 'partial',
                6 => 'other',
            ],
        ],
        'timeline' => [
            'type' => 'select',
            'options' => [
                0 => 'urgent',
                1 => 'within_month',
                2 => 'within_3_months',
                3 => 'flexible',
                4 => 'not_sure',
            ],
        ],
        'budget_range' => [
            'type' => 'select',
            'options' => [
                0 => 'low',
                1 => 'medium',
                2 => 'high',
                3 => 'premium',
                4 => 'not_sure',
            ],
        ],
        'has_project_plan' => [
            'type' => 'select',
            'options' => [
                0 => 'idea_only',
                1 => 'need_design',
                2 => 'plans_ready',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'remodel_scope' => 'O que pretende remodelar? Cozinha, casa de banho, casa completa, apartamento?',
                'timeline' => 'Qual é o prazo desejado para a obra?',
                'budget_range' => 'Tem alguma expectativa de orçamento para esta obra?',
                'has_project_plan' => 'Já tem um plano ou projeto definido para esta obra?',
            ],
            'field_options' => [
                'remodel_scope' => [
                    'kitchen' => 'Cozinha',
                    'bathroom' => 'Casa de banho',
                    'full_house' => 'Casa completa',
                    'apartment' => 'Apartamento',
                    'commercial' => 'Espaço comercial',
                    'partial' => 'Parcial/Divisões',
                    'other' => 'Outro',
                ],
                'timeline' => [
                    'urgent' => 'Urgente',
                    'within_month' => 'Este mês',
                    'within_3_months' => 'Até 3 meses',
                    'flexible' => 'Flexível',
                    'not_sure' => 'A decidir',
                ],
                'budget_range' => [
                    'low' => 'Económico',
                    'medium' => 'Médio',
                    'high' => 'Alto',
                    'premium' => 'Premium',
                    'not_sure' => 'A decidir',
                ],
                'has_project_plan' => [
                    'idea_only' => 'Tenho ideia do que quero',
                    'need_design' => 'Preciso de ajuda com o design',
                    'plans_ready' => 'Já tenho projeto pronto',
                ],
            ],
            'missed_call' => [
                'welcome_message' => 'Olá! Não pudemos atender a sua chamada. Como podemos ajudar?',
                'intents' => [
                    'budget' => 'Quero um orçamento',
                    'report' => 'Reportar um problema',
                    'other' => 'Outro assunto',
                ],
            ],
            'name' => 'Remodelações',
            'keywords' => [
                0 => 'remodelar',
                1 => 'remodelação',
                2 => 'remodelacao',
                3 => 'obras',
                4 => 'cozinha',
                5 => 'casa de banho',
                6 => 'wc',
                7 => 'renovar',
            ],
            'synonyms' => [
                'remodel_scope' => [
                    'kitchen' => [
                        0 => 'cozinha',
                        1 => 'cozinhas',
                        2 => 'remodelar cozinha',
                    ],
                    'bathroom' => [
                        0 => 'casa de banho',
                        1 => 'wc',
                        2 => 'banheiro',
                        3 => 'sanita',
                        4 => 'lavabo',
                        5 => 'banho',
                    ],
                    'full_house' => [
                        0 => 'casa toda',
                        1 => 'vivenda',
                        2 => 'moradia',
                        3 => 'casa inteira',
                        4 => 'total',
                        5 => 'completo',
                    ],
                    'apartment' => [
                        0 => 'apartamento',
                        1 => 'apartamentos',
                        2 => 'andar',
                    ],
                    'commercial' => [
                        0 => 'loja',
                        1 => 'escritório',
                        2 => 'escritorio',
                        3 => 'comercial',
                        4 => 'restaurante',
                        5 => 'café',
                    ],
                    'partial' => [
                        0 => 'parcial',
                        1 => 'algumas divisões',
                        2 => 'alguns quartos',
                        3 => 'sala',
                        4 => 'quarto',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para serviços de remodelação. O teu trabalho é recolher informações sobre o projeto de remodelação para que um empreiteiro possa preparar um orçamento. Sê conversador, amigável e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. Nunca dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON. Valores:
- remodel_scope: kitchen, bathroom, full_house, apartment, commercial, partial, other
- timeline: urgent, within_month, within_3_months, flexible, not_sure
- budget_range: low, medium, high, premium, not_sure',
            ],
        ],
    ],
];
