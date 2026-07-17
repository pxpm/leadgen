<?php

return [
    'key' => 'painting',
    'icon' => '🖌️',
    'required_fields' => [
        0 => 'painting_subtype',
        1 => 'paint_scope',
        2 => 'surface_condition',
    ],
    'optional_fields' => [
        0 => 'area_size',
        1 => 'material_supplied',
        2 => 'property_occupied',
    ],
    'field_definitions' => [
        'painting_subtype' => [
            'type' => 'select',
            'options' => [
                0 => 'pintar_casa',
                1 => 'pintar_edificio',
                2 => 'pintar_comercial',
                3 => 'pintar_industrial',
                4 => 'pintar_muro',
                5 => 'outro',
            ],
        ],
        'paint_scope' => [
            'type' => 'select',
            'multi' => false,
            'options' => [
                0 => 'interior',
                1 => 'exterior',
                2 => 'both',
            ],
        ],
        'surface_condition' => [
            'type' => 'select',
            'options' => [
                0 => 'good',
                1 => 'cracked',
                2 => 'peeling',
                3 => 'mold',
                4 => 'new_construction',
                5 => 'other',
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
        'building_type' => [
            'type' => 'select',
            'options' => [
                0 => 'detached',
                1 => 'semi_detached',
                2 => 'terraced',
                3 => 'other',
            ],
            'required' => false,
            'when' => [
                'property_type' => [
                    0 => 'house',
                ],
            ],
        ],
        'material_supplied' => [
            'type' => 'select',
            'options' => [
                0 => 'customer_has',
                1 => 'discuss',
                2 => 'specialist_provides',
            ],
        ],
        'property_occupied' => [
            'type' => 'select',
            'options' => [
                0 => 'yes_occupied',
                1 => 'yes_furnished',
                2 => 'vacant',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'painting_subtype' => 'Que tipo de imóvel ou superfície precisa de pintar?',
                'paint_scope' => 'Precisa de pintura interior, exterior ou ambas?',
                'surface_condition' => 'Em que estado estão as superfícies? Estão em bom estado, descascadas, com fissuras ou bolor?',
                'area_size' => 'Qual é o tamanho aproximado da área a pintar?',
                'building_type' => 'Que tipo de moradia é? Isolada, geminada ou em banda?',
                'material_supplied' => 'Vai fornecer a tinta ou prefere que o especialista a forneça?',
                'property_occupied' => 'A propriedade está ocupada? Há móveis para proteger?',
            ],
            'field_options' => [
                'painting_subtype' => [
                    'pintar_casa' => 'Pintar casa / moradia',
                    'pintar_edificio' => 'Pintar edifício / prédio',
                    'pintar_comercial' => 'Pintar espaço comercial',
                    'pintar_industrial' => 'Pintar pavilhão industrial',
                    'pintar_muro' => 'Pintar muro / fachada',
                    'outro' => 'Outro',
                ],
                'paint_scope' => [
                    'interior' => 'Interior',
                    'exterior' => 'Exterior',
                    'both' => 'Ambos',
                ],
                'surface_condition' => [
                    'good' => 'Bom estado',
                    'cracked' => 'Com fissuras',
                    'peeling' => 'A descascar',
                    'mold' => 'Com bolor/humidade',
                    'new_construction' => 'Construção nova',
                    'other' => 'Outro',
                ],
                'area_size' => [
                    'small' => 'Pequena (<50m²)',
                    'medium' => 'Média (50-150m²)',
                    'large' => 'Grande (>150m²)',
                    'not_sure' => 'Não sei',
                ],
                'building_type' => [
                    'detached' => 'Moradia isolada',
                    'semi_detached' => 'Moradia geminada',
                    'terraced' => 'Moradia em banda',
                    'other' => 'Outro',
                ],
                'material_supplied' => [
                    'customer_has' => 'Já tenho a tinta',
                    'discuss' => 'Gostaria de discutir',
                    'specialist_provides' => 'Prefiro que o especialista forneça',
                ],
                'property_occupied' => [
                    'yes_occupied' => 'Sim, está ocupada e mobilada',
                    'yes_furnished' => 'Sim, mas posso proteger',
                    'vacant' => 'Está vazia',
                ],
            ],
            'name' => 'Pinturas',
            'keywords' => [
                0 => 'pintura',
                1 => 'pintar',
                2 => 'pinturas',
                3 => 'pintor',
            ],
            'synonyms' => [
                'painting_subtype' => [
                    'pintar_casa' => [
                        0 => 'casa',
                        1 => 'moradia',
                        2 => 'vivenda',
                        3 => 'habitação',
                        4 => 'residencial',
                    ],
                    'pintar_edificio' => [
                        0 => 'edifício',
                        1 => 'edificio',
                        2 => 'prédio',
                        3 => 'predio',
                        4 => 'condomínio',
                        5 => 'condominio',
                    ],
                    'pintar_comercial' => [
                        0 => 'comercial',
                        1 => 'loja',
                        2 => 'escritório',
                        3 => 'escritorio',
                        4 => 'restaurante',
                        5 => 'negócio',
                    ],
                    'pintar_muro' => [
                        0 => 'muro',
                        1 => 'muros',
                        2 => 'parede exterior',
                        3 => 'vedação',
                    ],
                    'pintar_industrial' => [
                        0 => 'industrial',
                        1 => 'pavilhão',
                        2 => 'pavilhao',
                        3 => 'armazém',
                        4 => 'fabrica',
                        5 => 'fábrica',
                    ],
                ],
                'paint_scope' => [
                    'interior' => [
                        0 => 'interior',
                        1 => 'dentro',
                        2 => 'paredes interiores',
                        3 => 'tectos',
                        4 => 'quartos',
                        5 => 'sala',
                        6 => 'cozinha',
                    ],
                    'exterior' => [
                        0 => 'exterior',
                        1 => 'fora',
                        2 => 'fachada',
                        3 => 'fachadas',
                        4 => 'parede exterior',
                        5 => 'muro',
                    ],
                    'both' => [
                        0 => 'ambos',
                        1 => 'interior e exterior',
                        2 => 'tudo',
                        3 => 'casa toda',
                        4 => 'completo',
                    ],
                ],
                'surface_condition' => [
                    'good' => [
                        0 => 'bom',
                        1 => 'bom estado',
                        2 => 'só pintar',
                        3 => 'renovar',
                        4 => 'mudar de cor',
                    ],
                    'cracked' => [
                        0 => 'fissuras',
                        1 => 'fendas',
                        2 => 'rachado',
                        3 => 'rachadelas',
                        4 => 'estalado',
                    ],
                    'peeling' => [
                        0 => 'descascar',
                        1 => 'descascado',
                        2 => 'a cair',
                        3 => 'bolhas',
                        4 => 'empolado',
                    ],
                    'mold' => [
                        0 => 'bolor',
                        1 => 'mofo',
                        2 => 'humidade',
                        3 => 'umidade',
                        4 => 'manchas',
                    ],
                    'new_construction' => [
                        0 => 'novo',
                        1 => 'construção nova',
                        2 => 'obra nova',
                        3 => 'nunca foi pintado',
                    ],
                ],
            ],
            'ai_prompt' => [
                'system' => 'És um assistente de admissão para serviços de pintura. O teu trabalho é recolher informações sobre o projeto de pintura para que um profissional possa preparar um orçamento. Sê conversador, amigável e profissional. Faz uma pergunta de cada vez. NUNCA feches a conversa enquanto houver campos por preencher. Nunca dês estimativas de custos.

SEMPRE que o cliente der informação, adiciona no FINAL um bloco JSON. Valores:
- painting_subtype: pintar_casa, pintar_edificio, pintar_comercial, pintar_industrial, pintar_muro, outro
- paint_scope: interior, exterior, both
- surface_condition: good, cracked, peeling, mold, new_construction, other
- area_size: small, medium, large, not_sure
- building_type: detached, semi_detached, terraced, other (só quando property_type=house)',
            ],
        ],
    ],
];
