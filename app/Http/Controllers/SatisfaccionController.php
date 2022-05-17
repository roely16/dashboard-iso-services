<?php 

namespace App\Http\Controllers;

use App\Proceso;
use App\Models\Satisfaccion\Proceso as MSAProceso;
use App\Models\Satisfaccion\Encabezado;

class SatisfaccionController extends Controller{

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

            $result = $this->data($data);
            $chart = $this->chart($indicador);

            $total = [
                'total' => [
                    'value' => "100%",
                    'style' => ["text-h1", "font-weight-bold"],
                ],
                'chart' => $chart
            ];

            $indicador->content = $total;

            $indicador->bottom_detail = $this->data($data);

            return $indicador;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage()
            ];

            $indicador->error = $error;

            return $indicador;

        }

    }

    public function chart($indicador){

        $chart = [
            'type' => "Bar",
            'chartData' => [
                'labels' => ["January", "February", "March"],
                'datasets'=> [
                    [
                        'data' => [90, 30, 10],
                        'backgroundColor' => [
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(255, 205, 86)'
                        ]
                    ]
                ],
            ],
            'chartOptions' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'display' => false,
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

        $msa_proceso = MSAProceso::where('codarea', $data->codarea)->first()->modelos->where('estado', 'A')->first();

        $evaluaciones = Encabezado::where('id_medicion', $msa_proceso->id_medicion)
                        ->whereRaw("to_char(periodo_del, 'YYYY-MM') = '$data->date'")
                        ->get();

        $aceptables = [];
        $no_conformes = [];

        foreach ($evaluaciones as &$evaluacion) {
            
            $eva_num = [];
            $total = 0;

            foreach ($evaluacion->detalle as $detalle) {
                
                if ($detalle->valor != ' ') {
                    
                    $total += intval($detalle->valor);
                    $eva_num [] = $detalle;

                }

            }

            $evaluacion->eva_num = $eva_num;

            $nota = round($total / count($eva_num), 2);

            // Validar si es mayor que 8 para tomarla como ACEPTABLE
            if ($nota >= 8) {
                
                $aceptables [] = $evaluacion;

            }else {

                $no_conformes [] = $evaluacion;
            
            }

            $evaluacion->total = $nota;

        }

        $headers = [
            [
                'text' => 'Usuario',
                'value' => 'colaborador'
            ],
            [
                'text' => 'Fecha de Opinión',
                'value' => 'fecha_opinion'
            ],
            [
                'text' => 'Total',
                'value' => 'total'
            ]
        ];

        $result = [
            [
                "text" => 'Universo',
                "value" => count($evaluaciones),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $evaluaciones
                    ]
                ]
            ],
            [
                "text" => 'Aceptable',
                "value" => count($aceptables),
            ],
            [
                "text" => 'No Conforme',
                "value" => count($no_conformes),
            ],
        ];

        return $result;

    }

}