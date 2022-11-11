<?php

return [

    'INFRAESTRUCTURA' => [
        'total' => 0,
        'bottom_detail' => [
            [
                'text' => 'Anteriores',
                'value' => 0
            ],
            [
                'text' => 'Ingresado',
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
                'text' => 'Proceso',
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
                'text' => 'Finalizado',
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
    'CONTRATOS' => [
        'total' => 0,
        'bottom_detail' => [
            [
                'text' => 'Anteriores', 
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableContratos',
                'fullscreen' => true
            ],
            [
                'text' => 'Programado', 
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableContratos',
                'fullscreen' => true
            ],
            [
                'text' => 'En Proceso', 
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'fullscreen' => true
            ],  
            [
                'text' => 'Finalizado', 
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail',
                'fullscreen' => true
            ]
        ]
    ],
    'COMPRAS' => [
        'total' => 0,
        'bottom_detail' => [
            [
                'text' => 'Ingresado',
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
                'text' => 'Proceso',
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
                'text' => 'Finalizado',
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
    ]

];