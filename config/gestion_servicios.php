<?php

return [
    'CAPACITACIONES' => [
        'total' => 0,
        'bottom_detail' => [
            [
                'text' => 'Total',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                'text' => 'Realizadas',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                'text' => 'Rechazadas',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                'text' => 'Pendientes',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Suspendidas',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ]
    ],
    'SUMINISTROS' => [
        'total' => 0,
        'bottom_detail' => [
            [
                'text' => 'Anterior',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                'text' => 'Ingresadas',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                'text' => 'Resueltas',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ]
        ],
        'resueltas' => 0,
        'pendientes' => 0
    ]
];