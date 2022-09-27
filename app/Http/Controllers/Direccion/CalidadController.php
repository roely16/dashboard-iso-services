<?php

namespace App\Http\Controllers\Direccion;
use App\Http\Controllers\Controller;

use App\Indicador;
use App\Proceso;

class CalidadController extends Controller{

    public function create($indicador){

        $proceso = Proceso::find($indicador->id_proceso);
        $area = $proceso->area;
        $dependencia = $proceso->dependencia;
        
        $data = (object) [
            'codarea' => $area->codarea,
            'date' => $indicador->date,
            'dependencia' => $dependencia,
            'data_controlador' => $indicador->data_controlador,
            'controlador' => $indicador->controlador,
            'id_proceso' => $proceso->id,
            'id_indicador' => $indicador->id,
            'config' => $indicador->config,
            'nombre_historial' => $indicador->nombre_historial,
            'subarea_historial' => $indicador->subarea_historial,
            'campos' => $indicador->orden_campos ? explode(',', $indicador->orden_campos) : null
        ];

        // * Validar la fecha, si es un mes anterior deberá de buscar en el historial

        $current_date = date('Y-m');

        if (strtotime($indicador->date) < strtotime($current_date)) {

            $result = (object) app('App\Http\Controllers\ConfigController')->get_history($data);

        }else{

            $result = (object) $this->data($data);

        }

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
            
            return config('app.CALIDAD');

        }

        // Obtener la lista de procesos que tienen un indicador de eficacia
        $indicadores = Indicador::where('tipo', 'calidad')
                        ->orderBy('id_proceso', 'asc')
                        ->get();

        foreach ($indicadores as $indicador) {
            
            $indicador->date = $data->date;

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
            ],
            [
                'text' => 'Válidas',
                'value' => null,
                'component' => 'tables/TableProcesos',
            ],
            [
                'text' => 'SNC',
                'value' => null,
                'component' => 'tables/TableProcesos',
            ],
            [
                'text' => 'Correcciones',
                'value' => null,
                'component' => 'tables/TableProcesos',
            ]
        ];

        $indicadores_p = $indicadores;

        // Por cada indicador obtener los valores necesarios para realizar la sumatoria
        foreach ($indicadores as $indicador) {
            
            $total += $indicador->total_calidad;
            $validas += $indicador->validas;

            foreach ($indicador->bottom_detail as $detalle) {
                
                $bottom_detail[$i]['value'] += $detalle['value'];

                $areas = [];

                foreach ($indicadores_p as $indicador_p) {
                    
                    $area = Proceso::find($indicador_p->id_proceso)->area;

                    if (array_key_exists('detail', $indicador_p->bottom_detail[$i])) {

                        $area->bottom_detail = $indicador_p->bottom_detail[$i]['detail'];
                        $area->component = $indicador_p->bottom_detail[$i]['component'];
                        
                        if (count($area->bottom_detail['table']['items']) > 0) {
      
                            $areas [] = $area;
                            
                        }
                        
                    }

                }

                $bottom_detail[$i]['detail'] = $areas;
                $i++;

            }

            $i = 0;
        }

        if ($total > 0) {
            
            $porcentaje = round(($validas / $total) * 100, 1);

        }else{
            
            $porcentaje = 100;

        }

        $response = [
            'indicadores' => $indicadores,
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail,
        ];

        return $response;

    }

}