<?php

return [
    'key' => 'leak_repair',
    'icon' => '💧',
    'required_fields' => [
        0 => 'leak_location',
        1 => 'severity',
    ],
    'optional_fields' => [
        0 => 'pipe_material',
        1 => 'water_shutoff',
        2 => 'water_source',
        3 => 'problem_start',
        4 => 'affected_fixtures',
    ],
    'field_definitions' => [
        'leak_location' => [
            'type' => 'select',
            'options' => [
                0 => 'kitchen',
                1 => 'bathroom',
                2 => 'ceiling',
                3 => 'wall',
                4 => 'basement',
                5 => 'outdoor',
                6 => 'other',
            ],
        ],
        'severity' => [
            'type' => 'select',
            'options' => [
                0 => 'dripping',
                1 => 'small_stream',
                2 => 'flooding',
                3 => 'damp_only',
            ],
        ],
        'pipe_material' => [
            'type' => 'select',
            'options' => [
                0 => 'copper',
                1 => 'pvc',
                2 => 'galvanized',
                3 => 'pex',
                4 => 'multilayer',
                5 => 'not_sure',
            ],
        ],
        'water_shutoff' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'cant_find',
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
        'affected_fixtures' => [
            'type' => 'multi_select',
            'options' => [
                0 => 'sink',
                1 => 'toilet',
                2 => 'shower_bathtub',
                3 => 'dishwasher',
                4 => 'washing_machine',
                5 => 'fridge',
                6 => 'other',
            ],
        ],
    ],
    'conditional_requirements' => [
    ],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'leak_location' => 'Onde está a fuga? Cozinha, casa de banho, teto, cave?',
                'severity' => 'Qual é a gravidade? Pinga, corre um fio de água, ou está a inundar?',
                'pipe_material' => 'Sabe de que material são os canos? (se não souber, tudo bem)',
                'water_shutoff' => 'Consegue fechar a torneira de segurança?',
                'water_source' => 'O problema envolve água da rede, poço ou fossa séptica?',
                'problem_start' => 'Quando é que este problema começou?',
                'affected_fixtures' => 'Que equipamentos são afetados? (pia, sanita, chuveiro, etc.)',
            ],
            'field_options' => [
                'leak_location' => [
                    'kitchen' => 'Cozinha',
                    'bathroom' => 'Casa de banho',
                    'ceiling' => 'Teto',
                    'wall' => 'Parede',
                    'basement' => 'Cave',
                    'outdoor' => 'Exterior',
                    'other' => 'Outro',
                ],
                'severity' => [
                    'dripping' => 'A pingar',
                    'small_stream' => 'Fio de água',
                    'flooding' => 'A inundar',
                    'damp_only' => 'Só humidade',
                ],
                'pipe_material' => [
                    'copper' => 'Cobre',
                    'pvc' => 'PVC',
                    'galvanized' => 'Galvanizado',
                    'pex' => 'PEX',
                    'multilayer' => 'Multicamada',
                    'not_sure' => 'Não sei',
                ],
                'water_shutoff' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                    'cant_find' => 'Não encontro',
                ],
                'water_source' => [
                    'municipal' => 'Água da rede',
                    'well' => 'Água de poço',
                    'septic' => 'Fossa séptica',
                    'both' => 'Ambos (rede + esgotos)',
                    'unsure' => 'Não tenho a certeza',
                ],
                'problem_start' => [
                    'less_than_week' => 'Há menos de uma semana',
                    'few_weeks' => 'Há algumas semanas',
                    'month_or_more' => 'Há um mês ou mais',
                ],
                'affected_fixtures' => [
                    'sink' => 'Pia / Lava-louça',
                    'toilet' => 'Sanita',
                    'shower_bathtub' => 'Chuveiro / Banheira',
                    'dishwasher' => 'Máquina de lavar loiça',
                    'washing_machine' => 'Máquina de lavar roupa',
                    'fridge' => 'Frigorífico',
                    'other' => 'Outro',
                ],
            ],
            'name' => 'Reparação de Fugas',
            'keywords' => [
                0 => 'fuga',
                1 => 'pinga',
                2 => 'infiltração',
                3 => 'infiltracao',
                4 => 'cano',
                5 => 'canalização',
                6 => 'canalizacao',
                7 => 'água',
                8 => 'agua',
                9 => 'inundação',
                10 => 'inundacao',
            ],
            'synonyms' => [
                'leak_location' => [
                    'kitchen' => [
                        0 => 'cozinha',
                        1 => 'lava-louça',
                        2 => 'lava louça',
                        3 => 'pia',
                    ],
                    'bathroom' => [
                        0 => 'casa de banho',
                        1 => 'wc',
                        2 => 'banheiro',
                        3 => 'chuveiro',
                        4 => 'banheira',
                        5 => 'lavatório',
                    ],
                    'ceiling' => [
                        0 => 'teto',
                        1 => 'tecto',
                        2 => 'cima',
                        3 => 'andar de cima',
                    ],
                    'basement' => [
                        0 => 'cave',
                        1 => 'garagem',
                        2 => 'subsolo',
                    ],
                    'outdoor' => [
                        0 => 'jardim',
                        1 => 'exterior',
                        2 => 'fora',
                        3 => 'quintal',
                        4 => 'pátio',
                    ],
                ],
                'severity' => [
                    'dripping' => [
                        0 => 'pinga',
                        1 => 'pingar',
                        2 => 'pingadeira',
                        3 => 'gotas',
                        4 => 'lentamente',
                    ],
                    'small_stream' => [
                        0 => 'fio',
                        1 => 'corre',
                        2 => 'constante',
                        3 => 'alguma',
                    ],
                    'flooding' => [
                        0 => 'inundação',
                        1 => 'inundacao',
                        2 => 'muita',
                        3 => 'alagado',
                        4 => 'tudo molhado',
                        5 => 'urgente',
                    ],
                    'damp_only' => [
                        0 => 'humidade',
                        1 => 'umidade',
                        2 => 'mancha',
                        3 => 'bolor',
                        4 => 'mofo',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para reparação de fugas de água. Recolhe informações sobre a localização e gravidade. Se for uma inundação, sugere fechar a torneira de segurança. Sê calmo, conversador e profissional. NUNCA dês estimativas de custos.',
            ],
        ],
    ],
];
