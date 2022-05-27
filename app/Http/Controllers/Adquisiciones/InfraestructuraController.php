<?php

namespace App\Http\Controllers\Adquisiciones;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Compras\Gestion;

class InfraestructuraController extends Controller{

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
                            $bottom_detail[2]['value'],
                            $bottom_detail[0]['value']
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

        // * Obtener las gestiones de infraestructura ingresadas 
        $ingresado = Gestion::whereRaw("to_char(fecha, 'YYYY-MM') = '$data->date'")
                        ->where('tipogestion', 2)
                        ->where('codarearesolver', 1)
                        ->whereIn('tipo_infraestructura', [1,2,3])
                        ->get();

        // * Obtener las gestiones de infraestructura que ya se encuentran en proceso 
        $proceso = Gestion::whereRaw("to_char(fecha, 'YYYY-MM') = '$data->date'")
                    ->where('tipogestion', 2)
                    ->where('codarearesolver', 1)
                    ->whereIn('tipo_infraestructura', [1,2,3])
                    ->where('status', 4)
                    ->get();

        // * Obtener las gestiones de infraestructura que ya fueron finalizadas
        $finalizadas = Gestion::whereRaw("to_char(fecha, 'YYYY-MM') = '$data->date'")
                        ->where('tipogestion', 2)
                        ->where('codarearesolver', 1)
                        ->whereIn('tipo_infraestructura', [1,2,3])
                        ->where('status', 5)
                        ->get();
        
        $bottom_detail = [
            [
                'text' => 'Ingresado',
                'value' => count($ingresado),
            ],
            [
                'text' => 'Proceso',
                'value' => count($proceso)
            ],
            [
                'text' => 'Finalizado',
                'value' => count($finalizadas)
            ]
        ];

        if (count($ingresado) > 0) {
            
            $porcentaje = round((count($finalizadas) / count($ingresado)) * 100, 1);
        
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

?>