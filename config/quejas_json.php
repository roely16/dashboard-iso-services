<?php

return [
    'QUEJAS' => [
        'total' => 0,
        'encuestas' => 0,
        'quejas' => 0,
        'bottom_detail' => [
            [
                "text" => "Encuestas",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableSatisfaccion',
                'divide' => 'down'
            ],
            [
                "text" => "Quejas",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                "text" => "Felicitaciones",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                "text" => "Sugerencias",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail'
            ],
        ]
    ]
];