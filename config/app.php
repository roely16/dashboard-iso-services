<?php 

return [
    'CALIDAD' => [
        'total' => 0,
        'validas' => 0,
        'total_calidad' => 0,
        'bottom_detail' => [
            [
                "text" => "Total",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                "text" => "Válidas",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                "text" => "SNC",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                "text" => "Correcciones",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
        ]
    ]
];