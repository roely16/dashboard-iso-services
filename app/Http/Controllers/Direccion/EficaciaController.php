<?php

namespace App\Http\Controllers\Direccion;
use App\Http\Controllers\Controller;

use App\Indicador;
use App\Proceso;

class EficaciaController extends Controller{

    public function create($indicador){

        $data = (object) [
            'date' => $indicador->date
        ];

        $result = (object) $this->data($data);

        $chart = $this->chart($result);

        $total = [
            'data' => $result,
            'total' => [
                'value' =>  $result->total . "%",
            ],
            'chart' => $chart
        ];

        $indicador->content = $total;
        $indicador->bottom_detail = $result->bottom_detail;

        return $indicador;

    } 
    
    public function chart($result){

        $chart = [
            'type' => "Doughnut",
            'chartData' => [
                'datasets'=> [
                    [
                        'data' => [$result->total, 100 - $result->total],
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

        // Obtener la lista de procesos que tienen un indicador de eficacia
        $indicadores = Indicador::where('tipo', 'eficacia')->get();

        foreach ($indicadores as $indicador) {
            
            $indicador->date = $data->date;

            app('App\Http\Controllers' . $indicador->controlador)->{$indicador->funcion}($indicador);

        }

        $i = 0;
        $carga_trabajo = 0;
        $total_resueltos = 0;

        $bottom_detail = [
            [
                'text' => 'Anteriores',
                'value' => null,
                'component' => 'tables/TableProcesos'
            ],
            [
                'text' => 'Ingresados',
                'value' => null,
                'component' => 'tables/TableProcesos'
            ],
            [
                'text' => 'Resueltos',
                'value' => null,
                'component' => 'tables/TableProcesos'
            ],
            [
                'text' => 'Pendientes',
                'value' => null,
                'component' => 'tables/TableProcesos'
            ]
        ];

        $indicadores_p = $indicadores;

        // Por cada indicador obtener los valores necesarios para realizar la sumatoria
        foreach ($indicadores as $indicador) {
            
            $carga_trabajo += $indicador->carga_trabajo;
            $total_resueltos += $indicador->total_resueltos;

            foreach ($indicador->bottom_detail as $detalle) {
                
                $bottom_detail[$i]['value'] += $detalle['value'];

                $areas = [];

                foreach ($indicadores_p as $indicador_p) {
                    
                    $area = Proceso::find($indicador_p->id_proceso)->area;

                    $empty_table = [
                        'table' => [
                            'headers' => [],
                            'items' => []
                        ]
                    ];

                    $area->bottom_detail = array_key_exists('detail', $indicador_p->bottom_detail[$i]) ? $indicador_p->bottom_detail[$i]['detail'] : $empty_table;
                    $area->component = array_key_exists('component', $indicador_p->bottom_detail[$i]) ? $indicador_p->bottom_detail[$i]['component'] : null;

                    $areas [] = $area;

                }

                $bottom_detail[$i]['detail'] = $areas;
                $i++;

            }

            $i = 0;
        }

        $porcentaje = round(($total_resueltos / ($carga_trabajo) * 100), 1);

        $response = [
            'indicadores' => $indicadores,
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail,
        ];

        return $response;

    }

}