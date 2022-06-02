<?php 

namespace App\Http\Controllers\Atencion;
use App\Http\Controllers\Controller;

use App\Models\Atencion\Ticket;
use App\Models\Atencion\IngresoExpediente;

class EficaciaController extends Controller{

    public function create($indicador){

        try {
            
            $data = (object) [
                'date' => $indicador->date,
            ];

            $result = (object) $this->data($data);

            $indicador->data = $result;

            return $result;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage()
            ];

            $indicador->error = $error;

            return $indicador;

        }
    }

    public function data($data){

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
            $itiempo_time = strtotime($ingresado->itiempo);
            $ftiempo_time = strtotime($ingresado->ftiempo);
            
            // * Calcular aquellos que se demoran 10 minutos o menos en la atención
            $ingresado->minutos_atencion = (abs($itiempo_time - $ftiempo_time) / 60);
            
            // * Crear el texto descriptivo del tiempo de atención
            $minutos = intval($ingresado->minutos_atencion) . ' minutos';
            $segundos = intval(($ingresado->minutos_atencion - intval($ingresado->minutos_atencion)) * 60) . ' segundos';

            $ingresado->atencion_tiempo = $minutos . ' y ' . $segundos;

            // * Calcular aquellos que se demoran 20 minutos o menos desde que entra el ticket hasta iniciar la atención
            $ingresado->minutos_cola = (abs($itiempo_time - $f_entrada_time) / 60);

            // * Calcular el texto descriptivo del tiempo en cola
            $minutos = intval($ingresado->minutos_cola) . ' minutos';
            $segundos = intval(($ingresado->minutos_cola - intval($ingresado->minutos_cola)) * 60) . ' segundos';

            $ingresado->cola_tiempo = $minutos . ' y ' . $segundos;

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

        $bottom_detail = [
            [
                'text' => 'Total Tickets',
                'value' => count($ingresados),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $ingresados
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
                        'items' => $ingreso_expedientes
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Cancelados',
                'value' => count($anulados),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $anulados
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ];

        if (count($ingresados) > 0) {
            
            $cumplimiento = round((count($tickets_menor_10) / count($ingresados)) * 100, 1);
            $mensual = round((count($tickets_menor_20) / count($ingresados)) * 100, 1);
            $trimestral = round((count($tickets_menor_45) / count($ingresados)) * 100, 1);

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
                'value' => count($tickets_menor_10),
                'detail' => [
                    'table' => [
                        'headers' => $headers_cumplimiento,
                        'items' => $tickets_menor_10
                    ]
                ],
                'component' => 'tables/TableTicketTime'
            ],
            'menor_20' => [
                'value' => count($tickets_menor_20),
                'detail' => [
                    'table' => [
                        'headers' => $headers_cola,
                        'items' => $tickets_menor_20
                    ]
                ],
                'component' => 'tables/TableTicketTime'
            ],
            'menor_45' => [
                'value' => count($tickets_menor_45),
                'detail' => [
                    'table' => [
                        'headers' => $headers_cola,
                        'items' => $tickets_menor_45
                    ]
                ],
                'component' => 'tables/TableTicketTime'
            ],  
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}