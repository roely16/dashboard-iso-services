<?php

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use App\Models\Nomenclatura\ISONomenclatura;

class NomenclaturaController extends Controller{

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('calidad_json.CALIDAD');

        }
        
        $total = ISONomenclatura::select(
                        'lote',
                        DB::raw("to_char(fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha"),
                        'historial',
                        'resultado',
                        'usuario'
                    )
                    ->whereRaw("TO_CHAR(fecha, 'YYYY-MM') = '$data->date'")
                    ->where('intento', 1)
                    ->orderBy('fecha', 'asc')
                    ->get();

        $validos = [];
        $rechazados = []; 

        foreach ($total as $item) {
            
            $split_historial = explode(',', $item->historial);

            $item->expediente = $split_historial[0];
            $item->usuario_opera = $split_historial[2];
        
            if ($item->resultado === 'VALIDO') {
                
                $validos [] = $item;

            }else{

                $rechazados [] = $item;

            }

        }

        $no_conformes = (object) app('App\Http\Controllers\NoConformesController')->process($data); 

        $exp_no_conformes = [];

        foreach ($no_conformes as $snc) {
            
            $exp_no_conformes [] = $snc->expediente;

        }

        $filter_validos = [];

        foreach ($validos as $valido) {
            
            if (!in_array($valido->expediente, $exp_no_conformes)) {
                
                $filter_validos [] = $valido;

            }

        }

        $headers = [
            [
                'text' => 'Expediente',
                'value' => 'expediente'
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha'
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario_opera'
            ]
        ];

        $bottom_detail = [
            [
                'text' => 'Total',
                'value' => count($total),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $total
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                'text' => 'Válidas',
                'value' => count($filter_validos),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $filter_validos
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                'text' => 'SNC',
                'value' => count($no_conformes),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $no_conformes
                    ]
                ],
                'component' => 'tables/TableDetail',
            ],
            [
                'text' => 'Correcciones',
                'value' => count($rechazados),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $rechazados
                    ]
                ],
                'component' => 'tables/TableDetail',
            ]
        ];

        if (count($total) > 0) {
            
            $porcentaje = round((count($filter_validos) / count($total)) * 100, 2);

        }else{

            $porcentaje = 100;

        }

        $response = [
            'total' => $porcentaje,
            'validas' => count($filter_validos),
            'total_calidad' => count($total),
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}