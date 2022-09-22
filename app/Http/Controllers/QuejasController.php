<?php

namespace App\Http\Controllers;

use App\Models\Quejas\Proceso as SQProceso;
use App\Models\Satisfaccion\Proceso as MSAProceso;
use App\Models\Satisfaccion\Encabezado;
use App\Models\Quejas\Queja;
use App\Models\Satisfaccion\ModeloEncabezado;
use App\Models\Satisfaccion\ModeloDetalle;
use App\Proceso;

use Illuminate\Support\Facades\DB;

class QuejasController extends Controller{

    const DATA_STRUCTURE = [

        'total' => 0,
        'encuestas' => 0,
        'quejas' => 0,
        'bottom_detail' => [
            [
                "text" => "Encuestas",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableSatisfaccion'
            ],
            [
                "text" => "Quejas",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                "text" => "Felicitaciones",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                "text" => "Sugerencias",
                "value" => 0,
                'detail' => [
                    'table' => [
                        'headers' => [],
                        'items' => []
                    ],
                ],
                'component' => 'tables/TableDetail'
            ],
        ]
    ]; 

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
            'nombre_historial' => $proceso->nombre_historial,
            'subarea_historial' => 'QUEJAS'
        ];

        $current_date = date('Y-m');

        if (strtotime($indicador->date) < strtotime($current_date)) {
            
            $result = (object) app('App\Http\Controllers\ConfigController')->get_history($data);

        }else{

            $result = (object) $this->data($data);

        }

        $chart = $this->chart($result);

        $total = [
            'total' => [
                'value' => $result->total,
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

        if (property_exists($data, 'get_structure')) {
            
            return self::DATA_STRUCTURE;

        }

        $sq_proceso = SQProceso::where('rh_areas_codarea', $data->codarea)->first();

        $result_quejas = Queja::select(
                            'id_queja',
                            'descripcion',
                            'clasificacion',
                            'dirigido_a',
                            DB::raw("to_char(fecha_acuse_recibo, 'DD/MM/YYYY') as fecha_acuse_recibo")
                        )
                        ->where('id_proceso', $sq_proceso->id_proceso)
                        ->where(function($query){
                            $query->where('clasificacion', 'QUEJA')
                            ->orWhere('clasificacion', 'FELICITACION')
                            ->orWhere('clasificacion', 'SUGERENCIA');
                        })
                        ->where('status', 'A')
                        ->whereRaw("to_char(fecha_acuse_recibo, 'YYYY-MM') = '$data->date'")
                        ->get();


        $quejas = [];
        $felicitaciones = [];
        $sugerencias = [];

        foreach ($result_quejas as $item) {
            
            if ($item->clasificacion == 'QUEJA') {

                $quejas [] = $item;

            }elseif($item->clasificacion == 'FELICITACION'){

                $felicitaciones [] = $item;

            }else{

                $sugerencias [] = $item;

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

        $evaluaciones = Encabezado::select(
                            'correlativo', 
                            'colaborador', 
                            DB::raw("to_char(fecha_opinion, 'DD/MM/YYYY HH24:MI:SS') as fecha_opinion")
                        )
                        ->whereIn('id_medicion', $modelos_encabezado)
                        ->whereRaw("to_char(fecha_opinion, 'YYYY-MM') = '$data->date'")
                        ->get();
        
        foreach ($evaluaciones as &$evaluacion) {

            $eva_num = [];
            $total = 0;

            foreach ($evaluacion->detalle as $detalle) {
                
                // Obtener la pregunta
                $pregunta = ModeloDetalle::where('id_medicion', $detalle->id_medicion)->where('id_pregunta', $detalle->id_pregunta)->first();

                $detalle->pregunta = $pregunta->texto_pregunta;
                $detalle->tipo_valor = $pregunta->tipo_valor;

                if (is_numeric($detalle->valor)) {
                    
                    $total += intval($detalle->valor);
                    $eva_num [] = $detalle;

                }

            }

            $evaluacion->eva_num = $eva_num;

            $nota = round($total / count($eva_num), 2);

            // Validar si es mayor que 8 para tomarla como ACEPTABLE
            if ($nota >= 8) {
                
                $evaluacion->color = 'success';

            }else {

                $evaluacion->color = 'error';
            
            }

            $evaluacion->total = $nota;

        }
        $headers_evaluaciones = [
            [
                'text' => 'Correlativo',
                'value' => 'correlativo',
                'width' => '25%',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'colaborador',
                'width' => '25%',
                'sortable' => false
            ],
            [
                'text' => 'Fecha de Opinión',
                'value' => 'fecha_opinion',
                'width' => '25%',
                'sortable' => false
            ],
            [
                'text' => 'Total',
                'value' => 'total',
                'width' => '25%',
                'align' => 'center',
                'sortable' => false
            ],
            [
                'text' => '',
                'value' => 'data-table-expand'
            ]
        ];

        $headers = [
            [
                'text' => 'Fecha',
                'value' => 'fecha_acuse_recibo',
                'width' => '25%',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'dirigido_a',
                'width' => '25%',
                'sortable' => false
            ],
            [
                'text' => 'Descripción',
                'value' => 'descripcion',
                'width' => '50%',
                'sortable' => false
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
                ],
                'component' => 'tables/TableSatisfaccion'
            ],
            [
                "text" => "Quejas",
                "value" => count($quejas),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $quejas
                    ],
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                "text" => "Felicitaciones",
                "value" => count($felicitaciones),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $felicitaciones
                    ],
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                "text" => "Sugerencias",
                "value" => count($sugerencias),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $sugerencias
                    ],
                ],
                'component' => 'tables/TableDetail'
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