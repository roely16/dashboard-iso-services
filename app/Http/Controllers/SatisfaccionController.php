<?php 

namespace App\Http\Controllers;

use App\Proceso;
use App\Models\Satisfaccion\Proceso as MSAProceso;
use App\Models\Satisfaccion\Encabezado;
use App\Models\Satisfaccion\ModeloEncabezado;
use App\Models\Satisfaccion\ModeloDetalle;

use Illuminate\Support\Facades\DB;

class SatisfaccionController extends Controller{
   
    public function create($indicador){

        try {
            
            $data = $indicador->kpi_data;

            // * Validar si es una consulta de un mes posterior o actual 
            $result = (object) app('App\Http\Controllers\ValidationController')->check_case($indicador);

            $result = $result->data ? $result->data : (object) $this->data($data);

            $chart = $this->chart($result);

            $total = [
                'total' => [
                    'value' => $result->total,
                ],
                'chart' => $chart
            ];

            $indicador->content = $total;

            $indicador->bottom_detail = $result->bottom_detail;
            $indicador->evaluaciones = $result->evaluaciones;
            $indicador->no_conformes = $result->no_conformes;
            $indicador->data = $result;

            return $indicador;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ];

            $indicador->error = $error;

            return $indicador;

        }

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

        try {
            
            if (property_exists($data, 'get_structure')) {
            
                return config('satisfaccion_json.SATISFACCION');
    
            }
    
            $msa_procesos = MSAProceso::select('idproceso')->where('codarea', $data->codarea)->get()->pluck('idproceso');
    
            $modelos_encabezado = ModeloEncabezado::select('id_medicion')
                                    ->whereIn('idproceso', $msa_procesos)
                                    ->where('estado', 'A')
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
    
            $colaboradores = Encabezado::select(
                                'colaborador'
                            )
                            ->whereIn('id_medicion', $modelos_encabezado)
                            ->whereRaw("to_char(fecha_opinion, 'YYYY-MM') = '$data->date'")
                            ->groupBy('colaborador')
                            ->get();
    
            $aceptables = [];
            $no_conformes = [];
    
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
    
                    $aceptables [] = $evaluacion;
    
                }else {
    
                    $evaluacion->color = 'error';
    
                    $no_conformes [] = $evaluacion;
                
                }
    
                $evaluacion->total = $nota;
    
            }
    
            $preguntas_catalog = [];

            // Obtener el catalogo de preguntas para el proceso en cuestión
            $procesos_array = $msa_procesos->toArray();
            $procesos = implode(",", $procesos_array);

            $consulta_preguntas = DB::connection('rrhh')->select("  SELECT *
                                                                    FROM MSA_MODELO_DETALLE
                                                                    WHERE ID_MEDICION IN (
                                                                        SELECT ID_MEDICION
                                                                        FROM MSA_MODELO_ENCABEZADO
                                                                        WHERE IDPROCESO IN ($procesos)
                                                                        AND ESTADO = 'A'
                                                                        AND NOMBRE_MEDICION = '$data->nombre_medicion'
                                                                    )
                                                                    AND TIPO_OBJETO = 'RADIO'");

            foreach ($consulta_preguntas as $key => &$pregunta) {
                
                $preguntas_catalog [] = 'pregunta_' . ($key + 1);

            }

            $table_detail = [];
            $promedio_general = 0;

            // * Armar el objeto para sacar promedios de promedios
            foreach ($colaboradores as &$colaborador) {
                                        
                $temp = (object) [];
                $temp->nombre = $colaborador->colaborador;
                $temp_promedio = 0;
    
                foreach ($preguntas_catalog as $key=>$pregunta) {
                    
                    $total = 0;
                    $notas = [];
                    
                    $temp->{$pregunta} = 1;

                    // * Sacar el promedio de todos las evaluaciones del colaborador por cada pregunta
                    foreach ($evaluaciones as &$evaluacion) {
                        
                        // $e = 1;

                        if ($colaborador->colaborador == $evaluacion->colaborador) {
                            
                            // $e++;

                            if (count($evaluacion->eva_num) == count($preguntas_catalog)) {
                                
                                $total += $evaluacion->eva_num[$key]->valor;
                                $notas [] = $evaluacion->eva_num[$key]->valor;

                            }
                            
                            
                        }
        
                    }

                    $temp->{$pregunta} = count($notas) > 0 ? round($total / count($notas), 2) : 0; 

                    $temp_promedio += $temp->{$pregunta}; 

                }

                $temp->promedio = round(($temp_promedio / count($preguntas_catalog) * 10), 2);
                $table_detail [] = $temp;

                $promedio_general += $temp->promedio;
    
            }
            
            $detalle_satisfaccion = ['table' => [
                                        'items' => $table_detail,
                                        'headers' => [
                                            [
                                                'text' => 'Usuario',
                                                'value' => 'nombre',
                                                'width' => '25%',
                                                'sortable' => false
                                            ],
                                        ]
                                    ]];

            foreach ($preguntas_catalog as $key => $pregunta) {
                
                $temp = [
                    'text' => 'Pregunta ' . ($key + 1),
                    'value' => $pregunta,
                    'width' => '15%',
                    'sortable' => false,
                    'align' => 'center'
                ];

                array_push($detalle_satisfaccion['table']['headers'], $temp);

            }

            $temp = [
                'text' => 'Promedio',
                'value' => 'promedio',
                'width' => '25%',
                'sortable' => false,
                'align' => 'center'
            ];

            array_push($detalle_satisfaccion['table']['headers'], $temp);

            $headers = [
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
    
            $bottom_detail = [
                [
                    "text" => 'Universo',
                    "value" => count($evaluaciones),
                    'detail' => [
                        'table' => [
                            'headers' => $headers,
                            'items' => $evaluaciones
                        ],
                    ],
                    'component' => 'tables/SatisfaccionDetalle',
                    'divide' => 'down',
                    'resumen' => $detalle_satisfaccion
                ],
                [
                    "text" => 'Aceptable',
                    "value" => count($aceptables),
                    'detail' => [
                        'table' => [
                            'headers' => $headers,
                            'items' => $aceptables
                        ],
                    ],
                    'component' => 'tables/SatisfaccionDetalle',
                    'divide' => 'up',
                    'resumen' => $detalle_satisfaccion
                ],
                [
                    "text" => 'No Conforme',
                    "value" => count($no_conformes),
                    'detail' => [
                        'table' => [
                            'headers' => $headers,
                            'items' => $no_conformes
                        ],
                    ],
                    'component' => 'tables/SatisfaccionDetalle',
                    'resumen' => $detalle_satisfaccion
                ],
            ];
    
            $response = [
                // 'total' => count($evaluaciones) > 0 ? round(100 - ((count($no_conformes) / count($evaluaciones)) * 100), 2) : 100,
                'total' => round(($promedio_general / count($table_detail)), 2),
                'evaluaciones' => count($evaluaciones),
                'no_conformes' => count($no_conformes),
                'bottom_detail' => $bottom_detail,
                'colaboradores' => $colaboradores,
            ];
    
            return $response;

        } catch (\Throwable $th) {
            
            dd(['message' => $th->getMessage(), 'line' => $th->getLine(), 'file' => $th->getFile()]);

        }
        

    }

}