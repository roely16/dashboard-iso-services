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
    ],
    'EFICACIA_ATENCION' => [
        'cumplimiento' => 0,
        'mensual' => 0,
        'trimestral' => 0,
        'menor_10' => [
            'value' => 0,
            'detail' => [
                'table' => [
                    'headers' => [],
                    'items' => []
                ]
            ],
            'component' => 'tables/TableTicketTime'
        ],
        'menor_20' => [
            'value' => 0,
            'detail' => [
                'table' => [
                    'headers' => [],
                    'items' => []
                ]
            ],
            'component' => 'tables/TableTicketTime'
        ],
        'menor_45' => [
            'value' => 0,
            'detail' => [
                'table' => [
                    'headers' => [],
                    'items' => []
                ]
            ],
            'component' => 'tables/TableTicketTime'
        ],  
        'bottom_detail' => [
            [
                'text' => 'Total Tickets',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableTicketTime'
            ],
            [
                'text' => 'Recepción Docs',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Cancelados',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ]
    ],
    'ACCIONES_DIRECCION' => [
        'avance' => [
            'text' => 'Avance',
            'value' => 0
        ],
        'eficacia' => [
            'text' => 'Eficacia',
            'value' => 0,
            'detail' => [
                'table' => [
                    'items' => [],
                    'headers' => []
                ]
            ],
            'component' => 'tables/TableAcciones'
        ],
        'bottom_detail' => [
            [
                'text' => 'Abiertas',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableAcciones'
            ],
            [
                'text' => 'Cerradas',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableAcciones'
            ],
            [
                'text' => 'Total',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableAcciones'
            ],
            [
                'text' => 'Avance',
                'value' => 0,
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableAcciones'
            ]
        ]
    ],
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
        ],
        'indicadores' => []
    ],
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