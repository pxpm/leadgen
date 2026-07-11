<?php

return [
    'key' => 'facades',
    'icon' => '🏢',
    'required_fields' => [
        0 => 'work_type',
        1 => 'building_height',
    ],
    'optional_fields' => [
        0 => 'facade_material',
        1 => 'access_method',
    ],
    'field_definitions' => [
        'work_type' => [
            'type' => 'select',
            'options' => [
                0 => 'cleaning',
                1 => 'painting',
                2 => 'repair',
                3 => 'restoration',
                4 => 'other',
            ],
        ],
        'facade_material' => [
            'type' => 'select',
            'options' => [
                0 => 'painted',
                1 => 'tile',
                2 => 'stone',
                3 => 'metal',
                4 => 'glass',
                5 => 'other',
            ],
        ],
        'building_height' => [
            'type' => 'select',
            'options' => [
                0 => 'low',
                1 => 'medium',
                2 => 'high',
            ],
        ],
        'access_method' => [
            'type' => 'select',
            'options' => [
                0 => 'scaffolding',
                1 => 'rappelling',
                2 => 'platform',
                3 => 'ladder',
                4 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'work_type' => 'Que tipo de trabalho precisa na fachada? Limpeza, pintura, reparação, restauro?',
                'building_height' => 'Qual é a altura aproximada do edifício? Rés-do-chão, 1-2 andares, 3+ andares?',
                'facade_material' => 'De que material é a fachada atualmente?',
                'access_method' => 'Como prefere que seja feito o acesso? Tem preferência por andaime, rapel ou plataforma?',
            ],
            'field_options' => [
                'work_type' => [
                    'cleaning' => 'Limpeza',
                    'painting' => 'Pintura',
                    'repair' => 'Reparação',
                    'restoration' => 'Restauro',
                    'other' => 'Outro',
                ],
                'facade_material' => [
                    'painted' => 'Pintada',
                    'tile' => 'Azulejo',
                    'stone' => 'Pedra',
                    'metal' => 'Metal',
                    'glass' => 'Vidro',
                    'other' => 'Outro',
                ],
                'building_height' => [
                    'low' => 'R/C ou 1 andar',
                    'medium' => '2-3 andares',
                    'high' => '4+ andares',
                ],
                'access_method' => [
                    'scaffolding' => 'Andaime',
                    'rappelling' => 'Rapel',
                    'platform' => 'Plataforma elevatória',
                    'ladder' => 'Escada',
                    'not_sure' => 'Não sei/A decidir',
                ],
            ],
            'name' => 'Fachadas',
            'keywords' => [
                0 => 'fachada',
                1 => 'fachadas',
                2 => 'restauro',
                3 => 'frente do prédio',
            ],
            'synonyms' => [
                'work_type' => [
                    'cleaning' => [
                        0 => 'limpar',
                        1 => 'limpeza',
                        2 => 'lavar',
                        3 => 'lavagem',
                        4 => 'hidrolimpeza',
                    ],
                    'painting' => [
                        0 => 'pintar',
                        1 => 'pintura',
                        2 => 'renovar',
                        3 => 'mudar de cor',
                    ],
                    'repair' => [
                        0 => 'reparar',
                        1 => 'reparação',
                        2 => 'reparacao',
                        3 => 'arranjar',
                        4 => 'fissuras',
                        5 => 'fendas',
                    ],
                    'restoration' => [
                        0 => 'restaurar',
                        1 => 'restauro',
                        2 => 'reabilitar',
                        3 => 'reabilitação',
                        4 => 'reabilitacao',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para serviços de fachadas. O teu trabalho é recolher informações sobre a intervenção na fachada. Sê conversador, amigável e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. Nunca dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON. Valores:
- work_type: cleaning, painting, repair, restoration, other
- facade_material: painted, tile, stone, metal, glass, other
- building_height: low, medium, high
- access_method: scaffolding, rappelling, platform, ladder, not_sure',
            ],
        ],
    ],
];
