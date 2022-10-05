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
            
            $current_date = date('Y-m');

            if (strtotime($data->date) < strtotime($current_date)) {
                
                // * Hacer llamado al controlador de Configuración y obtener el dato de Pendientes del mes anterior
                //$result = app('App\Http\Controllers\ConfigController')->create($data);

                // * Es un mes anterior por lo que es necesario verificar en el historico
                $historico = Historico::where('id_proceso', $data->id_proceso)
                                ->where('id_indicador', $data->id_indicador)
                                ->where('fecha', $data->date)
                                ->orderBy('id', 'desc')
                                ->first();

                // * Si se encuentra un registro historico
                if ($historico) {
                    
                    $json_data = json_decode(file_get_contents($historico->path));
                    
                    $response = [
                        'total' => $json_data->total,
                        'carga_trabajo' => $json_data->carga_trabajo,
                        'total_resueltos' => $json_data->total_resueltos,
                        'bottom_detail' => $json_data->bottom_detail,
                        'updated_at' => $historico->updated_at
                    ];

                    return $response;
                }                

            }

            // * Obtener el dato de anteriores desde el controlador ConfigController, tomando el dato del mes anterior

            // * Crear objeto con los parametros necesarios para realizar la consulta 
            
            $data_history = (object) [
                'date' => date('Y-m', strtotime($data->date . " -1 month")),
                'id_proceso' => $data->id_proceso,
                'id_indicador' => $data->id_indicador,
                'data_controlador' => $data->data_controlador,
                'controlador' => $data->controlador,
                'nombre_historial' => $data->nombre_historial,
                'subarea_historial' => $data->subarea_historial,
                'campos' => $data->campos,
            ];
            
            $result = app('App\Http\Controllers\ConfigController')->get_history($data_history);

            // * Obtener el dato de los pendientes del mes anterior (Congelado)
            $pendientes_congelado = $result['bottom_detail'][3]['value'];
            $items_pendientes_congelado = $result['bottom_detail'][3]['detail']['table']['items'];

            $codigo = $data->dependencia->codigo;

            // * Mes siguiente
        
            $date_after = date('Y-m', strtotime($data->date . ' +1 month'));

            // * Obtener de la tabla ISO_DASHBOARD_HISTORIAL el campo CAMPO_4 que hace referencia a ANTERIORES 

            $anteriores = 0;

            if ($data->nombre_historial) {
                
                $split_date = explode('-', $data->date);
                $month_before = intval($split_date[1]) - 1;
                $year = $split_date[0];

                $anteriores = Historial::select('campo_4 as total')
                            ->where('area', $data->nombre_historial)
                            ->where('mes', $month_before)
                            ->where('anio', $year)
                            ->where('sub_area', 'EFICACIA')
                            ->first();
    
                $total_anterior = $anteriores ? $anteriores->total : 0;
    
            }

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

            // $total_pendientes = ($total_ingresados + count($anteriores)) - $total_resueltos;

            $carga_trabajo = count($ingresados) + ($data->nombre_historial ? $total_anterior : count($anteriores));

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
                                'items' => $anteriores
                            ],
                        ],
                        'component' => 'tables/TableDetail',
                        'fullscreen' => true
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
                        'fullscreen' => true
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
                        'fullscreen' => true
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