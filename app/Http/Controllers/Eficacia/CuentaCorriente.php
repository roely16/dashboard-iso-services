<?php 

namespace App\Http\Controllers\Eficacia;

use App\Http\Controllers\Controller;

use App\Historial;
use App\Proceso;
use App\MCAProcesos;

use Illuminate\Support\Facades\DB;

class CuentaCorriente extends Controller{

    public function data($data){

        $codigo = $data->dependencia->codigo;

        // * Mes siguiente
    
        $date_after = date('Y-m', strtotime($data->date . ' +1 month'));

        // * Obtener de la tabla ISO_DASHBOARD_HISTORIAL el campo CAMPO_4 que hace referencia a ANTERIORES 

        $anteriores = 0;

        // Calculo en base a consultas
        $anteriores = MCAProcesos::where('dependencia', $data->dependencia->codigo)
                        ->whereRaw("(
                            fecha_ingreso < to_date('$data->date', 'YYYY-MM')
                        )")
                        ->whereRaw("to_char(fecha_ingreso, 'YYYY') = '2022'")
                        ->whereRaw("(
                            fecha_finalizacion is null
                            or to_char(fecha_finalizacion, 'YYYY-MM') = '$data->date'
                        )")
                        ->get();

        $anteriores = DB::connection('catastrousr')->select("SELECT
                                                                concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                t1.usuario,
                                                                t2.descripcion as estado,
                                                                t3.descripcion as tipo
                                                            from sgc.mca_procesos_vw t1
                                                            inner join catastro.cdo_status t2
                                                            on t1.status_documento = t2.codstatus
                                                            inner join catastro.cdo_clasedocto t3
                                                            on t1.codigoclase = t3.codigoclase
                                                            where fecha_ingreso < to_date('$data->date', 'YYYY-MM')
                                                            and dependencia = '$codigo'
                                                            and to_char(fecha_ingreso, 'YYYY') = '2022'
                                                            and (
                                                                fecha_finalizacion is null
                                                                or to_char(fecha_finalizacion, 'YYYY-MM') = '$data->date'
                                                            )
                                                            and t1.codigoclase = 3
                                                            and t1.tramite in (322)
                                                            order by t1.fecha_ingreso");
        
        $ingresados = DB::connection('catastrousr')->select("   SELECT 
                                                                    concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                    to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                    to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                    t1.usuario,
                                                                    t2.descripcion as estado,
                                                                    t3.descripcion as tipo
                                                                from sgc.mca_procesos_vw t1
                                                                inner join catastro.cdo_status t2
                                                                on t1.status_documento = t2.codstatus
                                                                inner join catastro.cdo_clasedocto t3
                                                                on t1.codigoclase = t3.codigoclase
                                                                where to_char(fecha_ingreso, 'YYYY-MM') = '$data->date'
                                                                and dependencia = '$codigo'
                                                                and t1.codigoclase = 3
                                                                and t1.tramite in (322)
                                                                order by t1.fecha_ingreso");

        $resueltos = DB::connection('catastrousr')->select("SELECT 
                                                                concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                t1.usuario,
                                                                t2.descripcion as estado,
                                                                t3.descripcion as tipo
                                                            from sgc.mca_procesos_vw t1
                                                            inner join catastro.cdo_status t2
                                                            on t1.status_documento = t2.codstatus
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
                                                            and t1.codigoclase = 3
                                                            and t1.tramite in (322)
                                                            order by t1.fecha_ingreso");

        $pendientes = DB::connection('catastrousr')->select("SELECT
                                                                concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                t1.usuario,
                                                                t2.descripcion as estado,
                                                                t3.descripcion as tipo
                                                            from sgc.mca_procesos_vw t1
                                                            inner join catastro.cdo_status t2
                                                            on t1.status_documento = t2.codstatus
                                                            inner join catastro.cdo_clasedocto t3
                                                            on t1.codigoclase = t3.codigoclase
                                                            where fecha_ingreso < to_date('$date_after', 'YYYY-MM')
                                                            and dependencia = '$codigo'
                                                            and to_char(fecha_ingreso, 'YYYY') = '2022'
                                                            and (
                                                                fecha_finalizacion is null
                                                                or to_char(fecha_finalizacion, 'YYYY-MM') = '$date_after'
                                                            )
                                                            and t1.codigoclase = 3
                                                            and t1.tramite in (322)
                                                            order by t1.fecha_ingreso");
    
        $headers_ingresados = [
            [
                'text' => 'Documento',
                'value' => 'expediente',
                'width' => '33%',
                'sortable' => false
            ],
            [
                'text' => 'Ingreso',
                'value' => 'fecha_ingreso',
                'width' => '33%',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario',
                'width' => '33%',
                'sortable' => false
            ]
        ];

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

        // $total_pendientes = ($total_ingresados + count($anteriores)) - $total_resueltos;

        $carga_trabajo = count($ingresados) + count($anteriores);

        if ($carga_trabajo > 0) {
           
            $porcentaje = round((count($resueltos) / $carga_trabajo * 100), 1);

        }else{

            $porcentaje = 0;
        }
        
        $response = [
            "total" => $porcentaje,
            'carga_trabajo' => $carga_trabajo,
            'total_resueltos' => count($resueltos),
            'bottom_detail' => [
                [
                    "text" => "Anteriores",
                    "value" => count($anteriores),
                    'detail' => [
                        'table' => [
                            'headers' => $headers_resueltos,
                            'items' => $anteriores
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
                    "value" => count($pendientes),
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