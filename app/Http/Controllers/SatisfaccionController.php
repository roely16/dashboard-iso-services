<?php 

namespace App\Http\Controllers;

use App\Proceso;
use App\Models\Satisfaccion\Proceso as MSAProceso;
use App\Models\Satisfaccion\Encabezado;
use App\Models\Satisfaccion\ModeloEncabezado;
use App\Models\Satisfaccion\ModeloDetalle;

use Illuminate\Support\Facades\DB;

class SatisfaccionController extends Controller{
   
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
            $indicador->evaluaciones = $result->evaluaciones;
            $indicador->no_conformes = $result->no_conformes;
            $indicador->data = $result;

            return $indicador;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ];

            $indicador->error = $error;

            return $indicador;

        }

    }

    public function chart($data){

        $bottom_detail = $data->bottom_detail;

        $chart = [
            'type' => "Bar",
            'chartData' => [
                'labels' => [$bottom_detail[1]['text'], $bottom_detail[2]['text']],
                'datasets'=> [
                    [
                        'data' => [$bottom_detail[1]['value'], $bottom_detail[2]['value']],
                        'backgroundColor' => [
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
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

        if (property_exists($data, 'get_structure')) {
            
            return config('satisfaccion_json.SATISFACCION');

        }

        $msa_procesos = MSAProceso::select('idproceso')->where('codarea', $data->codarea)->get()->pluck('idproceso');

        $modelos_encabezado = ModeloEncabezado::select('id_medicion')
                                ->whereIn('idproceso', $msa_procesos)
                                ->get()
                                ->pluck('id_medicion');

        $evaluaciones = Encabezado::select(
                            'correlativo', 
                            'colaborador', 
                            DB::raw("to_char(fecha_opinion, 'DD/MM/YYYY HH24:MI:SS') as fecha_opinion")
                        )
                        ->whereIn('id_medicion', $modelos_encabezado)
                        ->whereRaw("to_char(fecha_opinion, 'YYYY-MM') = '$data->date'")
                        ->get();
                        
        $aceptables = [];
        $no_conformes = [];

        foreach ($evaluaciones as &$evaluacion) {
            
            $eva_num = [];
            $total = 0;

            foreach ($evaluacion->detalle as $detalle) {
                
                // Obtener la pregunta
                $pregunta = ModeloDetalle::where('id_medicion', $detalle->id_medicion)->where('id_pregunta', $detalle->id_pregunta)->first();

                $detalle->pregunta = $pregunta->texto_pregunta;
                $detalle->tipo_valor = $pregunta->tipo_valor;

                if (is_numeric($detalle->valor)) {
                    
                    $total += intval($detalle->valor);
                    $eva_num [] = $detalle;

                }

            }

            $evaluacion->eva_num = $eva_num;

            $nota = round($total / count($eva_num), 2);

            // Validar si es mayor que 8 para tomarla como ACEPTABLE
            if ($nota >= 8) {
                
                $evaluacion->color = 'success';

                $aceptables [] = $evaluacion;

            }else {

                $evaluacion->color = 'error';

                $no_conformes [] = $evaluacion;
            
            }

            $evaluacion->total = $nota;

        }

        $headers = [
            [
                'text' => 'Correlativo',
                'value' => 'correlativo',
                'width' => '25%',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'colaborador',
                'width' => '25%',
                'sortable' => false
            ],
            [
                'text' => 'Fecha de Opinión',
                'value' => 'fecha_opinion',
                'width' => '25%',
                'sortable' => false
            ],
            [
                'text' => 'Total',
                'value' => 'total',
                'width' => '25%',
                'align' => 'center',
                'sortable' => false
            ],
            [
                'text' => '',
                'value' => 'data-table-expand'
            ]
        ];

        $bottom_detail = [
            [
                "text" => 'Universo',
                "value" => count($evaluaciones),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $evaluaciones
                    ],
                ],
                'component' => 'tables/TableSatisfaccion'
            ],
            [
                "text" => 'Aceptable',
                "value" => count($aceptables),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $aceptables
                    ],
                ],
                'component' => 'tables/TableSatisfaccion'
            ],
            [
                "text" => 'No Conforme',
                "value" => count($no_conformes),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $no_conformes
                    ],
                ],
                'component' => 'tables/TableSatisfaccion'
            ],
        ];

        $response = [
            'total' => count($evaluaciones) > 0 ? round(100 - ((count($no_conformes) / count($evaluaciones)) * 100), 1) : 100,
            'evaluaciones' => count($evaluaciones),
            'no_conformes' => count($no_conformes),
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}