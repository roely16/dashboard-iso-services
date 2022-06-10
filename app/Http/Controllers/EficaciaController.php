<?php

namespace App\Http\Controllers;

use App\Historial;
use App\Proceso;
use App\MCAProcesos;

use Illuminate\Support\Facades\DB;

class EficaciaController extends Controller{

    public function create($indicador){

        try {
            
            $proceso = Proceso::find($indicador->id_proceso);
            $area = $proceso->area;
            $dependencia = $proceso->dependencia;
            
            $data = (object) [
                'codarea' => $area->codarea,
                'date' => $indicador->date,
                'dependencia' => $dependencia,
                'nombre_historial' => $proceso->nombre_historial
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
            $indicador->carga_trabajo = $result->carga_trabajo;
            $indicador->total_resueltos = $result->total_resueltos;
            
            return $indicador;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage()
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
        
        // * Separar la fecha
    

        $date_before = date('Y-m', strtotime($data->date . ' -1 month'));
        $date_after = date('Y-m', strtotime($data->date . ' +1 month'));

        // * Obtener de la tabla ISO_DASHBOARD_HISTORIAL el campo CAMPO_4 que hace referencia a ANTERIORES 

        $anteriores = 0;

        if ($data->nombre_historial) {
            
            // $anteriores = Historial::select('campo_4 as total')
            //             ->where('area', $data->nombre_historial)
            //             ->where('mes', $month_before)
            //             ->where('anio', $year)
            //             ->where('sub_area', 'EFICACIA')
            //             ->first();

            // $anteriores = $anteriores->total;

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
                        ->count();
        
        $ingresados = MCAProcesos::where('dependencia', $data->dependencia->codigo)
                        ->whereRaw("
                            to_char(fecha_ingreso, 'YYYY-MM') = '$data->date'
                        ")
                        ->count();

        $resueltos = MCAProcesos::where('dependencia', $data->dependencia->codigo)
                        ->whereRaw("(
                            fecha_ingreso < to_date('$data->date', 'YYYY-MM')
                            or to_char(fecha_ingreso, 'YYYY-MM') = '$data->date'
                        )")
                        ->whereRaw("to_char(fecha_ingreso, 'YYYY') = '2022'")
                        ->whereRaw("(
                            to_char(fecha_finalizacion, 'YYYY-MM') = '$data->date'
                        )")
                        ->count();

        $pendientes = MCAProcesos::where('dependencia', $data->dependencia->codigo)
                        ->whereRaw("(
                            fecha_ingreso < to_date('$date_after', 'YYYY-MM')
                        )")
                        ->whereRaw("to_char(fecha_ingreso, 'YYYY') = '2022'")
                        ->whereRaw("(
                            fecha_finalizacion is null or
                            to_char(fecha_finalizacion, 'YYYY-MM') = '$date_after'
                        )")
                        ->count();
    
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
                'width' => '20%',
                'sortable' => false
            ],
            [
                'text' => 'Ingreso',
                'value' => 'fecha_ingreso',
                'width' => '30%',
                'sortable' => false
            ],
            [
                'text' => 'Finalización',
                'value' => 'fecha_finalizacion',
                'width' => '30%',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario',
                'width' => '20%',
                'sortable' => false
            ]
        ];

        // $total_pendientes = ($total_ingresados + count($anteriores)) - $total_resueltos;

        $carga_trabajo = $ingresados + $anteriores;

        if ($carga_trabajo > 0) {
           
            $porcentaje = round(($resueltos / $carga_trabajo * 100), 1);

        }else{

            $porcentaje = 0;
        }
        
        $response = [
            "total" => $porcentaje,
            'carga_trabajo' => $carga_trabajo,
            'total_resueltos' => $resueltos,
            'bottom_detail' => [
                [
                    "text" => "Anteriores",
                    "value" => $anteriores,
                    'detail' => [
                        'table' => [
                            'headers' => $headers_resueltos,
                            'items' => $anteriores
                        ],
                    ],
                    'component' => 'tables/TableDetail'
                ],
                [
                    "text" => "Ingresados",
                    "value" => $ingresados,
                    'detail' => [
                        'table' => [
                            'headers' => $headers_ingresados,
                            'items' => $ingresados
                        ],
                    ],
                    'component' => 'tables/TableDetail'
                ],
                [
                    "text" => "Resueltos",
                    "value" => $resueltos,
                    'detail' => [
                        'table' => [
                            'headers' => $headers_resueltos,
                            'items' => $resueltos
                        ],
                    ],
                    'component' => 'tables/TableDetail'
                ],
                [
                    "text" => "Pendientes",
                    "value" => $pendientes,
                    'detail' => [
                        'table' => [
                            'headers' => $headers_resueltos,
                            'items' => $pendientes
                        ],
                    ],
                    'component' => 'tables/TableDetail'
                ],
            ]
        ];

        return $response;

       

    }

}