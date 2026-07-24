<?php

return [
    'key' => 'vehicle_transport',
    'icon' => '🚗',
    'required_fields' => [
        0 => 'vehicle_type',
        1 => 'vehicle_operational',
        2 => 'transport_type',
    ],
    'optional_fields' => [
        0 => 'vehicle_value',
        1 => 'boat_length',
        2 => 'has_trailer',
        3 => 'has_stand',
        4 => 'photos',
    ],
    'field_definitions' => [
        'vehicle_type' => [
            'type' => 'select',
            'options' => [
                0 => 'car',
                1 => 'motorcycle',
                2 => 'boat',
                3 => 'rv_camper',
                4 => 'truck_van',
                5 => 'atv',
                6 => 'other',
            ],
        ],
        'vehicle_operational' => [
            'type' => 'select',
            'options' => [
                0 => 'yes_drivable',
                1 => 'no_not_running',
                2 => 'not_sure',
            ],
        ],
        'transport_type' => [
            'type' => 'select',
            'options' => [
                0 => 'open_trailer',
                1 => 'enclosed',
                2 => 'not_sure',
            ],
        ],
        'vehicle_value' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_10k',
                1 => '10k_30k',
                2 => '30k_100k',
                3 => 'over_100k',
                4 => 'not_sure',
            ],
        ],
        'boat_length' => [
            'type' => 'select',
            'options' => [
                0 => 'less_than_5m',
                1 => '5_8m',
                2 => '8_12m',
                3 => 'over_12m',
                4 => 'not_sure',
            ],
            'required' => false,
            'when' => [
                'vehicle_type' => [
                    0 => 'boat',
                ],
            ],
        ],
        'has_trailer' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
                2 => 'not_sure',
            ],
            'required' => false,
            'when' => [
                'vehicle_type' => [
                    0 => 'boat',
                ],
            ],
        ],
        'has_stand' => [
            'type' => 'select',
            'options' => [
                0 => 'yes',
                1 => 'no',
            ],
            'required' => false,
            'when' => [
                'vehicle_type' => [
                    0 => 'motorcycle',
                ],
            ],
        ],
    ],
    'conditional_requirements' => [],
    'locales' => [
        'pt' => [
            'field_prompts' => [
                'vehicle_type' => 'Que tipo de veículo pretende transportar?',
                'vehicle_operational' => 'O veículo está operacional? Consegue circular pelos seus próprios meios?',
                'transport_type' => 'Prefere transporte em reboque aberto ou fechado?',
                'vehicle_value' => 'Qual é o valor aproximado do veículo?',
                'boat_length' => 'Qual é o comprimento da embarcação?',
                'has_trailer' => 'A embarcação tem reboque próprio?',
                'has_stand' => 'A mota tem descanso/cavalete?',
            ],
            'field_options' => [
                'vehicle_type' => [
                    'car' => 'Carro',
                    'motorcycle' => 'Mota',
                    'boat' => 'Barco / Embarcação',
                    'rv_camper' => 'Autocaravana / RV',
                    'truck_van' => 'Camião / Carrinha',
                    'atv' => 'ATV / Quad',
                    'other' => 'Outro',
                ],
                'vehicle_operational' => [
                    'yes_drivable' => 'Sim, está operacional',
                    'no_not_running' => 'Não, não funciona',
                    'not_sure' => 'Não tenho a certeza',
                ],
                'transport_type' => [
                    'open_trailer' => 'Reboque aberto',
                    'enclosed' => 'Fechado (contentor/caixa)',
                    'not_sure' => 'Não tenho a certeza',
                ],
                'vehicle_value' => [
                    'less_than_10k' => 'Menos de 10.000 €',
                    '10k_30k' => '10.000–30.000 €',
                    '30k_100k' => '30.000–100.000 €',
                    'over_100k' => 'Mais de 100.000 €',
                    'not_sure' => 'Prefiro não dizer',
                ],
                'boat_length' => [
                    'less_than_5m' => 'Menos de 5 m',
                    '5_8m' => '5–8 m',
                    '8_12m' => '8–12 m',
                    'over_12m' => 'Mais de 12 m',
                    'not_sure' => 'Não sei',
                ],
                'has_trailer' => [
                    'yes' => 'Sim, tem reboque',
                    'no' => 'Não tem reboque',
                    'not_sure' => 'Não sei',
                ],
                'has_stand' => [
                    'yes' => 'Sim',
                    'no' => 'Não',
                ],
            ],
            'name' => 'Transporte de Veículos',
            'keywords' => [
                0 => 'carro',
                1 => 'mota',
                2 => 'veículo',
                3 => 'veiculo',
                4 => 'barco',
                5 => 'autocaravana',
                6 => 'reboque',
                7 => 'transportar carro',
                8 => 'transportar mota',
                9 => 'transportar barco',
            ],
        ],
        'en' => [
            'field_prompts' => [
                'vehicle_type' => 'What type of vehicle do you need to transport?',
                'vehicle_operational' => 'Is the vehicle operational? Can it move under its own power?',
                'transport_type' => 'Do you prefer open or enclosed transport?',
                'vehicle_value' => 'What is the approximate value of the vehicle?',
                'boat_length' => 'What is the length of the boat?',
                'has_trailer' => 'Does the boat have its own trailer?',
                'has_stand' => 'Does the motorcycle have a stand?',
            ],
            'field_options' => [
                'vehicle_type' => [
                    'car' => 'Car',
                    'motorcycle' => 'Motorcycle',
                    'boat' => 'Boat / Watercraft',
                    'rv_camper' => 'RV / Camper',
                    'truck_van' => 'Truck / Van',
                    'atv' => 'ATV / Quad',
                    'other' => 'Other',
                ],
                'vehicle_operational' => [
                    'yes_drivable' => 'Yes, operational',
                    'no_not_running' => 'No, not running',
                    'not_sure' => 'Not sure',
                ],
                'transport_type' => [
                    'open_trailer' => 'Open trailer',
                    'enclosed' => 'Enclosed (container/box)',
                    'not_sure' => 'Not sure',
                ],
                'vehicle_value' => [
                    'less_than_10k' => 'Less than €10,000',
                    '10k_30k' => '€10,000–30,000',
                    '30k_100k' => '€30,000–100,000',
                    'over_100k' => 'Over €100,000',
                    'not_sure' => 'Prefer not to say',
                ],
                'boat_length' => [
                    'less_than_5m' => 'Less than 5 m',
                    '5_8m' => '5–8 m',
                    '8_12m' => '8–12 m',
                    'over_12m' => 'Over 12 m',
                    'not_sure' => 'Not sure',
                ],
                'has_trailer' => [
                    'yes' => 'Yes, has trailer',
                    'no' => 'No trailer',
                    'not_sure' => 'Not sure',
                ],
                'has_stand' => [
                    'yes' => 'Yes',
                    'no' => 'No',
                ],
            ],
            'name' => 'Vehicle Transport',
            'keywords' => [
                0 => 'car',
                1 => 'motorcycle',
                2 => 'vehicle',
                3 => 'boat',
                4 => 'rv',
                5 => 'camper',
                6 => 'trailer',
                7 => 'car shipping',
                8 => 'auto transport',
                9 => 'bike transport',
            ],
        ],
    ],
];
