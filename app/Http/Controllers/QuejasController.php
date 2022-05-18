<?php

namespace App\Http\Controllers;

use App\Models\Quejas\Proceso as SQProceso;
use App\Models\Satisfaccion\Proceso as MSAProceso;
use App\Models\Satisfaccion\Encabezado;
use App\Models\Quejas\Queja;
use App\Models\Satisfaccion\ModeloEncabezado;
use App\Proceso;

class QuejasController extends Controller{

    public function create($indicador){

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
        $indicador->encuestas = $result->encuestas;
        $indicador->quejas = $result->quejas;

        return $indicador;

    }

    public function chart($data){

        $bottom_detail = $data->bottom_detail;

        $chart = [
            'type' => "Bar",
            'chartData' => [
                'labels' => [
                    $bottom_detail[0]['text'], 
                    $bottom_detail[1]['text'], 
                    $bottom_detail[2]['text']
                ],
                'datasets'=> [
                    [
                        'data' => [
                            $bottom_detail[0]['value'], 
                            $bottom_detail[1]['value'], 
                            $bottom_detail[2]['value']
                        ],
                        'backgroundColor' => [
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(130, 201, 129)'
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

        $sq_proceso = SQProceso::where('rh_areas_codarea', $data->codarea)->first();

        $result_quejas = Queja::where('id_proceso', $sq_proceso->id_proceso)
                    ->where(function($query){
                        $query->where('clasificacion', 'QUEJA')
                        ->orWhere('clasificacion', 'FELICITACION');
                    })
                    ->where('status', 'A')
                    ->whereRaw("to_char(fecha_acuse_recibo, 'YYYY-MM') = '$data->date'")
                    ->get();


        $quejas = [];
        $felicitaciones = [];

        foreach ($result_quejas as $item) {
            
            if ($item->clasificacion == 'QUEJA') {

                $quejas [] = $item;

            }else{

                $felicitaciones [] = $item;

            }

        }

        $msa_procesos = MSAProceso::select('idproceso')
                        ->where('codarea', $data->codarea)
                        ->get()
                        ->pluck('idproceso');

        $modelos_encabezado = ModeloEncabezado::select('id_medicion')
                                ->whereIn('idproceso', $msa_procesos)
                                ->where('nombre_medicion', 'ENCUESTA TELEFONICA')
                                ->get()
                                ->pluck('id_medicion');

        $evaluaciones = Encabezado::whereIn('id_medicion', $modelos_encabezado)
                        ->whereRaw("to_char(fecha_opinion, 'YYYY-MM') = '$data->date'")
                        ->get();

        $headers_evaluaciones = [
            [
                'text' => 'Usuario',
                'value' => 'colaborador'
            ],
            [
                'text' => 'Fecha de Opinión',
                'value' => 'fecha_opinion'
            ]
        ];

        $headers = [
            [
                'text' => 'Fecha',
                'value' => 'fecha_acuse_recibo'
            ],
            [
                'text' => 'Usuario',
                'value' => 'dirigido_a'
            ]
        ];

        $bottom_detail = [
            [
                "text" => "Encuestas",
                "value" => count($evaluaciones),
                'detail' => [
                    'table' => [
                        'headers' => $headers_evaluaciones,
                        'items' => $evaluaciones
                    ]
                ]
            ],
            [
                "text" => "Quejas",
                "value" => count($quejas),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $quejas
                    ]
                ]
            ],
            [
                "text" => "Felicitaciones",
                "value" => count($felicitaciones),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $felicitaciones
                    ]
                ]
            ],
        ];

        $response = [
            'total' => count($evaluaciones) ? round((count($quejas) / count($evaluaciones))* 100, 2) : 0,
            'encuestas' => count($evaluaciones),
            'quejas' => count($quejas),
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}