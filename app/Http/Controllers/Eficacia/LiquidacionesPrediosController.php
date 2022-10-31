<?php

namespace App\Http\Controllers\Eficacia;
use App\Http\Controllers\Controller;

class LiquidacionesPrediosController extends Controller{

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('eficacia_json.LIQUIDACIONES_PREDIOS');

        }

        // ! Obtener el dato de los Anteriores
        $result_anteriores = (object) app('App\Http\Controllers\PreviousController')->update_previous($data);
        $pendientes_congelado = $result_anteriores->value;
        $items_pendientes_congelado = $result_anteriores->items;

        $bottom_detail = [
            [
                'text' => 'Anterior',
                'value' => $pendientes_congelado,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => $items_pendientes_congelado
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
                "value" => $pendientes_congelado,
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
