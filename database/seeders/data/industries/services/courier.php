<?php

return [
    'key' => 'courier',
    'icon' => '📨',
    'required_fields' => [
        0 => 'package_type',
        1 => 'package_count',
        2 => 'delivery_speed',
    ],
    'optional_fields' => [
        0 => 'needs_signature',
        1 => 'fragile',
        2 => 'value_declared',
    ],
    'field_definitions' => [
        'package_type' => [
            'type' => 'select',
            'options' => [
                0 => 'document',
                1 => 'small_package',
                2 => 'medium_box',
                3 => 'large_box',
                4 => 'other',
            ],
        ],
        'package_count' => [
            'type' => 'select',
            'options' => [
                0 => '1',
                1 => '2_5',
                2 => '6_20',
                3 => '21_plus',
            ],
        ],
        'delivery_speed' => [
            'type' => 'select',
            'options' => [
                0 => 'same_day',
                1 => 'next_day',
                2 => 'standard',
                3 => 'economy',
            ],
        ],
        'needs_signature' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
            ],
        ],
        'fragile' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
            ],
        ],
        'value_declared' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_50',
                1 => '50_200',
                2 => '200_1000',
                3 => 'over_1000',
                4 => 'not_sure',
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'package_type' => 'Que tipo de envio precisa?',
                'package_count' => 'Quantas encomendas / volumes precisa de enviar?',
                'delivery_speed' => 'Com que urgência precisa da entrega?',
                'needs_signature' => 'A entrega precisa de assinatura na receção?',
                'fragile' => 'O conteúdo é frágil?',
                'value_declared' => 'Qual é o valor declarado do conteúdo?',
            ],
            'field_options' => [
                'package_type' => [
                    'document' => 'Documento / Envelope',
                    'small_package' => 'Pequena encomenda (<2 kg)',
                    'medium_box' => 'Caixa média (2–10 kg)',
                    'large_box' => 'Caixa grande (>10 kg)',
                    'other' => 'Outro',
                ],
                'package_count' => [
                    '1' => '1 volume',
                    '2_5' => '2–5 volumes',
                    '6_20' => '6–20 volumes',
                    '21_plus' => 'Mais de 20 volumes',
                ],
                'delivery_speed' => [
                    'same_day' => 'Hoje (urgente)',
                    'next_day' => 'Amanhã (24h)',
                    'standard' => 'Normal (2–3 dias)',
                    'economy' => 'Económico (3–5 dias)',
                ],
                'needs_signature' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                ],
                'fragile' => [
                    'yes' => 'Sim, é frágil',
                    'no' => 'Não',
                ],
                'value_declared' => [
                    'less_than_50' => 'Menos de 50 €',
                    '50_200' => '50–200 €',
                    '200_1000' => '200–1.000 €',
                    'over_1000' => 'Mais de 1.000 €',
                    'not_sure' => 'Não sei',
                ],
            ],
            'name' => 'Estafeta / Expresso',
            'keywords' => [
                0 => 'estafeta',
                1 => 'expresso',
                2 => 'encomenda',
                3 => 'encomendas',
                4 => 'correio',
                5 => 'urgente',
                6 => 'entrega',
                7 => 'enviar',
                8 => 'envio',
                9 => 'courier',
            ],
        ],
        'en' => [
            'field_prompts' => [
                'package_type' => 'What type of shipment do you need?',
                'package_count' => 'How many packages/items do you need to send?',
                'delivery_speed' => 'How quickly do you need delivery?',
                'needs_signature' => 'Does the delivery require a signature on receipt?',
                'fragile' => 'Is the content fragile?',
                'value_declared' => 'What is the declared value of the contents?',
            ],
            'field_options' => [
                'package_type' => [
                    'document' => 'Document / Envelope',
                    'small_package' => 'Small package (<2 kg)',
                    'medium_box' => 'Medium box (2–10 kg)',
                    'large_box' => 'Large box (>10 kg)',
                    'other' => 'Other',
                ],
                'package_count' => [
                    '1' => '1 item',
                    '2_5' => '2–5 items',
                    '6_20' => '6–20 items',
                    '21_plus' => 'More than 20 items',
                ],
                'delivery_speed' => [
                    'same_day' => 'Same day (urgent)',
                    'next_day' => 'Next day (24h)',
                    'standard' => 'Standard (2–3 days)',
                    'economy' => 'Economy (3–5 days)',
                ],
                'needs_signature' => [
                    'yes' => 'Yes',
                    'no' => 'No',
                ],
                'fragile' => [
                    'yes' => 'Yes, fragile',
                    'no' => 'No',
                ],
                'value_declared' => [
                    'less_than_50' => 'Less than €50',
                    '50_200' => '€50–200',
                    '200_1000' => '€200–1,000',
                    'over_1000' => 'Over €1,000',
                    'not_sure' => 'Not sure',
                ],
            ],
            'name' => 'Courier / Express',
            'keywords' => [
                0 => 'courier',
                1 => 'express',
                2 => 'parcel',
                3 => 'package',
                4 => 'delivery',
                5 => 'urgent',
                6 => 'overnight',
                7 => 'send',
                8 => 'shipment',
            ],
        ],
    ],
];
