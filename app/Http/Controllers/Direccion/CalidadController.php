<?php

namespace App\Http\Controllers\Direccion;
use App\Http\Controllers\Controller;

use App\Indicador;
use App\Proceso;
use App\IndicadorExcept;

class CalidadController extends Controller{

    public function create($indicador){

        $data = $indicador->kpi_data;

        // * Validar si es una consulta de un mes posterior o actual 
        $result = (object) app('App\Http\Controllers\ValidationController')->check_case($indicador);

        $result = $result->data ? $result->data : (object) $this->data($data);

        $chart = $this->chart($result);

        $total = [
            'data' => $result,
            'total' => [
                'value' =>  $result->total,
            ],
            'chart' => $chart
        ];

        $indicador->content = $total;
        $indicador->bottom_detail = $result->bottom_detail;
        $indicador->indicadores = $result->indicadores;
        $indicador->data = $result;

        return $indicador;

    }

    public function chart($data){

        $bottom_detail = $data->bottom_detail;

        $total = $bottom_detail[0];
        $validas = $bottom_detail[1];

        $chart = [
            'type' => "Pie",
            'chartData' => [
                'labels' => [
                    $validas['text'],
                    'SNC y Rechazos'
                ],
                'datasets'=> [
                    [
                        'data' => [
                            $validas['value'],
                            $total['value'] - $validas['value']
                        ],
                        'backgroundColor' => [
                            'rgb(54, 162, 235)',
                            'rgba(255, 205, 86, 0)'
                        ]
                    ]
                ],
            ],
            'chartOptions' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'display' => false,
                    ],
                ],
            ],
        ];

        return $chart;

    }

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('calidad_json.CALIDAD_DIRECCION');

        }

        // Obtener la lista de procesos que tienen un indicador de eficacia

        // * Tomar en consideración que no deberán de figurar los indicadores en la tabla de excepción 
        $indicadores = Indicador::where('tipo', 'calidad')
                        ->whereNotIn('id', IndicadorExcept::where('mes', $data->date)->pluck('id_indicador')->toArray())
                        ->orderBy('id_proceso', 'asc')
                        ->get();

        foreach ($indicadores as $indicador) {
            
            $indicador->date = $data->date;

            // * Obtener el nombre del proceso 
            $proceso = Proceso::find($indicador->id_proceso);
            $indicador->proceso = $proceso->nombre;

            // * Obtener la información para el indicador
            $kpi_data = app('app\Http\Controllers\DashboardController')->get_info($indicador);

            $indicador->kpi_data = $kpi_data;

            app('App\Http\Controllers' . $indicador->controlador)->{$indicador->funcion}($indicador);

        }

        $i = 0;
        $total = 0;
        $validas = 0;

        $bottom_detail = [
            [
                'text' => 'Total',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'tooltip' => []
            ],
            [
                'text' => 'Válidas',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'tooltip' => []
            ],
            [
                'text' => 'SNC',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'tooltip' => []
            ],
            [
                'text' => 'Correcciones',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'tooltip' => []
            ]
        ];

        $indicadores_p = $indicadores;
        $suma_porcentajes = 0;
        $num_indicadores = 0;

        $total_detail = [];

        // Por cada indicador obtener los valores necesarios para realizar la sumatoria
        foreach ($indicadores as $indicador) {
            
            $total += $indicador->total_calidad;
            $validas += $indicador->validas;

            $suma_porcentajes += $indicador->data->total;

            array_push($total_detail, ['name' => $indicador->nombre, 'title' => $indicador->proceso, 'value' => $indicador->data->total]);

            $num_indicadores++;

            foreach ($indicador->bottom_detail as $detalle) {
                
                $bottom_detail[$i]['value'] += $detalle['value'];

                array_push($bottom_detail[$i]['tooltip'], ['name' => $indicador->nombre, 'title' => $indicador->proceso, 'value' => $detalle['value']]);

                $areas = [];

                foreach ($indicadores_p as $indicador_p) {
                    
                    $area = Proceso::find($indicador_p->id_proceso)->area;

                    if ($i < count($indicador_p->bottom_detail)) {
                        
                        if (array_key_exists('detail', $indicador_p->bottom_detail[$i])) {

                            $area->bottom_detail = $indicador_p->bottom_detail[$i]['detail'];
                            $area->component = $indicador_p->bottom_detail[$i]['component'];
                            
                            if (count($area->bottom_detail['table']['items']) > 0) {
        
                                
                                
                            }

                            $areas [] = $area;
                            
                        }

                    }

                }

                $bottom_detail[$i]['detail'] = $areas;
                $i++;

            }

            $i = 0;
        }

        if ($suma_porcentajes > 0) {
            
            $porcentaje = round($suma_porcentajes / $num_indicadores, 2);

        }else{
            
            $porcentaje = 100;

        }

        $response = [
            'indicadores' => $indicadores,
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail,
            'tooltip' => $total_detail,
            'value' => $porcentaje,
            'component' => 'tables/DireccionDetalle',
            'text' => $indicador->nombre
        ];

        return $response;

    }

}