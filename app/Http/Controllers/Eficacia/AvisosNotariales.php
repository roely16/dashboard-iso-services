<?php 

namespace App\Http\Controllers\Eficacia;
use App\Http\Controllers\Controller;

use App\Historial;
use App\Proceso;
use App\MCAProcesos;
use App\Historico;

use Illuminate\Support\Facades\DB;

class AvisosNotariales extends Controller{

    public function data($data){

        try {
            
            if (property_exists($data, 'get_structure')) {
            
                return config('eficacia_json.EFICACIA');
    
            }
            
            // ! Obtener el dato de los Anteriores
            $result_anteriores = (object) app('App\Http\Controllers\PreviousController')->update_previous($data);
            $pendientes_congelado = $result_anteriores->value;
            $items_pendientes_congelado = $result_anteriores->items;

            $codigo = $data->dependencia->codigo;

            // * Mes siguiente
        
            $date_after = date('Y-m', strtotime($data->date . ' +1 month'));

            // Calculo en base a consultas
            /*
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
                                                                    to_char(t1.fecha_fin_tarea, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
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
                                                                    fecha_fin_tarea is null
                                                                    or to_char(fecha_fin_tarea, 'YYYY-MM') = '$data->date'
                                                                )
                                                                and t1.status_tarea <> 1
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
                                                                    and t1.status_tarea <> 1
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
                                                                and t1.status_tarea <> 1
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
                                                                and t1.status_tarea <> 1
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

            $carga_trabajo = count($ingresados) + $pendientes_congelado;

            if ($carga_trabajo > 0) {
            
                $porcentaje = round((count($resueltos) / $carga_trabajo * 100), 1);

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
            
        } catch (\Throwable $th) {
            
            return $th->getMessage();

        }
     
    }
    
}