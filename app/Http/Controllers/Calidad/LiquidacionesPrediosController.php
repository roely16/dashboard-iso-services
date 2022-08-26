<?php

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use App\MCAProcesos;

use Illuminate\Support\Facades\DB;

class LiquidacionesPrediosController extends Controller{

    public function data($data){

        $bottom_detail = [
            [
                'text' => 'Total',
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
                "text" => "Válidas",
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
        ];

        $response = [
            'total' => 0,
            'validas' => 100,
            'total_calidad' => 100,
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}

?>
