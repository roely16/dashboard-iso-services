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

            $ingresados = DB::connection('catastrousr')->select("   SELECT
                                                                        concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                        to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                        to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                        t1.usuario,
                                                                        t2.descripcion as estado,
                                                                        t3.descripcion as tipo
                                                                    FROM SGC.MCA_PROCESOS_VW T1
                                                                    inner join catastro.cdo_status_docto t2
                                                                    on t1.status_documento = t2.codigo
                                                                    inner join catastro.cdo_clasedocto t3
                                                                    on t1.codigoclase = t3.codigoclase
                                                                    WHERE T1.DEPENDENCIA IN (18)
                                                                    AND TO_CHAR(T1.FECHA_ASIGNA_TAREA, 'YYYY-MM') = '$data->date'
                                                                    AND T1.STATUS_DOCUMENTO <> 3
                                                                    -- and T1.STATUS_TAREA <> 1
                                                                    and TRUNC(FECHA_ASIGNA_TAREA) = (
                                                                        SELECT MAX(TRUNC(FECHA_ASIGNACION)) 
                                                                        FROM CDO_BANDEJA 
                                                                        WHERE DOCUMENTO = T1.DOCUMENTO 
                                                                        AND ANIO = T1.ANIO 
                                                                        AND CODIGOCLASE = T1.CODIGOCLASE 
                                                                        AND DEPENDENCIA IN (18)
                                                                    )");

            $resueltos = DB::connection('catastrousr')->select("SELECT 
                                                                    concat(t1.documento, concat('-', t1.anio)) as expediente,
                                                                    to_char(t1.fecha_ingreso, 'DD/MM/YYYY HH24:MI:SS') as fecha_ingreso,
                                                                    to_char(t1.fecha_finalizacion, 'DD/MM/YYYY HH24:MI:SS') as fecha_finalizacion,
                                                                    t1.usuario,
                                                                    t2.descripcion as estado,
                                                                    t3.descripcion as tipo
                                                                FROM SGC.MCA_PROCESOS_VW t1
                                                                inner join catastro.cdo_status_docto t2
                                                                on t1.status_documento = t2.codigo
                                                                inner join catastro.cdo_clasedocto t3
                                                                on t1.codigoclase = t3.codigoclase
                                                                WHERE DEPENDENCIA IN (18)
                                                                AND TO_CHAR(FECHA_ASIGNA_TAREA, 'YYYY-MM') = '$data->date'
                                                                AND TO_CHAR(FECHA_FIN_TAREA, 'YYYY-MM') = '$data->date'
                                                                AND STATUS_DOCUMENTO <> 3");

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
                                'items' => []
                            ],
                        ],
                        'component' => 'tables/PendientesDetalle',
                        'fullscreen' => false
                    ]
                ]
            ];

            return $response;
            
        } catch (\Throwable $th) {
            
            return $th->getMessage();

        }
     
    }
    
}