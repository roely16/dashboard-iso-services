<?php

namespace App\Http\Controllers\GestionServicios;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Suministros\Orden;

use Illuminate\Support\Facades\DB;

class SuministrosController extends Controller {

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

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage()
            ];

            $indicador->error = $error;

            return $indicador;

        }

    }

    public function chart($data){

        $resueltas = $data->resueltas;
        $pendientes = $data->pendientes;

        $chart = [
            'type' => "Doughnut",
            'chartData' => [
                'datasets'=> [
                    [
                        'data' => [$resueltas, $pendientes],
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
        
        $mes_anterior = date('Y-m', strtotime($data->date . ' -1 month'));

        // * Ordenes ingresadas en el mes anterior y que no fueron atendidas o que fueron atendidas hasta el mes siguiente
        
        $anteriores = Orden::whereRaw("to_char(fechasolicitudimpresa, 'YYYY-MM') = '$mes_anterior'")
                        ->where(function($query) use ($data){
                            $query->where('fechafinalizacion', null)
                            ->orWhereRaw("to_char(fechafinalizacion, 'YYYY-MM') = '$data->date'");
                        })
                        ->get();

        // * Ordenes ingresadas en el mes 
        
        $ingresadas = Orden::whereRaw("to_char(fechasolicitudimpresa, 'YYYY-MM') = '$data->date'")
                        ->whereNotIn('status', [0, 3])
                        ->get();

        // * Ordenes finalizadas en el mes
        // ! No se está tomando en cuenta el mes en el que fue ingresado

        $resueltas = Orden::whereRaw("to_char(fechafinalizacion, 'YYYY-MM') = '$data->date'")
                    ->get();

        $bottom_detail = [
            [
                'text' => 'Anterior',
                'value' => count($anteriores)
            ],
            [
                'text' => 'Ingresadas',
                'value' => count($ingresadas)
            ],
            [
                'text' => 'Resueltas',
                'value' => count($resueltas)
            ]
        ];

        $carga_trabajo = count($anteriores) + count($ingresadas);
        $pendientes = $carga_trabajo - count($resueltas);

        if ($carga_trabajo > 0) {

            $porcentaje = round((count($resueltas) / $carga_trabajo) * 100, 1);

        }else{

            $porcentaje = 100;

        }

        $response = [
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail,
            'resueltas' => count($resueltas),
            'pendientes' => $pendientes
        ];

        return $response;
    }

}