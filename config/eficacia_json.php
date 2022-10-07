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
    ],
    'EFICACIA_DIRECCION' => [
        'indicadores' => [],
        'total' => 0,
        'bottom_detail' => [
            [
                'text' => 'Anteriores',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true
            ],
            [
                'text' => 'Ingresados',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true
            ],
            [
                'text' => 'Resueltos',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true
            ],
            [
                'text' => 'Pendientes',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true
            ]
        ],
    ],
    'EFICACIA_SERVICIOS' => [
        'total' => 0,
        'bottom_detail' => [
            [
                'text' => 'Anterior',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => 0,
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                'text' => 'Recibido',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => 0,
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                'text' => 'Finalizado',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => 0,
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                'text' => 'Emitidos',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => 0,
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            // [
            //     'text' => 'Pendientes',
            //     'value' => 0,
            //     'detail' => [
            //         'table' => [
            //             'items' => 0,
            //             'headers' => []
            //         ]
            //     ],
            //     'component' => 'tables/TableDetail'
            // ]
        ]
    ],
    'EFICACIA_TICKETS' => [
        'total' => 0,
        'bottom_detail' => [
            [
                'text' => 'Anteriores',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => 0
                    ],
                ],
                'component' => 'tables/TableTickets',
                'fullscreen' => true,
                'divide' => 'down'
            ],
            [
                'text' => 'Ingresados',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => 0
                    ],
                ],
                'component' => 'tables/TableTickets',
                'fullscreen' => true,
                'divide' => 'down'
            ],
            [
                'text' => 'Resueltos',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => 0
                    ],
                ],
                'component' => 'tables/TableTickets',
                'fullscreen' => true,
                'divide' => 'up'
            ],
            [
                'text' => 'Pendientes',
                'value' => 0,
                'component' => 'tables/TableProcesos',
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => 0
                    ],
                ],
                'component' => 'tables/TableTickets',
                'fullscreen' => true
            ]
        ]
    ]
];