<?php

namespace App\Http\Controllers\GestionServicios;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Capacitaciones\Capacitacion;

use Illuminate\Support\Facades\DB;

class CapacitacionesController extends Controller {

    public function create($indicador){

        try {
            
            $proceso = Proceso::find($indicador->id_proceso);
            $area = $proceso->area;
            $dependencia = $proceso->dependencia;
            
            $data = (object) [
                'codarea' => $area->codarea,
                'date' => $indicador->date,
                'dependencia' => $dependencia
            ];

            $result = (object) $this->data($data);
            $chart = $this->chart($result);

            $total = [
                'total' => [
                    'value' => $result->total . "%",
                ],
                'chart' => $chart
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

        $bottom_detail = $data->bottom_detail;

        $chart = [
            'type' => "Doughnut",
            'chartData' => [
                'datasets'=> [
                    [
                        'data' => [
                            $bottom_detail[1]['value'],
                            $bottom_detail[2]['value']
                        ],
                        'backgroundColor' => [
                            "rgb(128,232,155)",
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

    public function data($data){
        
        // * Obtener el total de capacitacion programadas para el trimestre
        $trimestres = [
            [1,2,3],
            [4,5,6],
            [7,8,9],
            [10,11,12]
        ];

        $split_date = explode('-', $data->date);
        $mes = intval($split_date[1]);
        $year = $split_date[0];

        $trimestre = floor(($mes - 1) / 3) + 1;

        $str_trimestre = implode(',', $trimestres[$trimestre - 1]);

        $capacitaciones = Capacitacion::whereRaw("to_char(fecha_estimada, 'YYYY') = '$year'")
                            ->whereRaw("to_number(to_char(fecha_estimada, 'MM')) IN ($str_trimestre)")
                            ->get();


        $cap = [
            'finalizado' => [],
            'programado' => [],
            'suspendido' => []
        ];

        foreach ($capacitaciones as &$capacitacion) {
            
            $cap[strtolower($capacitacion['estado'])][] = $capacitacion;

        }

        $bottom_detail = [
            [
                'text' => 'Total',
                'value' => count($capacitaciones),
                'capacitaciones' => $cap
            ],
            [
                'text' => 'Realizadas',
                'value' => count($cap['finalizado'])
            ],
            [
                'text' => 'Pendientes',
                'value' => count($cap['programado'])
            ],
            [
                'text' => 'Suspendidas',
                'value' => count($cap['suspendido'])
            ]
        ];

        if(count($capacitaciones) > 0){

            $porcentaje = round((count($cap['finalizado']) / count($capacitaciones)) * 100, 1);

        }else{

            $porcentaje = 0;

        }

        $response = [
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail
        ];

        return $response;
        
    }

}