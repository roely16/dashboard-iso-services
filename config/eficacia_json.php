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
                'component' => 'tables/PendientesDetalle',
                'fullscreen' => false
            ]
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
                'fullscreen' => true,
                'tooltip' => []
            ],
            [
                'text' => 'Ingresados',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true,
                'tooltip' => []
            ],
            [
                'text' => 'Resueltos',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true,
                'tooltip' => []
            ],
            [
                'text' => 'Pendientes',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true,
                'tooltip' => []
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
                'text' => 'Pendientes',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => 0,
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
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
            ]
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
    ],
    'LIQUIDACIONES' => [
        'total' => 0,
        'validas' => 0,
        'total_calidad' => 0,
        'bottom_detail' => [
            [
                'text' => 'Anterior',
                'value' => 0,
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
                "text" => "Meta",
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
                "text" => "Real",
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
                "text" => "Pendiente",
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
    ],
    'LIQUIDACIONES_PREDIOS' => [
        'total' => 0,
        'validas' => 0,
        'total_calidad' => 0,
        'bottom_detail' => [
            [
                'text' => 'Anterior',
                'value' => 0,
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
                "text" => "Meta",
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
                "text" => "Ejecutado",
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
                "text" => "Pendiente",
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
    ],
    'CONVENIOS' => [
        'total' => 0,
        'carga_trabajo' => 0,
        'total_resueltos' => 0,
        'bottom_detail' => [
            [
                'text' => 'Total',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                'text' => 'En Tiempo',
                'value' => 0,
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
                'text' => 'Fuera de Tiempo',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail'
            ]
        ]
    ]
];