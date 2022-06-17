<?php

namespace App\Http\Controllers\Adquisiciones;
use App\Http\Controllers\Controller;

use App\Proceso;
use App\Models\Compras\FondoCompra;
use App\Models\Compras\Gestion;

use Illuminate\Support\Facades\DB;

class ComprasController extends Controller{

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
                            $bottom_detail[2]['value'],
                            $bottom_detail[0]['value'] - $bottom_detail[2]['value']
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
        
        $split_date = explode('-', $data->date);
        $year = $split_date[0];
        $month = intval($split_date[1]);

        // * Obtener las compras ingresadas
        $ingresado = FondoCompra::select(
                            'id_fondo_compra',
                            'detalle_compra',
                            'modalidad_compra',
                            DB::raw("to_char(fecha_elaboracion, 'DD/MM/YYYY HH24:MI:SS') as fecha_elaboracion") 
                        )   
                        ->whereNotIn('distribucion', ['COPEM'])
                        ->where('anio_ejecucion', $year)
                        ->where('programada', $month)
                        ->orderBy('id_fondo_compra', 'asc')
                        ->get();

        $proceso = [];
        $finalizadas = [];

        foreach ($ingresado as &$compra) {
            
            // Por cada una de las compras buscar si existe una gestión y si esta se encuentra en etapa 2
            // * Si es así deberá de ser una compra finalizada de lo contrario queda en pendiente
            $gestion = Gestion::where('id_fondo_compra', $compra->id_fondo_compra)->first();

            if (!$gestion) {
                
                continue;

            }

            $compra->gestionid = $gestion->gestionid;
            $compra->titulo = $gestion->titulo;
            $compra->fecha = $gestion->fecha;

            // * Si existe una gestión, determinar si ya esta en paso 2
            $bitacora_etapas = DB::connection('rrhh')->select(" SELECT * 
                                                                FROM ADM_BITACORA_ETAPAS 
                                                                WHERE GESTIONID = '$gestion->gestionid'
                                                                AND PASO = 2");

            if (count($bitacora_etapas) > 0) {
                
                $finalizadas [] = $compra;

                continue;

            }

            $proceso [] = $compra;

        }

        $headers_ingresado = [
            [
                'text' => 'ID',
                'value' => 'id_fondo_compra',
                'sortable' => false,
                'width' => '10%'
            ],
            [
                'text' => 'Detalle',
                'value' => 'detalle_compra',
                'sortable' => false,
                'width' => '65%'
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha_elaboracion',
                'sortable' => false,
                'width' => '25%'
            ]
        ];

        $headers_gestion = [
            [
                'text' => 'ID',
                'value' => 'id_fondo_compra',
                'sortable' => false,
                'width' => '10%'
            ],
            [
                'text' => 'Detalle',
                'value' => 'detalle_compra',
                'sortable' => false,
                'width' => '30%'
            ],
            [
                'text' => 'Gestión',
                'value' => 'gestionid',
                'sortable' => false,
                'width' => '10%'
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Título',
                'value' => 'titulo',
                'sortable' => false,
                'width' => '30%'
            ]
        ];

        $bottom_detail = [
            [
                'text' => 'Ingresado',
                'value' => count($ingresado),
                'detail' => [
                    'table' => [
                        'headers' => $headers_ingresado,
                        'items' => $ingresado
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Proceso',
                'value' => count($proceso),
                'detail' => [
                    'table' => [
                        'headers' => $headers_gestion,
                        'items' => $proceso
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Finalizado',
                'value' => count($finalizadas),
                'detail' => [
                    'table' => [
                        'headers' => $headers_gestion,
                        'items' => $finalizadas
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ];

        if (count($ingresado) > 0) {
            
            $porcentaje = round((count($finalizadas) / count($ingresado)) * 100, 1);
        
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

?>