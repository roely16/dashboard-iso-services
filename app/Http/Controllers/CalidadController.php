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
                'data_controlador' => $indicador->data_controlador
            ];

            $result = (object) $this->data($data);
            $chart = $this->chart($indicador);

            $total = [
                'total' => [
                    'value' => $result->total . "%",
                ],
                'chart' => $chart,
                'results' => $result
            ];

            $indicador->content = $total;

            $indicador->bottom_detail = $result->bottom_detail;

            return $indicador;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage()
            ];

            $indicador->error = $error;

            return $indicador;

        }
        

    }

    public function chart($data){

        $chart = [
            'type' => "Pie",
            'chartData' => [
                'labels' => ["January", "February", "March"],
                'datasets'=> [
                    [
                        'data' => [90, 10],
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