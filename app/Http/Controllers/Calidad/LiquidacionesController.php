<?php

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use App\MCAProcesos;

use Illuminate\Support\Facades\DB;

class LiquidacionesController extends Controller{

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('calidad_json.CALIDAD');

        }

        // Obtener la información de la RFC
        $url = "http://172.23.25.36/indicadores_liquidaciones/resultadoLiquidacion.php";
        $curl = curl_init();
        
        $date_start = date('Ymd', strtotime($data->date));;
        $date_end = date('Ymt', strtotime($data->date));

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,"fecha_inicio=" . $date_start . "&fecha_fin=" . $date_end);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        $result_array = json_decode($result);

        $total = [];
        $validas = [];
        $snc = [];
        $rechazadas = [];

        foreach ($result_array as &$item) {
            
            if (!empty($item->ZCASO) && !empty($item->ZESTADO) && !empty($item->ZMUESTRA_EXTRACCION)){

                $total [] = $item;

                if ($item->ZESTADO == 'A'){

                    $validas [] = $item;

                } else if ($item->ZESTADO == 'E'){
                    
                    $validas [] = $item;

                }else if ($item->ZESTADO == 'F') {

                    $rechazadas [] = $item;

                }
            }

        }

        // Obtener los SNC

        $headers = [
            [
                'text' => 'Caso',
                'value' => 'ZCASO'
            ],
            [
                'text' => 'Fecha',
                'value' => 'ZFECH_CREA_CASO'
            ],
            [
                'text' => 'Estado',
                'value' => 'ZESTADO'
            ],
            [
                'text' => 'Usuario',
                'value' => 'ZUSUARIO_EXTRACCION'
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
                "text" => "Válidas",
                "value" => count($validas),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $validas
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
                "value" => count($rechazadas),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $rechazadas
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
        ];

        if (count($total) > 0) {
            
            $porcentaje = round((count($validas) / count($total)) * 100, 1);

        }else{

            $porcentaje = 0;

        }

        $response = [
            'total' => $porcentaje,
            'validas' => 100,
            'total_calidad' => 100,
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}

?>
