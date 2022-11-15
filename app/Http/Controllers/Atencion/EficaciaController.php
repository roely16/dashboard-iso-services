<?php 

namespace App\Http\Controllers\Atencion;
use App\Http\Controllers\Controller;

use App\Models\Atencion\Ticket;
use App\Models\Atencion\IngresoExpediente;
use App\Proceso;

use Illuminate\Support\Facades\DB;

class EficaciaController extends Controller{

    public function create($indicador){

        try {

            $data = $indicador->kpi_data;

            // * Validar si es una consulta de un mes posterior o actual 
            $result = (object) app('App\Http\Controllers\ValidationController')->check_case($indicador);

            $result = $result->data ? $result->data : (object) $this->data($data);

            if ($result->cumplimiento > 100) {
                    
                $result->cumplimiento = 100;
                
            }

            $indicador->total = $result->cumplimiento;
            $indicador->bottom_detail = [];
            $indicador->data = $result;

            return $result;

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

    public function data($data){

        // * Retornar la estructura de datos vacia 
        if (property_exists($data, 'get_structure')) {
            
            return config('app.EFICACIA_ATENCION');

        }

        $split_date = explode('-', $data->date);
        $year = $split_date[0];
        $month = $split_date[1];

        $date_fixed = $month . $year;

        $trimestre_vencimiento = [1, 4, 7, 10];

        // * Obtener el total de tickets ingresados 
        $ingresados = Ticket::whereRaw("to_char(itiempo, 'YYYY-MM') = '$data->date'")
                            ->whereNotNull('ftiempo')
                            ->orderBy('id_transaccion', 'asc')
                            ->get();

        // * Obtener los tickets anulados
        $anulados = Ticket::whereIn('id_grupo', [9, 10])
                        ->where('ftiempo', null)
                        ->whereRaw("comentario not like '%SITUACI%N ESPECIAL POR FIN DE TRIMESTRE%'")
                        ->whereRaw("to_char(f_entrada, 'YYYY-MM') = '$data->date'")
                        ->get();

        // * Ingreso de expedientes
        $ingreso_expedientes = IngresoExpediente::whereRaw("to_char(fecha, 'YYYY-MM') = '$data->date'")
                                ->orderBy('id_ingreso_expediente', 'asc')
                                ->get();

        // ID Grupo que aplican 
        $id_grupo = [9, 10];

        $tickets_menor_10 = [];
        $tickets_menor_20 = [];
        $tickets_menor_45 = [];

        foreach ($ingresados as $ingresado) {
            
            $f_entrada_time = strtotime($ingresado->f_entrada);
            $f_llamada = strtotime($ingresado->f_llamada);

            $itiempo_time = strtotime($ingresado->itiempo);
            $ftiempo_time = strtotime($ingresado->ftiempo);
            
            // * Calcular aquellos que se demoran 10 minutos o menos en la atención
            $ingresado->minutos_atencion = (abs($itiempo_time - $ftiempo_time) / 60);
            $ingresado->minutos_cola = (abs($f_llamada - $f_entrada_time) / 60);
            
            // * Crear el texto descriptivo del tiempo de atención
            $minutos = intval($ingresado->minutos_atencion) . ' minutos';
            $segundos = intval(($ingresado->minutos_atencion - intval($ingresado->minutos_atencion)) * 60) . ' segundos';

            $ingresado->atencion_tiempo = $minutos . ' y ' . $segundos;

            // * Calcular aquellos que se demoran 20 minutos o menos desde que entra el ticket hasta iniciar la atención
            //$ingresado->minutos_cola = (abs($itiempo_time - $f_entrada_time) / 60);

            // * Calcular el texto descriptivo del tiempo en cola
            $minutos = intval($ingresado->minutos_cola) . ' minutos';
            $segundos = intval(($ingresado->minutos_cola - intval($ingresado->minutos_cola)) * 60) . ' segundos';

            //$ingresado->cola_tiempo = $minutos . ' y ' . $segundos;

            if ($ingresado->minutos_atencion <= 10 && in_array(intval($ingresado->id_grupo), $id_grupo, true)) {
                
                $tickets_menor_10 [] = $ingresado;

            }

            if($ingresado->minutos_cola <= 20 && in_array(intval($ingresado->id_grupo), $id_grupo, true)){

                $tickets_menor_20 [] = $ingresado;

            }

            if($ingresado->minutos_cola <= 45 && in_array(intval($ingresado->id_grupo), $id_grupo, true)){

                $tickets_menor_45 [] = $ingresado;

            }

        }

        $headers = [
            [
                'text' => 'ID',
                'value' => 'id_transaccion',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario',
                'sortable' => false
            ],
            [
                'text' => 'Tiempo de Atención',
                'value' => 'tiempo_atencion',
                'sortable' => false
            ],
            [
                'text' => 'Tiempo en Cola',
                'value' => 'tiempo_cola',
                'sortable' => false
            ],
            [
                'text' => '',
                'value' => 'data-table-expand'
            ]
        ];

        $headers_cumplimiento = [
            [
                'text' => 'ID',
                'value' => 'id_transaccion',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario',
                'sortable' => false
            ],
            [
                'text' => 'Tiempo de Atención',
                'value' => 'tiempo_atencion',
                'sortable' => false
            ],
            [
                'text' => '',
                'value' => 'data-table-expand'
            ]
        ];

        $headers_cola = [
            [
                'text' => 'ID',
                'value' => 'id_transaccion',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario',
                'sortable' => false
            ],
            [
                'text' => 'Tiempo en Cola',
                'value' => 'tiempo_cola',
                'sortable' => false
            ],
            [
                'text' => '',
                'value' => 'data-table-expand'
            ]
        ];

        $headers_docs = [
            [
                'text' => 'ID',
                'value' => 'id_ingreso_expediente',
                'sortable' => false
            ],
            [
                'text' => 'Expediente',
                'value' => 'expediente',
                'sortable' => false
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario',
                'sortable' => false
            ]
        ];



        // Total de tickets
        $total = DB::connection('catastrousr')->select("   SELECT * 
                                                            FROM SGC.INDICADOR_TICKETS_FINAL
                                                            WHERE TO_CHAR(F_ENTRADA,'YYYY-MM') IN ('$data->date')
                                                            AND CODIGO_STATUS IN (4)
                                                            AND UPPER(DESCRIPCION) IN ('CATASTRO','IUSI')
                                                            AND F_LLAMADA IS NOT NULL
                                                            AND ITIEMPO IS NOT NULL");

        $menor_10 = DB::connection('catastrousr')->select(" SELECT *
                                                            FROM SGC.INDICADOR_TICKETS_FINAL A
                                                            WHERE (FTIEMPO - ITIEMPO) * 24 * 60 <= 10
                                                            AND F_LLAMADA IS NOT NULL
                                                            AND ITIEMPO IS NOT NULL
                                                            AND CODIGO_STATUS IN (4) AND UPPER(DESCRIPCION) IN ('CATASTRO','IUSI')
                                                            AND TO_CHAR(ITIEMPO,'YYYY-MM') = '$data->date'
                                                        ");

        $menor_20 = DB::connection('catastrousr')->select(" SELECT *
                                                            FROM SGC.INDICADOR_TICKETS_FINAL A
                                                            WHERE (F_LLAMADA - F_ENTRADA)*24*60 <= 20
                                                            AND F_LLAMADA IS NOT NULL AND ITIEMPO IS NOT NULL
                                                            AND UPPER(DESCRIPCION) IN ('CATASTRO','IUSI')
                                                            AND TO_CHAR(ITIEMPO,'YYYY-MM') = '$data->date'");

        $menor_45 = DB::connection('catastrousr')->select(" SELECT *
                                                                FROM SGC.INDICADOR_TICKETS_FINAL A
                                                                WHERE (F_LLAMADA - F_ENTRADA) * 24 * 60 <= 45
                                                                AND F_LLAMADA IS NOT NULL AND ITIEMPO IS NOT NULL
                                                                AND CODIGO_STATUS IN (4) AND UPPER(DESCRIPCION) IN ('CATASTRO','IUSI')
                                                                AND TO_CHAR(ITIEMPO,'YYYY-MM') = '$data->date'");

        $cancelados = DB::connection('catastrousr')->select("   SELECT *
                                                                FROM SGC.INDICADOR_TICKETS_FINAL A
                                                                WHERE TO_CHAR(F_ENTRADA, 'YYYY-MM') = '$data->date'
                                                                AND UPPER(DESCRIPCION) IN ('CATASTRO','IUSI')
                                                                AND (CODIGO_STATUS <> 4 OR (F_LLAMADA IS NULL OR ITIEMPO IS NULL) )");

        $bottom_detail = [
            [
                'text' => 'Total Tickets',
                'value' => count($total),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableTicketTime'
            ],
            [
                'text' => 'Recepción Docs',
                'value' => count($ingreso_expedientes),
                'detail' => [
                    'table' => [
                        'headers' => $headers_docs,
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Cancelados',
                'value' => count($cancelados),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ];

        if (count($total) > 0) {
            
            $cumplimiento = round((count($menor_10) / count($total)) * 100, 2);
            $mensual = round((count($menor_20) / count($total)) * 100, 2);
            $trimestral = in_array(intval($month), $trimestre_vencimiento) ? round((count($menor_45) / count($total)) * 100, 2) : 0;

        }else{

            $cumplimiento = 0;
            $mensual = 0;
            $trimestral = 0;

        }

        $response = [
            'cumplimiento' => $cumplimiento,
            'mensual' => $mensual,
            'trimestral' => $trimestral,
            'menor_10' => [
                'value' => count($menor_10),
                'detail' => [
                    'table' => [
                        'headers' => $headers_cumplimiento,
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableTicketTime'
            ],
            'menor_20' => [
                'value' => count($menor_20),
                'detail' => [
                    'table' => [
                        'headers' => $headers_cola,
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableTicketTime'
            ],
            'menor_45' => [
                'value' => in_array(intval($month), $trimestre_vencimiento) ? count($menor_45) : 0,
                'detail' => [
                    'table' => [
                        'headers' => $headers_cola,
                        'items' => []
                    ]
                ],
                'component' => 'tables/TableTicketTime'
            ],  
            'bottom_detail' => $bottom_detail,
            'total' => $cumplimiento
        ];

        return $response;

    }

    public function detalle_tiempo($data){

    }

}