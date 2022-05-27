<?php 

namespace App\Http\Controllers\GestionServicios;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Vales\Vale;

use Illuminate\Support\Facades\DB;

class ValesController extends Controller {

    public function create($indicador){

        try {
            
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
                        'data' => [$bottom_detail[2]['value'], $bottom_detail[3]['value']],
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

        $mes_anterior = date('Y-m', strtotime($data->date . ' -1 month'));

        // * Obtener los vales pendientes ingresados en el mes anterior donde la fecha de entrega sea null o del mes actual
        $vales_anteriores = DB::connection('rrhh')->select("SELECT *
                                                            FROM adm_vales
                                                            WHERE
                                                                to_char(to_date(fecha, 'DD/MM/YY'), 'YYYY-MM') = '$mes_anterior'
                                                            
                                                            AND (
                                                                to_char(fecha_entrega, 'YYYY-MM') = '$data->date'
                                                                OR fecha_entrega IS NULL
                                                            )");

        // * Obtener los vales solicitados en el mes 
        $vales_ingresados = Vale::whereRaw("
                                to_char(to_date(fecha, 'DD/MM/YY'), 'YYYY-MM') = '$data->date'
                            ")
                            ->get();

        // * Obtener los vales finalizados en el mes 
        $vales_finalizados = Vale::whereRaw("
                                to_char(fecha_entrega, 'YYYY-MM') = '$data->date'
                            ")
                            ->get();

        // !Si es un mes anterior se deberá de realizar otro tipo de verificación
        $vales_pendientes = Vale::whereRaw("
                                (to_char(to_date(fecha, 'DD/MM/YY'), 'YYYY-MM') = '$mes_anterior'
                                or to_char(to_date(fecha, 'DD/MM/YY'), 'YYYY-MM') = '$data->date')
                            ")
                            ->where('fecha_entrega', null)
                            ->get();

        $bottom_detail = [
            [
                'text' => 'Anterior',
                'value' => count($vales_anteriores)
            ],
            [
                'text' => 'Recibido',
                'value' => count($vales_ingresados)
            ],
            [
                'text' => 'Finalizado',
                'value' => count($vales_finalizados)
            ],
            [
                'text' => 'Pendiente',
                'value' => count($vales_pendientes)
            ]
        ];

        $total_resueltos = count($vales_finalizados);
        $carga_trabajo = count($vales_anteriores) + count($vales_ingresados);
        
        if ($carga_trabajo > 0) {
            
            $porcentaje = round((($total_resueltos / $carga_trabajo) * 100), 1);

        }else{

            $porcentaje = 100;

        }
        

        $response = [
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}