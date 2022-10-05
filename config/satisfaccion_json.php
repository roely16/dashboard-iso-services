<?php

return [
    'SATISFACCION' => [
        'total' => 0,
        'evaluaciones' => 0,
        'no_conformes' => 0,
        'bottom_detail' => [
            [
                "text" => 'Universo',
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableSatisfaccion',
                'divide' => 'down'
            ],
            [
                "text" => 'Aceptable',
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableSatisfaccion',
                'divide' => 'up' 
            ],
            [
                "text" => 'No Conforme',
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableSatisfaccion'
            ],
        ]
    ],
    'SATISFACCION_DIRECCION' => [
        'indicadores' => [],
        'total' => 0,
        'bottom_detail' => [
            [
                'text' => 'Universo',
                'value' => 0,
                'component' => 'tables/TableProcesos'
            ],
            [
                'text' => 'Aceptable',
                'value' => 0,
                'component' => 'tables/TableProcesos'
            ],
            [
                'text' => 'No Aceptable',
                'value' => 0,
                'component' => 'tables/TableProcesos'
            ]
        ],
    ]
];