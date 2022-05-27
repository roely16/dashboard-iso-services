<?php

namespace App\Http\Controllers\Adquisiciones;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Compras\FondoCompra;
use App\Models\Compras\Gestion;
use App\Models\Compras\BitacoraEtapas;

class ComprasController extends Controller{

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

    public function chart(){

        $chart = [
            'type' => "Doughnut",
            'chartData' => [
                'datasets'=> [
                    [
                        'data' => [50, 10],
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
        
        $split_date = explode('-', $data->date);
        $year = $split_date[0];
        $month = intval($split_date[1]);

        // * Obtener las compras ingresadas
        $ingresado = FondoCompra::whereNotIn('distribucion', ['COPEM'])
                        ->where('anio_ejecucion', $year)
                        ->where('programada', $month)
                        ->get();

        $id_fondo_compra = $ingresado->pluck('id_fondo_compra');
        
        // * Obtener las compras que se encuentran en proceso
        $proceso = Gestion::whereIn('id_fondo_compra', $id_fondo_compra)->get();
        
        // * Obtener las compras que se encuentran finalizadas


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
                'value' => 0
            ]
        ];

        $response = [
            'total' => 100,
            'bottom_detail' => $bottom_detail
        ];

        return $response;
        
    }

}

?>