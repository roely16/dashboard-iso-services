<?php

namespace App\Http\Controllers;

use App\Proceso;
use App\MCAProcesos;

class EficaciaController extends Controller{

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
        
        $anteriores = 0;

        // En el mes actual buscar los expedientes que han sigo ingresados 

        $ingresados = MCAProcesos::where('dependencia', $data->dependencia->codigo)
                        ->whereRaw("TO_CHAR(FECHA_INGRESO, 'YYYY-MM') = '$data->date'")
                        ->get();

        $headers_ingresados = [
            [
                'text' => 'Documento',
                'value' => 'documento',
                'width' => '25%'
            ],
            [
                'text' => 'Año',
                'value' => 'anio',
                'width' => '25%'
            ],
            [
                'text' => 'Ingreso',
                'value' => 'fecha_ingreso',
                'width' => '25%'
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario',
                'width' => '25%'
            ]
        ];

        $total_ingresados = count($ingresados);

        $resueltos = [];
        $pendientes = [];

        foreach ($ingresados as $ingresado) {
            
            if ($ingresado->fecha_finalizacion) {
                
                $resueltos [] = $ingresado;

            }else{

                $pendientes [] = $ingresado;

            }

        }

        // En el mes actual buscar aquellos expedientes que han sido resueltos

        $total_resueltos = count($resueltos);

        $headers_resueltos = [
            [
                'text' => 'Documento',
                'value' => 'documento',
                'width' => '10%',
                'sortable' => false
            ],
            [
                'text' => 'Año',
                'value' => 'anio',
                'width' => '10%',
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

        $total_pendientes = ($total_ingresados + $anteriores) - $total_resueltos;

        $carga_trabajo = $total_ingresados + $anteriores;

        if ($carga_trabajo > 0) {
           
            $porcentaje = round(($total_resueltos / ($carga_trabajo) * 100), 1);

        }else{

            $porcentaje = 0;
        }
        
        $response = [
            "total" => $porcentaje,
            'carga_trabajo' => $carga_trabajo,
            'total_resueltos' => $total_resueltos,
            'bottom_detail' => [
                [
                    "text" => "Anteriores",
                    "value" => $anteriores,
                ],
                [
                    "text" => "Ingresados",
                    "value" => $total_ingresados,
                    'detail' => [
                        'table' => [
                            'headers' => $headers_ingresados,
                            'items' => $ingresados
                        ]
                    ]
                ],
                [
                    "text" => "Resueltos",
                    "value" => $total_resueltos,
                    'detail' => [
                        'table' => [
                            'headers' => $headers_resueltos,
                            'items' => $resueltos
                        ]
                    ]
                ],
                [
                    "text" => "Pendientes",
                    "value" => $total_pendientes,
                    'detail' => [
                        'table' => [
                            'headers' => $headers_ingresados,
                            'items' => $pendientes
                        ]
                    ]
                ],
            ]
        ];

        return $response;

       

    }

}