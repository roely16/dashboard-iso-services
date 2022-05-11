<?php

namespace App\Http\Controllers;

use App\MCAProcesos;
use App\Proceso;

use DB;

class CalidadController extends Controller{

    public function create($indicador){

        $proceso = Proceso::find($indicador->id_proceso)->first();
        $area = $proceso->area;
        
        $data = [
            'codarea' => $area->codarea,
            'date' => $indicador->date
        ];

        $result = $this->get_data($data);
        $chart = $this->chart($indicador);

        $total = [
            'total' => [
                'value' => "100%",
            ],
            'chart' => $chart,
            'results' => $result
        ];

        $indicador->content = $total;

        $indicador->bottom_detail = $this->bottom_detail($indicador);

        return $indicador;

    }

    public function chart($indicador){

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

    public function bottom_detail($indicador){

        $result = [
            [
                "text" => "Total",
                "value" => 605,
            ],
            [
                "text" => "Válidas",
                "value" => 602,
            ],
            [
                "text" => "SNC",
                "value" => 3,
            ],
            [
                "text" => "Rechazadas",
                "value" => 0,
            ],
        ];

        return $result;

    }

    public function get_data($data){

        $results = app('db')->connection('catastrousr')->select("SELECT *
                    FROM SGC.MCA_PROCESOS_VW
                    WHERE CODIGOCLASE = 1
                    AND TO_CHAR(FECHA_FINALIZACION, 'YYYY-MM') = '2022-05'
                    AND TO_CHAR(FECHA_INGRESO, 'YYYY-MM') = '2022-05'
                    AND DEPENDENCIA = 18
                    AND MES_INICIAL = 5");

        return $results;

    }
    

}