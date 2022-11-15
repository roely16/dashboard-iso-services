<?php

namespace App\Http\Controllers\Eficacia;
use App\Http\Controllers\Controller;

use App\MCAProcesos;
use App\MetaIndicador;

use Illuminate\Support\Facades\DB;

class LiquidacionesController extends Controller{

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('eficacia_json.LIQUIDACIONES');

        }
        
        // Obtener los datos de la RFC
        $url = "http://172.23.25.36/indicadores_liquidaciones/resultadoLiquidacion.php";
        $curl = curl_init();
        
        $date_start = date('Ymd', strtotime($data->date));
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

        foreach ($result_array as &$item) {
            
            if (!empty($item->ZCASO) && !empty($item->ZESTADO) && !empty($item->ZMUESTRA_EXTRACCION)){

                $total [] = $item;

                if ($item->ZESTADO == 'A' || $item->ZESTADO == 'E'){

                    $validas [] = $item;

                }
            }

        }

        $split_date = explode('-', $data->date);
        $year = $split_date[0];
        $month = $split_date[1];

        // Obtener la meta
        $meta = MetaIndicador::where('id_indicador', 'LIQUIDACIONES')
                    ->where('tipo', 'EFICACIA')
                    ->where('anio', $year)
                    ->where('mes', $month)
                    ->first();

        // ! Obtener el dato de los Anteriores
        $result_anteriores = (object) app('App\Http\Controllers\PreviousController')->update_previous($data);
        $pendientes_congelado = $result_anteriores->value;
        $items_pendientes_congelado = $result_anteriores->items;

        $pendientes = ($meta ? $meta->cantidad_meta : 0) + $pendientes_congelado - count($validas);

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
                "value" => $meta ? $meta->cantidad_meta : 0,
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
                "value" => count($validas),
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
                "value" => $pendientes < 0 ? 0 : $pendientes,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
        ];

        $porcentaje = 0;

        if ($meta) {
            
            if ($meta->cantidad_meta > 0) {
            
                $porcentaje = round((count($validas) / $meta->cantidad_meta) * 100, 2);
    
            }else{
    
                $porcentaje = 0;
    
            }

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
