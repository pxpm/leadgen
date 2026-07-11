<?php

return [
    'key' => 'waterproofing',
    'icon' => '💧',
    'required_fields' => [
        0 => 'surface_type',
        1 => 'problem_type',
    ],
    'optional_fields' => [
        0 => 'area_size',
        1 => 'access_type',
    ],
    'field_definitions' => [
        'surface_type' => [
            'type' => 'select',
            'options' => [
                0 => 'roof',
                1 => 'terrace',
                2 => 'facade',
                3 => 'foundation',
                4 => 'balcony',
                5 => 'other',
            ],
        ],
        'problem_type' => [
            'type' => 'select',
            'options' => [
                0 => 'leak',
                1 => 'damp',
                2 => 'preventive',
                3 => 'crack',
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
        'access_type' => [
            'type' => 'select',
            'options' => [
                0 => 'easy',
                1 => 'difficult',
                2 => 'needs_scaffolding',
                3 => 'needs_rappelling',
                4 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'surface_type' => 'Onde está o problema? É no telhado, terraço, fachada ou noutro local?',
                'problem_type' => 'Qual é o problema? Tem infiltração, humidade ou é uma medida preventiva?',
                'area_size' => 'Qual é o tamanho aproximado da área afetada?',
                'access_type' => 'O local é de fácil acesso ou precisa de andaimes/rapel?',
            ],
            'field_options' => [
                'surface_type' => [
                    'roof' => 'Telhado/Cobertura',
                    'terrace' => 'Terraço',
                    'facade' => 'Fachada',
                    'foundation' => 'Fundação/Cave',
                    'balcony' => 'Varanda',
                    'other' => 'Outro',
                ],
                'problem_type' => [
                    'leak' => 'Infiltração',
                    'damp' => 'Humidade',
                    'preventive' => 'Preventivo',
                    'crack' => 'Fissuras/Rachadelas',
                    'other' => 'Outro',
                ],
                'area_size' => [
                    'small' => 'Pequena (<10m²)',
                    'medium' => 'Média (10-50m²)',
                    'large' => 'Grande (>50m²)',
                    'not_sure' => 'Não sei',
                ],
                'access_type' => [
                    'easy' => 'Fácil acesso',
                    'difficult' => 'Acesso difícil',
                    'needs_scaffolding' => 'Precisa de andaime',
                    'needs_rappelling' => 'Precisa de rapel',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Impermeabilizações',
            'keywords' => [
                0 => 'impermeabilização',
                1 => 'impermeabilizacao',
                2 => 'impermeabilizar',
                3 => 'humidade',
                4 => 'umidade',
            ],
            'synonyms' => [
                'surface_type' => [
                    'roof' => [
                        0 => 'telhado',
                        1 => 'cobertura',
                        2 => 'telha',
                        3 => 'telhas',
                    ],
                    'terrace' => [
                        0 => 'terraço',
                        1 => 'terraco',
                        2 => 'varanda',
                        3 => 'marquise',
                    ],
                    'facade' => [
                        0 => 'fachada',
                        1 => 'parede',
                        2 => 'parede exterior',
                        3 => 'empena',
                    ],
                    'foundation' => [
                        0 => 'fundação',
                        1 => 'fundacao',
                        2 => 'cave',
                        3 => 'garagem',
                        4 => 'subsolo',
                    ],
                    'balcony' => [
                        0 => 'varanda',
                        1 => 'sacada',
                        2 => 'alpendre',
                    ],
                ],
                'problem_type' => [
                    'leak' => [
                        0 => 'infiltração',
                        1 => 'infiltracao',
                        2 => 'goteira',
                        3 => 'pinga',
                        4 => 'água',
                        5 => 'agua',
                        6 => 'humidade',
                        7 => 'umidade',
                        8 => 'bolor',
                        9 => 'mofo',
                    ],
                    'damp' => [
                        0 => 'humidade',
                        1 => 'umidade',
                        2 => 'condensação',
                        3 => 'condensacao',
                        4 => 'bolor',
                        5 => 'mofo',
                    ],
                    'preventive' => [
                        0 => 'prevenir',
                        1 => 'prevenção',
                        2 => 'prevencao',
                        3 => 'manutenção',
                        4 => 'manutencao',
                        5 => 'preventivo',
                    ],
                    'crack' => [
                        0 => 'fissura',
                        1 => 'fenda',
                        2 => 'rachadela',
                        3 => 'rachado',
                        4 => 'partido',
                        5 => 'quebrado',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para serviços de impermeabilização. O teu trabalho é recolher informações sobre problemas de infiltração/humidade para que um técnico possa preparar um orçamento. Sê conversador, amigável e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. Nunca dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON. Valores:
- surface_type: roof, terrace, facade, foundation, balcony, other
- problem_type: leak, damp, preventive, crack, other
- area_size: small, medium, large, not_sure
- access_type: easy, difficult, needs_scaffolding, needs_rappelling, not_sure',
            ],
        ],
    ],
];
