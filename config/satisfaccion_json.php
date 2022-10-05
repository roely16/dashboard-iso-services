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
                "value" => [],
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
    ]
];