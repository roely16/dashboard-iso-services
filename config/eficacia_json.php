<?php

return [
    'EFICACIA' => [
        "total" => 0,
        'carga_trabajo' => 0,
        'total_resueltos' => 0,
        'bottom_detail' => [
            [
                "text" => "Anteriores",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail',
                'fullscreen' => true,
                'divide' => 'down'
            ],
            [
                "text" => "Ingresados",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail',
                'fullscreen' => true,
                'divide' => 'down'
            ],
            [
                "text" => "Resueltos",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail',
                'fullscreen' => true,
                'divide' => 'up'
            ],
            [
                "text" => "Pendientes",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail',
                'fullscreen' => true
            ],
        ]
    ]
];