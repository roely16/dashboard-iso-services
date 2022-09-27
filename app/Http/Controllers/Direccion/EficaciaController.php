<?php

namespace App\Http\Controllers\Direccion;
use App\Http\Controllers\Controller;

use App\Indicador;
use App\Proceso;

class EficaciaController extends Controller{

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
        $indicador->indicadores = $result->indicadores;

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
                'component' => 'tables/TableProcesos',
                'fullscreen' => true
            ],
            [
                'text' => 'Ingresados',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true
            ],
            [
                'text' => 'Resueltos',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true
            ],
            [
                'text' => 'Pendientes',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true
            ]
        ];

        $indicadores_p = $indicadores;

        // Por cada indicador obtener los valores necesarios para realizar la sumatoria
        foreach ($indicadores as &$indicador) {
            
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

        
        $porcentaje = 0;

        if ($carga_trabajo > 0) {
            
            $porcentaje = round(($total_resueltos / ($carga_trabajo) * 100), 1);

        }
       
        $response = [
            'indicadores' => $indicadores,
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail,
        ];

        return $response;

    }

}