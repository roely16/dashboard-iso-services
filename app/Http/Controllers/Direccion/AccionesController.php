<?php 

namespace App\Http\Controllers\Direccion;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

use App\Proceso;

class AccionesController extends Controller{

    public function create($indicador){

        try {
            
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
                'campos' => $indicador->orden_campos ? explode(',', $indicador->orden_campos) : null,
                'estructura_controlador' => $indicador->estructura_controlador,
            ];

            // * Validar la fecha, si es un mes anterior deberá de buscar en el historial

            $current_date = date('Y-m');

            if (strtotime($indicador->date) < strtotime($current_date)) {

                $result = (object) app('App\Http\Controllers\ConfigController')->get_history($data);

            }else{

                $result = (object) $this->data($data);

            }

            $chart = $this->chart($result);

            $content = [
                'chart' => $chart,
            ];

            $result->content = $content;
                        
            $indicador->data = $result;

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
            'type' => "Pie",
            'chartData' => [
                'labels' => [
                   $bottom_detail[1]['text'],
                   $bottom_detail[0]['text']
                ],
                'datasets'=> [
                    [
                        'data' => [
                            $bottom_detail[1]['value'],
                            $bottom_detail[0]['value']
                        ],
                        'backgroundColor' => [
                            'rgb(54, 162, 235)',
                            'rgba(255, 205, 86, 0)'
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
            ],
        ];

        return $chart;

    }

    public function data($data){

        // * Retornar la estructura de datos vacia 
        if (property_exists($data, 'get_structure')) {
            
            return config('app.ACCIONES_DIRECCION');

        }

        $year = date('Y', strtotime($data->date));
        $last_day_month = date('Y-m-t', strtotime($data->date));

        $gestiones = DB::connection('rrhh')->select("   SELECT *
                                                        FROM gc_gestion
                                                        WHERE TO_CHAR(fecha_propuesta_cierre, 'YYYY') = '$year'
                                                        AND ESTATUS_GESTION NOT IN ('PENDIENTE', 'ANULADO')
                                                        AND FECHA_APERTURA_ACCION <= TO_DATE('$last_day_month', 'YYYY-MM-DD')");

        $abiertas = [];
        $cerradas = [];
        $en_tiempo = 0;
        $gestiones_en_tiempo = [];

        foreach ($gestiones as $gestion) {  

            // Contabilizar aquellas acciones que finalizaron en el tiempo estimado
            if ((strtotime($gestion->fecha_real_cierre) <= strtotime($gestion->fecha_propuesta_cierre) && $gestion->estatus_gestion == 'FINALIZADO')) {
               
                $en_tiempo++;
                $gestiones_en_tiempo [] = $gestion;

            }

            if ($gestion->estatus_gestion == 'FINALIZADO' && (strtotime($gestion->fecha_real_cierre) <= strtotime($last_day_month))) {
                
                $cerradas [] = $gestion;

            }elseif ($gestion->estatus_gestion == 'EN PROCESO' || (strtotime($gestion->fecha_real_cierre) >= strtotime($last_day_month))) {
                
                $abiertas [] = $gestion;

            }

        }

        $headers = [
            [
                'text' => 'ID',
                'value' => 'id_gestion',
                'sortable' => false
            ],
            [
                'text' => 'Descripción',
                'value' => 'descripcion',
                'sortable' => false
            ]
        ];

        if (count($gestiones) > 0) {
            
            $avance = round((count($cerradas) / count($gestiones)) * 100, 1);
            $eficacia = round(($en_tiempo / count($gestiones)) * 100, 1);

        }

        $bottom_detail = [
            [
                'text' => 'Abiertas',
                'value' => count($abiertas),
                'detail' => [
                    'table' => [
                        'items' => $abiertas,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableAcciones'
            ],
            [
                'text' => 'Cerradas',
                'value' => count($cerradas),
                'detail' => [
                    'table' => [
                        'items' => $cerradas,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableAcciones'
            ],
            [
                'text' => 'Total',
                'value' => count($gestiones),
                'detail' => [
                    'table' => [
                        'items' => $gestiones,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableAcciones'
            ],
            [
                'text' => 'Avance',
                'value' => $avance . '%',
                'detail' => [
                    'table' => [
                        'items' => [],
                        'headers' => []
                    ]
                ],
                'component' => 'tables/TableAcciones'
            ]
        ];


        $response = [
            'avance' => [
                'text' => 'Avance',
                'value' => $avance
            ],
            'eficacia' => [
                'text' => 'Eficacia',
                'value' => $eficacia,
                'detail' => [
                    'table' => [
                        'items' => $gestiones_en_tiempo,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableAcciones'
            ],
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}