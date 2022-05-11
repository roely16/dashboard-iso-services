<?php

namespace App\Http\Controllers;

use App\Proceso;
use App\MCAProcesos;

use Illuminate\Support\Facades\DB;

class EficaciaController extends Controller{

    public function create($indicador){

        $proceso = Proceso::find($indicador->id_proceso)->first();
        $area = $proceso->area;
        
        $data = [
            'codarea' => $area->codarea,
            'date' => $indicador->date
        ];

        $result = (object) $this->get_data($data);
        $chart = $this->chart($result);

        $total = [
            'total' => [
                'value' => $result->total . "%",
            ],
            'chart' => $chart,
            'result' => $result
        ];

        $indicador->content = $total;
        $indicador->bottom_detail = $result->bottom_detail;
        
        return $indicador;

    }

    public function chart($result){

        $chart = [
            'type' => "Doughnut",
            'chartData' => [
                'labels' => ["January", "February", "March"],
                'datasets'=> [
                    [
                        'data' => [$result->total, 100 - $result->total],
                        'backgroundColor' => [
                            "rgb(255, 99, 132)",
                            "rgba(54, 162, 235, 0.1)",
                        ],
                    ]
                ],
            ],
            'chartOptions' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'display' => false,
                    ],
                    'tooltips' => [
                        'enabled' => false
                    ],
                ],
                'scales' => [
                    'y' => [
                        'display' => false,
                    ],
                    'x' => [
                        'display' => false,
                    ],
                ],
            ],
        ];

        return $chart;

    }

    public function get_data($data){

        $ingresados = MCAProcesos::where('codigoclase', 1)
                    ->where('dependencia', 18)
                    ->where('mes_inicial', 5)
                    ->where('anio_inicial', 2022)
                    ->count();

        $anteriores = 0;

        $resueltos = 3;

        $pendientes = ($ingresados + $anteriores) - $resueltos;

        $porcentaje = round(($resueltos / ($ingresados + $anteriores) * 100), 2);

        $response = [
            "total" => $porcentaje,
            'bottom_detail' => [
                [
                    "text" => "Anteriores",
                    "value" => $anteriores,
                ],
                [
                    "text" => "Ingresados",
                    "value" => $ingresados,
                ],
                [
                    "text" => "Resueltos",
                    "value" => $resueltos,
                ],
                [
                    "text" => "Pendientes",
                    "value" => $pendientes,
                ],
            ]
        ];

        return $response;

    }

}