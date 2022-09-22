<?php

namespace App\Http\Controllers;

use App\Proceso;

class CalidadController extends Controller{

    public function create($indicador){

        try {

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
                'nombre_historial' => $proceso->nombre_historial,
                'subarea_historial' => 'CALIDAD'
            ];

            // * Validar la fecha, si es un mes anterior deberá de buscar en el historial

            $current_date = date('Y-m');

            if (strtotime($indicador->date) < strtotime($current_date)) {
                
                // * Agregar la estructura de columnas para obtener la información del dashboard anterior
                $campos = ['campo_3', 'campo_1', 'campo_4', 'campo_2'];

                $data->campos = $campos;

                $result = (object) app('App\Http\Controllers\ConfigController')->get_history($data);

            }else{

                $result = (object) $this->data($data);

            }
           
            $chart = $this->chart($result);

            $total = [
                'total' => [
                    'value' => $result->total,
                ],
                'chart' => $chart,
                'results' => $result
            ];

            $indicador->content = $total;

            $indicador->bottom_detail = $result->bottom_detail;
            $indicador->validas = $result->validas;
            $indicador->total_calidad = $result->total_calidad;
            $indicador->custom_data = property_exists($result, 'custom_data') ? $result->custom_data : null;

            return $indicador;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ];

            $indicador->error = $error;

            return $indicador;

        }
        

    }

    public function chart($data){

        $bottom_detail = $data->bottom_detail;

        $total =  $bottom_detail[0];
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
        
        $result = app('App\Http\Controllers' . $data->data_controlador)->data($data);

        return $result;

    }
    

}