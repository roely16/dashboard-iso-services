<?php

namespace App\Http\Controllers\Adquisiciones;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Compras\Gestion;

use Illuminate\Support\Facades\DB;

class ContratosController extends Controller{

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
            'type' => "Doughnut",
            'chartData' => [
                'datasets'=> [
                    [
                        'data' => [
                            $bottom_detail[2]['value'],
                            $bottom_detail[0]['value'] - $bottom_detail[2]['value']
                        ],
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
        
        // Programado
        $programado = DB::connection('rrhh')->select("  SELECT 
                                                            t1.id_programacion,
                                                            t1.numero_cuotas,
                                                            t1.descripcion,
                                                            t1.usuario,
                                                            t1.cantidad_meses,
                                                            to_char(t2.fecha_programado, 'DD/MM/YYYY') as fecha_programado,
                                                            t2.numero_cuota,
                                                            concat(to_char(t2.periodo_del, 'DD/MM/YYYY'), concat(' - ', to_char(t2.periodo_al, 'DD/MM/YYYY'))) as periodo,
                                                            t3.nombre as presupuesto
                                                        from adm_programacion t1, 
                                                        adm_detalleprogramacion t2,
                                                        adm_tipopresupuesto t3
                                                        where t1.id_programacion = t2.id_programacion
                                                        and t1.id_tipopresupuesto = t3.id_tipopresupuesto
                                                        and t1.estado = 'A'
                                                        and to_char(t2.fecha_programado, 'YYYY-MM') = '$data->date'");

        // Obtener un detalle más amplio de cada contrato

        foreach ($programado as &$contrato) {
            
            // Obtener el detalle de la programación
            $detalle_progra = DB::connection('rrhh')->select("  SELECT 
                                                                    id_detprogramacion,
                                                                    id_programacion,
                                                                    to_char(fecha_programado, 'DD/MM/YYYY') as fecha_programado,
                                                                    monto_programado,
                                                                    id_gestion,
                                                                    numero_cuota,
                                                                    concat(to_char(periodo_del, 'DD/MM/YYYY'), concat(' - ', to_char(periodo_al, 'DD/MM/YYYY'))) as periodo
                                                                FROM adm_detalleprogramacion 
                                                                WHERE id_programacion = $contrato->id_programacion");
            $contrato->detalle_progra = $detalle_progra;

            // Obtener la gestión de la programación
            $gestion_progra = DB::connection('rrhh')->select("  SELECT *
                                                                FROM adm_gestionprogramacion t1
                                                                LEFT JOIN adm_gestiones t2
                                                                ON t1.id_gestion = t2.gestionid
                                                                WHERE id_programacion = $contrato->id_programacion
                                                                ORDER BY fechafinalizacion");

            $contrato->gestion_progra = $gestion_progra;

        }

        $headers_progra = [
            [
                'text' => 'ID',
                'value' => 'id_programacion',
                'sortable' => false,
                'width' => '5%'
            ],
            [
                'text' => 'Descripción',
                'value' => 'descripcion',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Meses',
                'value' => 'cantidad_meses',
                'sortable' => false,
                'width' => '5%'
            ],
            [
                'text' => 'Cuotas',
                'value' => 'numero_cuotas',
                'sortable' => false,
                'width' => '5%'
            ],
            [
                'text' => 'Programado',
                'value' => 'fecha_programado',
                'sortable' => false,
                'width' => '10%'
            ],
            [
                'text' => 'Cuota Número',
                'value' => 'numero_cuota',
                'sortable' => false,
                'width' => '10%'
            ],
            [
                'text' => 'Periodo',
                'value' => 'periodo',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Presupuesto',
                'value' => 'presupuesto',
                'sortable' => false,
                'width' => '15%'
            ],
            [
                'text' => '',
                'value' => 'data-table-expand'
            ]
        ];

        // En Proceso
        // * Son todos aquellos que no tienen gestion o bien factura
        $en_proceso = DB::connection('rrhh')->select("SELECT 
                                                            t1.id_programacion,
                                                            t1.numero_cuotas,
                                                            t1.descripcion,
                                                            t1.usuario,
                                                            t1.cantidad_meses,
                                                            to_char(t2.fecha_programado, 'DD/MM/YYYY') as fecha_programado,
                                                            t2.numero_cuota,
                                                            concat(to_char(t2.periodo_del, 'DD/MM/YYYY'), concat(' - ', to_char(t2.periodo_al, 'DD/MM/YYYY'))) as periodo,
                                                            t3.nombre as presupuesto
                                                        from adm_programacion t1, 
                                                        adm_detalleprogramacion t2,
                                                        adm_tipopresupuesto t3
                                                        where t1.id_programacion = t2.id_programacion
                                                        and t1.id_tipopresupuesto = t3.id_tipopresupuesto
                                                        and t1.estado = 'A'
                                                        and to_char(t2.fecha_programado, 'YYYY-MM') = '$data->date'
                                                        and t1.id_programacion not in (
                                                            SELECT  a.id_programacion
                                                            FROM adm_programacion a,
                                                                adm_gestionprogramacion b,
                                                                adm_gestiones c,
                                                                adm_facturas d
                                                            WHERE  a.id_programacion = b.id_programacion
                                                                AND b.id_gestion = c.gestionid
                                                                AND c.gestionid = d.gestionid
                                                                AND TO_CHAR(c.fechafinalizacion,'YYYY-MM') = '$data->date'
                                                        )");
        
        /* 
            * Finalizados son aquellos que tienen factura
        */
        $finalizados = DB::connection('rrhh')->select(" SELECT  *
                                                        FROM  adm_programacion a,
                                                            adm_gestionprogramacion b,
                                                            adm_gestiones c,
                                                            adm_facturas d
                                                        WHERE  a.id_programacion = b.id_programacion
                                                            AND b.id_gestion = c.gestionid
                                                            AND c.gestionid = d.gestionid
                                                            AND TO_CHAR(c.fechafinalizacion,'YYYY-MM') = '$data->date'");

        $headers_finalizados = [
            [
                'text' => 'ID Contrato',
                'value' => 'id_programacion',
                'sortable' => false,
                'width' => '5%'
            ],
            [
                'text' => 'Descripción',
                'value' => 'descripcion',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'ID Gestión',
                'value' => 'gestionid',
                'sortable' => false,
                'width' => '10%'
            ],
            [
                'text' => 'Gestión',
                'value' => 'titulo',
                'sortable' => false,
                'width' => '25%'
            ]
        ];

        foreach ($programado as $item) {
            
            // Buscar las cuotas por cada uno de los contratos

        }

        $bottom_detail = [
            [
                'text' => 'Anteriores', 
                'value' => 0,
                'detail' => [
                    'table' => [
                        'headers' => $headers_progra,
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableContratos',
                'fullscreen' => true
            ],
            [
                'text' => 'Programado', 
                'value' => count($programado),
                'detail' => [
                    'table' => [
                        'headers' => $headers_progra,
                        'items' => $programado
                    ]
                ],
                'component' => 'tables/TableContratos',
                'fullscreen' => true
            ],
            [
                'text' => 'En Proceso', 
                'value' => count($en_proceso),
                'detail' => [
                    'table' => [
                        'headers' => $headers_progra,
                        'items' => $en_proceso
                    ]
                ],
                'component' => 'tables/TableDetail',
                'fullscreen' => true
            ],  
            [
                'text' => 'Finalizado', 
                'value' => count($finalizados),
                'detail' => [
                    'table' => [
                        'headers' => $headers_finalizados,
                        'items' => $finalizados
                    ]
                ],
                'component' => 'tables/TableDetail',
                'fullscreen' => true
            ]
        ];

        if (count($programado) > 0) {
            
            $porcentaje = round((count($finalizados) / count($programado)) * 100, 1);
        
        }else{

            $porcentaje = 0;

        }

        $response = [
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail
        ];

        return $response;
        
    }

}

?>