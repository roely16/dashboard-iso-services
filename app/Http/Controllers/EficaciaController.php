<?php

namespace App\Http\Controllers;

use App\Historial;
use App\Proceso;
use App\MCAProcesos;

use Illuminate\Support\Facades\DB;

class EficaciaController extends Controller{

    public function create($indicador){

        try {

            $data = $indicador->kpi_data;

            // * Validar si es una consulta de un mes posterior o actual 
            $result = (object) app('App\Http\Controllers\ValidationController')->check_case($indicador);

            $result = $result->data ? $result->data : (object) $this->data($data);

            $chart = $this->chart($result);

            // * Validar que no sea mayor a 100
            if ($result->total > 100) {
                    
                $result->total = 100;
                
            }

            $total = [
                'total' => [
                    'value' => property_exists($result, 'total') ? $result->total : 0,
                ],
                'chart' => $chart
            ];

            $indicador->content = $total;
            $indicador->bottom_detail = property_exists($result, 'bottom_detail') ? $result->bottom_detail : [];
            $indicador->carga_trabajo = property_exists($result, 'carga_trabajo') ? $result->carga_trabajo : 0;
            $indicador->total_resueltos = property_exists($result, 'total_resueltos') ? $result->total_resueltos : 0;
            $indicador->total = $result->total;
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

    public function chart($result){

        try {
            
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

        } catch (\Throwable $th) {
            
            $response = [
                'status' => 'error',
                'message' => $th->getMessage()
            ];

            return $response;

        }
       

    }

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('app.EFICACIA');

        }

        // * Definición de la estructura para el indicador
        
        // Validar si existe un función especifica para la data
        if ($data->data_controlador) {
            
            $result = app('App\Http\Controllers' . $data->data_controlador)->data($data);

            return $result;
            
        }

        $codigo = $data->dependencia->codigo;

        // * Mes siguiente
    
        $date_after = date('Y-m', strtotime($data->date . ' +1 month'));
        
        // ! Obtener el dato de los Anteriores
        $result_anteriores = (object) app('App\Http\Controllers\PreviousController')->update_previous($data);
        $pendientes_congelado = $result_anteriores->value;
        $items_pendientes_congelado = $result_anteriores->items;

        /*
        // Calculo en base a consultas
        // $anteriores = MCAProcesos::where('dependencia', $data->dependencia->codigo)
        //                 ->whereRaw("(
        //                     fecha_ingreso < to_date('$data->date', 'YYYY-MM')
        //                 )")
        //                 ->whereRaw("to_char(fecha_ingreso, 'YYYY') = '2022'")
        //                 ->whereRaw("(
        //                     fecha_finalizacion is null
        //                     or to_char(fecha_finalizacion, 'YYYY-MM') = '$data->date'
        //                 )")
        //                 ->get();

        $anteriores = DB::connection('catastrousr')->select("SELECT
                                                                concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                t1.usuario,
                                                                t2.descripcion as estado,
                                                                t3.descripcion as tipo
                                                            from sgc.mca_procesos_vw t1
                                                            inner join catastro.cdo_status_docto t2
                                                            on t1.status_documento = t2.codigo
                                                            inner join catastro.cdo_clasedocto t3
                                                            on t1.codigoclase = t3.codigoclase
                                                            where fecha_ingreso < to_date('$data->date', 'YYYY-MM')
                                                            and dependencia = '$codigo'
                                                            and to_char(fecha_ingreso, 'YYYY') = '2022'
                                                            and (
                                                                fecha_finalizacion is null
                                                                or to_char(fecha_finalizacion, 'YYYY-MM') = '$data->date'
                                                            )
                                                            order by t1.fecha_ingreso");
        
        */        
                                                
        $ingresados = DB::connection('catastrousr')->select("   SELECT 
                                                                    concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                    to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                    to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                    t1.usuario,
                                                                    t2.descripcion as estado,
                                                                    t3.descripcion as tipo
                                                                from sgc.mca_procesos_vw t1
                                                                inner join catastro.cdo_status_docto t2
                                                                on t1.status_documento = t2.codigo
                                                                inner join catastro.cdo_clasedocto t3
                                                                on t1.codigoclase = t3.codigoclase
                                                                where to_char(fecha_ingreso, 'YYYY-MM') = '$data->date'
                                                                and dependencia = '$codigo'
                                                                order by t1.fecha_ingreso");

        $resueltos = DB::connection('catastrousr')->select("SELECT 
                                                                concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                t1.usuario,
                                                                t2.descripcion as estado,
                                                                t3.descripcion as tipo
                                                            from sgc.mca_procesos_vw t1
                                                            inner join catastro.cdo_status_docto t2
                                                            on t1.status_documento = t2.codigo
                                                            inner join catastro.cdo_clasedocto t3
                                                            on t1.codigoclase = t3.codigoclase
                                                            where (
                                                                fecha_ingreso < to_date('$data->date', 'YYYY-MM')
                                                                or to_char(fecha_ingreso, 'YYYY-MM') = '$data->date'
                                                            )
                                                            and dependencia = '$codigo'
                                                            and to_char(fecha_ingreso, 'YYYY') = '2022'
                                                            and (
                                                                to_char(fecha_finalizacion, 'YYYY-MM') = '$data->date'
                                                            )
                                                            order by t1.fecha_ingreso");

        $pendientes = DB::connection('catastrousr')->select("SELECT
                                                                concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                t1.usuario,
                                                                t2.descripcion as estado,
                                                                t3.descripcion as tipo
                                                            from sgc.mca_procesos_vw t1
                                                            inner join catastro.cdo_status_docto t2
                                                            on t1.status_documento = t2.codigo
                                                            inner join catastro.cdo_clasedocto t3
                                                            on t1.codigoclase = t3.codigoclase
                                                            where fecha_ingreso < to_date('$date_after', 'YYYY-MM')
                                                            and dependencia = '$codigo'
                                                            and to_char(fecha_ingreso, 'YYYY') = '2022'
                                                            and (
                                                                fecha_finalizacion is null
                                                                or to_char(fecha_finalizacion, 'YYYY-MM') = '$date_after'
                                                            )
                                                            order by t1.fecha_ingreso");

        $headers_resueltos = [
            [
                'text' => 'Documento',
                'value' => 'expediente',
                'width' => '10%',
                'sortable' => false
            ],
            [
                'text' => 'Ingreso',
                'value' => 'fecha_ingreso',
                'width' => '20%',
                'sortable' => false
            ],
            [
                'text' => 'Finalización',
                'value' => 'fecha_finalizacion',
                'width' => '20%',
                'sortable' => false
            ],
            [
                'text' => 'Tipo',
                'value' => 'tipo',
                'width' => '15%',
                'sortable' => false
            ],
            [
                'text' => 'Estado',
                'value' => 'estado',
                'width' => '15%',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario',
                'width' => '10%',
                'sortable' => false
            ]
        ];

        $carga_trabajo = count($ingresados) + $pendientes_congelado;

        if ($carga_trabajo > 0) {
           
            $porcentaje = round((count($resueltos) / $carga_trabajo * 100), 2);

        }else{

            $porcentaje = 0;
        }

        $total_pendientes = $carga_trabajo - count($resueltos);
        
        $response = [
            "total" => $porcentaje,
            'carga_trabajo' => $carga_trabajo,
            'total_resueltos' => count($resueltos),
            'bottom_detail' => [
                [
                    "text" => "Anteriores",
                    "value" => $pendientes_congelado,
                    'detail' => [
                        'table' => [
                            'headers' => $headers_resueltos,
                            'items' => $items_pendientes_congelado
                        ],
                    ],
                    'component' => 'tables/TableDetail',
                    'fullscreen' => true,
                    'divide' => 'down'
                ],
                [
                    "text" => "Ingresados",
                    "value" => count($ingresados),
                    'detail' => [
                        'table' => [
                            'headers' => $headers_resueltos,
                            'items' => $ingresados
                        ],
                    ],
                    'component' => 'tables/TableDetail',
                    'fullscreen' => true,
                    'divide' => 'down'
                ],
                [
                    "text" => "Resueltos",
                    "value" => count($resueltos),
                    'detail' => [
                        'table' => [
                            'headers' => $headers_resueltos,
                            'items' => $resueltos
                        ],
                    ],
                    'component' => 'tables/TableDetail',
                    'fullscreen' => true,
                    'divide' => 'up'
                ],
                [
                    "text" => "Pendientes",
                    "value" => $total_pendientes,
                    'detail' => [
                        'table' => [
                            'headers' => $headers_resueltos,
                            'items' => $pendientes
                        ],
                    ],
                    'component' => 'tables/TableDetail',
                    'fullscreen' => true
                ],
            ]
        ];

        return $response;

    }

}