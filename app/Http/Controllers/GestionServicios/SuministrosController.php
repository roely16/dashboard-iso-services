<?php

namespace App\Http\Controllers\GestionServicios;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Suministros\Orden;

use Illuminate\Support\Facades\DB;

class SuministrosController extends Controller {

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

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage()
            ];

            $indicador->error = $error;

            return $indicador;

        }

    }

    public function chart($data){

        $resueltas = $data->resueltas;
        $pendientes = $data->pendientes;

        $chart = [
            'type' => "Doughnut",
            'chartData' => [
                'datasets'=> [
                    [
                        'data' => [$resueltas, $pendientes],
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

        // * Ordenes ingresadas en el mes anterior y que no fueron atendidas o que fueron atendidas hasta el mes siguiente
        
        $anteriores = Orden::select(
                            'adm_ordenes.*',
                            DB::raw("concat(rh_empleados.nombre, CONCAT(' ', rh_empleados.apellido)) as empleado"),
                            DB::raw("to_char(adm_ordenes.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha")
                        )
                        ->join('rh_empleados', 'adm_ordenes.empleadoid', '=', 'rh_empleados.nit')
                        ->whereRaw("to_char(adm_ordenes.fechasolicitudimpresa, 'YYYY-MM') = '$mes_anterior'")
                        ->where(function($query) use ($data){
                            $query->where('adm_ordenes.fechafinalizacion', null)
                            ->orWhereRaw("to_char(adm_ordenes.fechafinalizacion, 'YYYY-MM') = '$data->date'");
                        })
                        ->orderBy('adm_ordenes.ordenid', 'asc')
                        ->get();

        // * Ordenes ingresadas en el mes 
        
        $ingresadas = Orden::select(
                            'adm_ordenes.*',
                            DB::raw("concat(rh_empleados.nombre, CONCAT(' ', rh_empleados.apellido)) as empleado"),
                            DB::raw("to_char(adm_ordenes.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha")
                        )
                        ->join('rh_empleados', 'adm_ordenes.empleadoid', '=', 'rh_empleados.nit')
                        ->whereRaw("to_char(adm_ordenes.fechasolicitudimpresa, 'YYYY-MM') = '$data->date'")
                        ->whereNotIn('adm_ordenes.status', [0, 3])
                        ->orderBy('adm_ordenes.ordenid', 'asc')
                        ->get();

        // * Ordenes finalizadas en el mes
        // ! No se está tomando en cuenta el mes en el que fue ingresado

        $resueltas = Orden::select(
                        'adm_ordenes.*',
                        DB::raw("concat(rh_empleados.nombre, CONCAT(' ', rh_empleados.apellido)) as empleado"),
                        DB::raw("to_char(adm_ordenes.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha")
                    )
                    ->join('rh_empleados', 'adm_ordenes.empleadoid', '=', 'rh_empleados.nit')
                    ->whereRaw("to_char(adm_ordenes.fechafinalizacion, 'YYYY-MM') = '$data->date'")
                    ->orderBy('adm_ordenes.ordenid', 'asc')
                    ->get();

        $headers = [
            [
                'text' => 'ID',
                'value' => 'ordenid',
                'sortable' => false,
                'width' => '20%'
            ],
            [
                'text' => 'Empleado',
                'value' => 'empleado',
                'sortable' => false,
                'width' => '40%'
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha',
                'sortable' => false,
                'width' => '40%'
            ]
        ];            

        $bottom_detail = [
            [
                'text' => 'Anterior',
                'value' => count($anteriores),
                'detail' => [
                    'table' => [
                        'items' => $anteriores,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Ingresadas',
                'value' => count($ingresadas),
                'detail' => [
                    'table' => [
                        'items' => $ingresadas,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Resueltas',
                'value' => count($resueltas),
                'detail' => [
                    'table' => [
                        'items' => $resueltas,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ];

        $carga_trabajo = count($anteriores) + count($ingresadas);
        $pendientes = $carga_trabajo - count($resueltas);

        if ($carga_trabajo > 0) {

            $porcentaje = round((count($resueltas) / $carga_trabajo) * 100, 1);

        }else{

            $porcentaje = 100;

        }

        $response = [
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail,
            'resueltas' => count($resueltas),
            'pendientes' => $pendientes
        ];

        return $response;
    }

}