<?php 

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use App\Empleado;
use App\Models\Convenios\Convenio;

use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class ConveniosController extends Controller{

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('calidad_json.CALIDAD');

        }
        
        // * Obtener los servicios no conformes
        $no_conformes = (object) app('App\Http\Controllers\NoConformesController')->process($data);

        $total = DB::connection('cobros-iusi')->select(" SELECT 
                                                            t1.idpersona,
                                                            to_char(t1.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha, 
                                                            t2.nit as usuario
                                                        FROM mco_persona t1
                                                        INNER JOIN mco_bitacora t2
                                                        ON t1.idpersona = t2.idpersona
                                                        WHERE to_char(t2.fecha, 'YYYY-MM') = '$data->date'
                                                        AND t2.idestado = 7");

        $validos = [];
        $rechazados = [];

        foreach ($total as $item) {
            
            // * Buscar el nombre del usuario
            $empleado = Empleado::find($item->usuario);

            $item->usuario_opera = $empleado->usuario;

            // * Buscar el número de convenio
            $convenio = Convenio::where('idpersona', $item->idpersona)->first();

            $item->expediente = $convenio ? $convenio->no_convenio . '-' . $convenio->anio : null;

            // * Buscar si tiene registro en la bitacora
            $bitacora = DB::connection('cobros-iusi')->select(" SELECT *
                                                                FROM mco_bitacora
                                                                WHERE idpersona = $item->idpersona
                                                                AND idestado = 8");         

            if (count($bitacora) > 0) {
                
                $rechazados [] = $item;

            }else{
                
                $validos [] = $item;

            }

        }

        $headers = [
            [
                'text' => 'Convenio',
                'value' => 'expediente',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario_opera',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Resultado',
                'value' => 'resultado',
                'sortable' => false,
                'width' => '25%'
            ]
        ];

        $bottom_detail = [
            [
                'text' => 'Total',
                'value' => count($total),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $total
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Válidas',
                'value' => count($validos),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $validos
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'SNC',
                'value' => count($no_conformes),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $no_conformes
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Correcciones',
                'value' => count($rechazados),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $rechazados
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ];

        if (count($total) > 0) {
            
            $porcentaje = round((count($validos) / count($total)) * 100, 1);

        }else{

            $porcentaje = 100;

        }

        // Dashboard

        // Convenios IUSI aprobados por técnico
        $documentos_aprobados = DB::connection('cobros-iusi')->select(" SELECT *
                                                                        from (SELECT concat(concat(ap.nombre, ' '), ap.apellido) AS tecnico, count(b.idestado) as total
                                                                            FROM mco_bitacora b
                                                                                    INNER JOIN rh_empleados ap ON ap.nit = b.nit
                                                                            WHERE b.idestado = 7
                                                                                and idpersona NOT IN
                                                                                    (SELECT b.idpersona FROM mco_bitacora b WHERE idestado = 8 AND to_char(b.fecha, 'YYYY-MM') = '$data->date')
                                                                                AND to_char(b.fecha, 'YYYY-MM') = '$data->date'
                                                                            GROUP BY concat(concat(ap.nombre, ' '), ap.apellido)) cb");

        $labels = [];
        $data_chart = [];

        foreach ($documentos_aprobados as $documento) {
            
            $labels [] = $documento->tecnico;
            $data_chart [] = $documento->total;

        }

        $chart_aprobados = [
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data_chart,
                        'backgroundColor' => '#81C784',
                        'datalabels' => [
                            'align' => 'start',
                            'anchor' => 'end',
                            
                        ]
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'indexAxis' => 'y',
                'plugins' => [
                    'legend' => [
                        'display' => false
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Documentos Aprobados'
                    ],
                    'datalabels' => [
                        'padding' => 6,
                        'color' => 'black',
                        'borderRadious' => 4
                    ]
                ],
                'dataLabels' => true  
            ]
        ];

        // Convenios IUSI rechazados por técnico

        $rechazados = DB::connection('cobros-iusi')->select("   SELECT *
                                                                from (SELECT concat(concat(ap.nombre, ' '), ap.apellido) as tecnico, count(b.idestado) as total
                                                                    from mco_bitacora b
                                                                            inner join rh_empleados ap on ap.nit = b.nit
                                                                    where b.idestado = 7
                                                                        and idpersona in
                                                                            (select idpersona from mco_bitacora where idestado = 8 and to_char(b.fecha, 'YYYY-MM') = '$data->date')
                                                                    group by b.idestado, concat(concat(ap.nombre, ' '), ap.apellido)) Cr");

        $labels = [];
        $data_chart = [];

        foreach ($rechazados as $rechazado) {
                    
            $labels [] = $rechazado->tecnico;
            $data_chart [] = $rechazado->total;

        }

        $chart_rechazados = [
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data_chart,
                        'backgroundColor' => '#E57373'
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'indexAxis' => 'y',
                'plugins' => [
                    'legend' => [
                        'display' => false
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Documentos Rechazados'
                    ],
                    'datalabels' => [
                        'padding' => 6,
                        'color' => 'black',
                        'borderRadious' => 4
                    ]
                ],
                'dataLabels' => true   
            ]
        ];

        // Motivos de rechazo
        $motivos = DB::connection('cobros-iusi')->select("  SELECT count(b.idestado)as cantidad, c.descripcion
                                                            from mco_bitacora b
                                                            inner join rh_empleados ap on ap.nit=b.nit
                                                            inner join mco_cat_motivo c on c.idmotivo=b.idmotivo
                                                            where b.idpersona in(select b.idpersona
                                                                                from mco_bitacora b
                                                                                inner join rh_empleados ap on ap.nit=b.nit
                                                                                where b.idestado=7 and idpersona in (select idpersona from mco_bitacora where idestado=8 and to_char(b.fecha,'YYYY-MM') = '$data->date')
                                                                                group by b.idpersona) and b.idmotivo in(13,14,15,16,17,18,19,20,21,22,23,25,26,27) and to_char(b.fecha,'YYYY-MM') = '$data->date'
                                                            group by b.idestado, c.descripcion");

        $labels = [];
        $data_chart = [];
        $colors = [];

        foreach ($motivos as $motivo) {
                    
            $labels [] = $motivo->descripcion;
            $data_chart [] = $motivo->cantidad;
            $colors [] = '#' . str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT);

        }

        $chart_motivos = [
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $data_chart,
                        'backgroundColor' => $colors
                    ]
                ]
            ],
            'options' => [
                'responsive' => false,
                'plugins' => [
                    'legend' => [
                        'display' => true,
                        'position' => 'bottom'
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Motivos de Rechazo'
                    ],
                    'datalabels' => [
                        'padding' => 6,
                        'color' => 'black',
                        'borderRadious' => 4
                    ]
                ],
                'dataLabels' => true   
            ]
        ];

        //Motivos de rechazo por técnico 
        $chart_rechazo_tecnico = [
            'data' => [
                'labels' => [],
                'datasets' => [
                    [
                        'data' => [],
                        'backgroundColor' => []
                    ]
                ]
            ],
            'options' => [
                'responsive' => false,
                'plugins' => [
                    'legend' => [
                        'display' => true,
                        'position' => 'bottom'
                    ],
                    'title' => [
                        'display' => true,
                        'text' => 'Motivos de Rechazo'
                    ],
                    'datalabels' => [
                        'padding' => 6,
                        'color' => 'black',
                        'borderRadious' => 4
                    ]
                ],
                'dataLabels' => true   
            ]
        ];

        $response = [
            'total' => $porcentaje,
            'validas' => count($validos),
            'total_calidad' => count($total),
            'bottom_detail' => $bottom_detail,
            'custom_data' => [
                'component' => 'custom/DashboardCalidadConvenios',
                'fullscreen' => true,
                'detail' => [
                    'chart_aprobados' => $chart_aprobados,
                    'chart_rechazados' => $chart_rechazados,
                    'chart_motivos' => $chart_motivos,
                    'chart_rechazo_tecnico' => $chart_rechazo_tecnico
                ]
            ]
        ];

        return $response;

    }

    public function tecnicos_rechazos(Request $request){

        try {
            
            $tecnicos = DB::connection('cobros-iusi')->select(" SELECT *
                                                                from (SELECT b.nit, concat(concat(ap.nombre, ' '), ap.apellido) as colaborador
                                                                    from mco_bitacora b
                                                                            inner join rh_empleados ap on ap.nit = b.nit
                                                                    where b.idestado = 7
                                                                        and idpersona in
                                                                            (select idpersona from mco_bitacora where idestado = 8 and to_char(b.fecha, 'YYYY-MM') = '$request->date')
                                                                    group by concat(concat(ap.nombre, ' '), ap.apellido), b.nit)");

            $options = [];

            foreach ($tecnicos as $tecnico) {
                
                $options[$tecnico->nit] = $tecnico->colaborador;

            }

            return response()->json($options);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage());

        }

    }

    public function chart_rechazo_tecnico(Request $request){

        try {
            
            $data_chart = DB::connection('cobros-iusi')->select(" SELECT *
                                                            from (SELECT count(b.idestado) as rechazados, c.descripcion
                                                                from mco_bitacora b
                                                                        inner join rh_empleados ap on ap.nit = b.nit
                                                                        inner join mco_cat_motivo c on c.idmotivo = b.idmotivo
                                                                where b.idestado = 8
                                                                    and idpersona in (select idpersona
                                                                                    from mco_bitacora
                                                                                    where idestado = 3 and nit = '$request->nit' and to_char(fecha, 'YYYY-MM') = '$request->date')
                                                                group by b.idestado, c.descripcion) rd");
            
            $data = [];
            $labels = [];
            $colors = [];
 
            foreach ($data_chart as &$item) {
                
                $labels [] = $item->descripcion;
                $data [] = $item->rechazados;
                $colors [] = '#' . str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT);

            }

            // Obtener nombre del colaborador 
            $colaborador = Empleado::find($request->nit);

            $chart_rechazo_tecnico = [
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'data' => $data,
                            'backgroundColor' => $colors
                        ]
                    ]
                ],
                'options' => [
                    'responsive' => false,
                    'plugins' => [
                        'legend' => [
                            'display' => true,
                            'position' => 'bottom'
                        ],
                        'title' => [
                            'display' => true,
                            'text' => 'Motivos de Rechazo'
                        ],
                        'subtitle' => [
                            'display' => true,
                            'text' => $colaborador->nombre . ' ' . $colaborador->apellido,
                            'padding' => [
                                'bottom' => 5
                            ]
                        ],
                        'datalabels' => [
                            'padding' => 6,
                            'color' => 'black',
                            'borderRadious' => 4
                        ]
                    ],
                    'dataLabels' => true   
                ]
            ];

            return response()->json($chart_rechazo_tecnico);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

}