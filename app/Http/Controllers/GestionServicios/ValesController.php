<?php 

namespace App\Http\Controllers\GestionServicios;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Vales\Vale;
use App\Models\Compras\Gestion;

use Illuminate\Support\Facades\DB;

class ValesController extends Controller {

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
        $mes_posterior = date('Y-m', strtotime($data->date . ' +1 month'));

        // * Obtener los vales pendientes ingresados en el mes anterior donde la fecha de entrega sea null o del mes actual
        $vales_anteriores = DB::connection('rrhh')->select("SELECT t1.*, CONCAT(t2.nombre, CONCAT(' ', t2.apellido)) as nombre_responsable
                                                            FROM adm_vales t1
                                                            INNER JOIN rh_empleados t2
                                                            ON t1.responsable = t2.nit
                                                            WHERE
                                                                to_char(to_date(fecha, 'DD/MM/YY'), 'YYYY-MM') = '$mes_anterior'
                                                            AND (
                                                                to_char(fecha_entrega, 'YYYY-MM') = '$data->date'
                                                                OR fecha_entrega IS NULL
                                                            )
                                                            ORDER BY t1.valeid ASC");

        // * Obtener los vales solicitados en el mes 
        $vales_ingresados = Vale::select(
                                'adm_vales.*',
                                DB::raw("concat(rh_empleados.nombre, CONCAT(' ', rh_empleados.apellido)) as nombre_responsable")
                            )
                            ->join('rh_empleados', 'adm_vales.responsable', '=', 'rh_empleados.nit')
                            ->whereRaw("to_char(to_date(adm_vales.fecha, 'DD/MM/YY'), 'YYYY-MM') = '$data->date'")
                            ->orderBy('adm_vales.valeid', 'ASC')
                            ->get();

        // * Como pendientes se deberán de agregar aquellas gestiónes de vale sin atender
        $gestiones_pendientes = Gestion::select(
                                    'adm_gestiones.gestionid as no_gestion',
                                    DB::raw("to_char(adm_gestiones.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha"),
                                    DB::raw("concat(rh_empleados.nombre, CONCAT(' ', rh_empleados.apellido)) as nombre_responsable")
                                )
                                ->join('rh_empleados', 'adm_gestiones.empleadoid', '=', 'rh_empleados.nit')
                                ->where('tipogestion', 1)
                                ->where('adm_gestiones.status', 0)
                                ->whereRaw("to_char(adm_gestiones.fecha, 'YYYY-MM') = '$data->date'")
                                ->get();

        foreach ($gestiones_pendientes as $gestion) {
            
            $vales_ingresados [] = $gestion;

        }

        // * Obtener los vales finalizados en el mes 
        $vales_finalizados = Vale::select(
                                'adm_vales.*',
                                DB::raw("concat(rh_empleados.nombre, CONCAT(' ', rh_empleados.apellido)) as nombre_responsable")
                            )
                            ->join('rh_empleados', 'adm_vales.responsable', '=', 'rh_empleados.nit')
                            ->whereRaw("
                                to_char(adm_vales.fecha_entrega, 'YYYY-MM') = '$data->date'
                            ")
                            ->orderBy('adm_vales.valeid', 'ASC')
                            ->get();

        // * Un vale pendiente es aquel que no tiene fecha de entrega o bien que fue entregado en el mes siguiente
        $vales_pendientes = Vale::select(
                                'adm_vales.*',
                                DB::raw("concat(rh_empleados.nombre, CONCAT(' ', rh_empleados.apellido)) as nombre_responsable")
                            )
                            ->join('rh_empleados', 'adm_vales.responsable', '=', 'rh_empleados.nit')
                            ->whereRaw("to_char(to_date(adm_vales.fecha, 'DD/MM/YY'), 'YYYY-MM') = '$data->date'")
                            ->whereRaw("(adm_vales.fecha_entrega is null or to_char(adm_vales.fecha_entrega, 'YYYY-MM') = '$mes_posterior')")
                            ->orderBy('adm_vales.valeid', 'ASC')
                            ->get();

        $headers = [
            [
                'text' => 'Vale',
                'value' => 'no_vale'
            ],
            [
                'text' => 'Gestión',
                'value' => 'no_gestion'
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha'
            ],
            [
                'text' => 'Responsable',
                'value' => 'nombre_responsable'
            ]
        ];

        $headers_pendientes = [
            [
                'text' => 'Gestión',
                'value' => 'no_gestion'
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha'
            ],
            [
                'text' => 'Solicitante',
                'value' => 'nombre_responsable'
            ]
        ];
        
        $bottom_detail = [
            [
                'text' => 'Anterior',
                'value' => count($vales_anteriores),
                'detail' => [
                    'table' => [
                        'items' => $vales_anteriores,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Recibido',
                'value' => count($vales_ingresados),
                'detail' => [
                    'table' => [
                        'items' => $vales_ingresados,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Finalizado',
                'value' => count($vales_finalizados),
                'detail' => [
                    'table' => [
                        'items' => $vales_finalizados,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Emitidos',
                'value' => count($vales_pendientes),
                'detail' => [
                    'table' => [
                        'items' => $vales_pendientes,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Pendientes',
                'value' => count($gestiones_pendientes),
                'detail' => [
                    'table' => [
                        'items' => $gestiones_pendientes,
                        'headers' => $headers_pendientes
                    ]
                ],
                'component' => 'tables/TableDetail'
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