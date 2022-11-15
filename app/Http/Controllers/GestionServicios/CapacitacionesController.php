<?php

namespace App\Http\Controllers\GestionServicios;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Capacitaciones\Capacitacion;

use Illuminate\Support\Facades\DB;

class CapacitacionesController extends Controller {

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
                        'data' => [
                            $bottom_detail[1]['value'],
                            $bottom_detail[2]['value']
                        ],
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
        
        if (property_exists($data, 'get_structure')) {
            
            return config('gestion_servicios.CAPACITACIONES');

        }
        
        // * Obtener el total de capacitacion programadas para el trimestre
        $trimestres = [
            [1,2,3],
            [4,5,6],
            [7,8,9],
            [10,11,12]
        ];

        $split_date = explode('-', $data->date);
        $mes = intval($split_date[1]);
        $year = $split_date[0];

        $trimestre = floor(($mes - 1) / 3) + 1;

        $str_trimestre = implode(',', $trimestres[$trimestre - 1]);

        $capacitaciones = Capacitacion::select(
                                'rh_capacitacion.*',
                                DB::raw("to_char(fecha_estimada, 'MM/YYYY') as mes_estimado")
                            )
                            ->whereRaw("to_char(fecha_estimada, 'YYYY') = '$year'")
                            ->whereRaw("to_number(to_char(fecha_estimada, 'MM')) IN ($str_trimestre)")
                            ->get();


        $cap = [
            'finalizado' => [],
            'programado' => [],
            'suspendido' => [],
            'pendiente' => [],
            'rechazado' => []
        ];

        foreach ($capacitaciones as &$capacitacion) {
            
            $cap[strtolower($capacitacion['estado'])][] = $capacitacion;

        }

        $headers = [
            [
                'text' => 'Nombre',
                'value' => 'nombre',
                'sortable' => false
            ],
            [
                'text' => 'Descripción',
                'value' => 'descripcion',
                'sortable' => false
            ],
            [
                'text' => 'Estado',
                'value' => 'estado',
                'sortable' => false
            ],
            [
                'text' => 'Mes',
                'value' => 'mes_estimado',
                'sortable' => false
            ]
        ];

        $bottom_detail = [
            [
                'text' => 'Total',
                'value' => count($capacitaciones),
                'detail' => [
                    'table' => [
                        'items' => $capacitaciones,
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                'text' => 'Realizadas',
                'value' => count($cap['finalizado']),
                'detail' => [
                    'table' => [
                        'items' => $cap['finalizado'],
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                'text' => 'Rechazadas',
                'value' => count($cap['rechazado']),
                'detail' => [
                    'table' => [
                        'items' => $cap['rechazado'],
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                'text' => 'Pendientes',
                'value' => count($cap['programado']) + count($cap['pendiente']),
                'detail' => [
                    'table' => [
                        'items' => array_merge($cap['programado'], $cap['pendiente']),
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Suspendidas',
                'value' => count($cap['suspendido']),
                'detail' => [
                    'table' => [
                        'items' => $cap['suspendido'],
                        'headers' => $headers
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ];

        if(count($capacitaciones) > 0){

            $porcentaje = round(((count($cap['finalizado']) + count($cap['rechazado'])) / count($capacitaciones)) * 100, 2);

        }else{

            $porcentaje = 0;

        }

        $response = [
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail
        ];

        return $response;
        
    }

}