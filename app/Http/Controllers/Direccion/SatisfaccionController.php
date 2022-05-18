<?php

namespace App\Http\Controllers\Direccion;
use App\Http\Controllers\Controller;

use App\Indicador;
use App\Proceso;

class SatisfaccionController extends Controller{

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

    public function chart($data){

        $bottom_detail = $data->bottom_detail;

        $chart = [
            'type' => "Bar",
            'chartData' => [
                'labels' => [$bottom_detail[1]['text'], $bottom_detail[2]['text']],
                'datasets'=> [
                    [
                        'data' => [$bottom_detail[1]['value'], $bottom_detail[2]['value']],
                        'backgroundColor' => [
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
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

        $indicadores = Indicador::where('tipo', 'satisfaccion')->get();

        foreach ($indicadores as $indicador) {
            
            $indicador->date = $data->date;

            app('App\Http\Controllers' . $indicador->controlador)->{$indicador->funcion}($indicador);

        }

        $i = 0;
        $evaluaciones = 0;
        $no_conformes = 0;

        $bottom_detail = [
            [
                'text' => 'Universo',
                'value' => null
            ],
            [
                'text' => 'Aceptable',
                'value' => null
            ],
            [
                'text' => 'No Conforme',
                'value' => null
            ],
        ];

        $indicadores_p = $indicadores;

        // Por cada indicador obtener los valores necesarios para realizar la sumatoria
        foreach ($indicadores as $indicador) {
            
            $evaluaciones += $indicador->evaluaciones;
            $no_conformes += $indicador->no_conformes;

            foreach ($indicador->bottom_detail as $detalle) {
                
                $bottom_detail[$i]['value'] += $detalle['value'];

                $areas = [];

                foreach ($indicadores_p as $indicador_p) {
                    
                    $area = Proceso::find($indicador_p->id_proceso)->area;
        
                    if (array_key_exists('detail', $indicador_p->bottom_detail[$i])) {
                        $area->bottom_detail = $indicador_p->bottom_detail[$i]['detail'];

                        if (count($area->bottom_detail['table']['items']) > 0) {
      
                            $areas [] = $area;
                            
                        }
                        
                    }

                }

                $bottom_detail[$i]['detail'] = $areas;
                $i++;

            }

            $i = 0;
        }

        $response = [
            'indicadores' => $indicadores,
            'total' => $evaluaciones > 0 ? round(100 - (($no_conformes / $evaluaciones) * 100), 1) : 100,
            'bottom_detail' => $bottom_detail,
        ];

        return $response;

    }
}
