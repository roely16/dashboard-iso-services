<?php

namespace App\Http\Controllers\Eficacia;
use App\Http\Controllers\Controller;

use App\MCAProcesos;

use Illuminate\Support\Facades\DB;

class LiquidacionesPrediosController extends Controller{

    public function data($data){

        $bottom_detail = [
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
                "text" => "Pendientes",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
        ];

        $response = [
            'total' => 0,
            'validas' => 0,
            'total_calidad' => 0,
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}

?>
