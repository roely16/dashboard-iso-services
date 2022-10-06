<?php

namespace App\Http\Controllers\Eficacia;
use App\Http\Controllers\Controller;

use App\Models\Convenios\Indicador;
use App\Models\Convenios\Convenio;
use App\Models\Convenios\Bitacora;

use Illuminate\Support\Facades\DB;

use App\Proceso;

class ConveniosController extends Controller{

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
                'campos' => $indicador->orden_campos ? explode(',', $indicador->orden_campos) : null
            ];

            // * Validar la fecha, si es un mes anterior deberá de buscar en el historial

            $current_date = date('Y-m');

            if (strtotime($indicador->date) < strtotime($current_date)) {
                
                $result = (object) app('App\Http\Controllers\ConfigController')->get_history($data);

                // * Si la respuesta indica que no existe historial hacer consulta con el método data
                if (property_exists($result, 'history_empty')) {
                    
                    $result = (object) $this->data($data);
                                        
                }
                
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
            $indicador->carga_trabajo = $result->carga_trabajo;
            $indicador->total_resueltos = $result->total_resueltos;
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
        
        // * Obtener el indicador actual  y la meta
        $indicador = Indicador::where('estado', 'A')->first();


        // * Obtener las personas
        $personas = DB::connection('cobros-iusi')->select(" SELECT t1.idpersona, t1.fecha AS fecha_inicio, t2.fecha AS fecha_fin
                                                            FROM mco_persona t1
                                                            INNER JOIN mco_bitacora t2
                                                            ON t1.idpersona = t2.idpersona
                                                            WHERE idindicador = 1
                                                            AND to_char(t1.fecha, 'YYYY-MM') = '$data->date'
                                                            AND t2.idestado = 5");

        $en_tiempo = [];
        $fuera_tiempo = [];

        foreach ($personas as $key => $persona) {
            
            // Realizar el primer calculo de tiempo

            $inicio = strtotime($persona->fecha_inicio);
            $fin = strtotime($persona->fecha_fin);

            $primer_tiempo = (abs($inicio - $fin) / 60);

            $persona->primer_tiempo = $primer_tiempo;

            // Realizar el calculo del segundo tiempo

            // * Obtener como tiempo inicial, el registro en bitacora en estado 6
            $nuevo_inicio = Bitacora::select('fecha')
                            ->where('idpersona', $persona->idpersona)
                            ->where('idestado', 6)
                            ->first();

            // * Obtener como tiempo final, el registro en bitacora en estado 7
            $nuevo_fin = Bitacora::select('fecha')
                        ->where('idpersona', $persona->idpersona)
                        ->where('idestado', 7)
                        ->first();

            $inicio = strtotime($nuevo_inicio ? $nuevo_inicio->fecha : null);
            $fin = strtotime($nuevo_fin ? $nuevo_fin->fecha : null);

            $segundo_tiempo = (abs($inicio - $fin) / 60);

            if ($inicio == 0 || $fin == 0) {
                
                array_splice($personas, $key, 1);

                continue;

            }

            $persona->nuevo_inicio = $nuevo_inicio ? $nuevo_inicio->fecha : null;
            $persona->nuevo_fin = $nuevo_fin ? $nuevo_fin->fecha : null;
            $persona->segundo_tiempo = $segundo_tiempo;

            // Dependiendo del número de convenios se deberá de agregar 300 unidades de tiempo
            $convenios = Convenio::where('idpersona', $persona->idpersona)
                            ->where('estado', 'F')->count();

            $persona->convenios = $convenios;

            $meta = ($indicador->tiempo + ($convenios > 1 ? ($convenios - 1) * 300 : 0)) / 60;

            $persona->meta = $meta;
            $persona->tiempo_total = $primer_tiempo + $segundo_tiempo;

            $minutos = intval($persona->tiempo_total) . ' minutos';
            $segundos = intval(($persona->tiempo_total - intval($persona->tiempo_total)) * 60) . ' segundos';

            $persona->tiempo_descripcion = $minutos . ' y '. $segundos;

            if ($persona->tiempo_total <= $meta) {
                
                $en_tiempo [] = $persona;

            }else{

                $fuera_tiempo [] = $persona;

            }
            
        }

        $headers = [
            [
                'text' => 'ID Persona',
                'value' => 'idpersona'
            ],
            [
                'text' => 'Límite',
                'value' => 'meta'
            ],
            [
                'text' => 'Tiempo',
                'value' => 'tiempo_descripcion'
            ]
        ];

        $bottom_detail = [
            [
                'text' => 'Total',
                'value' => count($personas),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $personas
                    ],
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                'text' => 'En Tiempo',
                'value' => count($en_tiempo),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $en_tiempo
                    ],
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                'text' => 'Fuera de Tiempo',
                'value' => count($fuera_tiempo),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $fuera_tiempo
                    ],
                ],
                'component' => 'tables/TableDetail'
            ]
        ];

        if (count($personas) > 0) {
           
            $porcentaje = round((count($en_tiempo) / count($personas) * 100), 1);

        }else{

            $porcentaje = 0;
        }

        $response = [
            'total' => $porcentaje,
            'carga_trabajo' => 100,
            'total_resueltos' => 100,
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}