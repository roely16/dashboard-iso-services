<?php

namespace App\Http\Controllers\Direccion;
use App\Http\Controllers\Controller;

use App\Indicador;
use App\Proceso;
use App\IndicadorExcept;

class EficaciaController extends Controller{

    public function create($indicador){

        $data = $indicador->kpi_data;

        // * Validar si es una consulta de un mes posterior o actual 
        $result = (object) app('App\Http\Controllers\ValidationController')->check_case($indicador);

        $result = $result->data ? $result->data : (object) $this->data($data);
        
        $chart = $this->chart($result);

        $total = [
            'data' => $result,
            'total' => [
                'value' =>  $result->total,
            ],
            'chart' => $chart
        ];

        $indicador->content = $total;
        $indicador->bottom_detail = $result->bottom_detail;
        $indicador->indicadores = $result->indicadores;
        $indicador->data = $result;

        return $indicador;

    } 
    
    public function chart($result){

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

    }

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('eficacia_json.EFICACIA_DIRECCION');

        }

        // Obtener la lista de procesos que tienen un indicador de eficacia
        $indicadores = Indicador::where('tipo', 'eficacia')
                        ->whereNotIn('id', IndicadorExcept::where('mes', $data->date)->pluck('id_indicador')->toArray())
                        ->orderBy('id_proceso', 'asc')
                        ->get();

        foreach ($indicadores as $indicador) {
            
            $indicador->date = $data->date;

            // * Obtener el nombre del proceso 
            $proceso = Proceso::find($indicador->id_proceso);
            $indicador->proceso = $proceso->nombre;

            // * Obtener la información para el indicador
            $kpi_data = app('app\Http\Controllers\DashboardController')->get_info($indicador);

            $indicador->kpi_data = $kpi_data;
            
            app('App\Http\Controllers' . $indicador->controlador)->{$indicador->funcion}($indicador);

        }

        $i = 0;
        $carga_trabajo = 0;
        $total_resueltos = 0;

        $bottom_detail = [
            [
                'text' => 'Anteriores',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true,
                'tooltip' => []
            ],
            [
                'text' => 'Ingresados',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true,
                'tooltip' => []
            ],
            [
                'text' => 'Resueltos',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true,
                'tooltip' => []
            ],
            [
                'text' => 'Pendientes',
                'value' => null,
                'component' => 'tables/TableProcesos',
                'fullscreen' => true,
                'tooltip' => []
            ]
        ];

        $indicadores_p = $indicadores;
        $suma_porcentajes = 0;
        $num_indicadores = 0;

        $total_detail = [];

        // Por cada indicador obtener los valores necesarios para realizar la sumatoria
        foreach ($indicadores as &$indicador) {
            
            $carga_trabajo += $indicador->carga_trabajo;
            $total_resueltos += $indicador->total_resueltos;
            $suma_porcentajes += $indicador->data->total;
            $num_indicadores++;

            array_push($total_detail, ['name' => $indicador->nombre, 'title' => $indicador->proceso, 'value' => $indicador->data->total]);

            foreach ($indicador->bottom_detail as $detalle) {
                
                if ($i < count($bottom_detail)) {
                
                    $bottom_detail[$i]['value'] += $detalle['value'];
                    
                    array_push($bottom_detail[$i]['tooltip'], ['name' => $indicador->nombre, 'title' => $indicador->proceso, 'value' => $detalle['value']]);

                    $areas = [];

                    foreach ($indicadores_p as $indicador_p) {
                        
                        $area = Proceso::find($indicador_p->id_proceso)->area;

                        $empty_table = [
                            'table' => [
                                'headers' => [],
                                'items' => []
                            ]
                        ];

                        
                        if ($i < count($indicador_p->bottom_detail)) {

                            $area->bottom_detail = array_key_exists('detail', $indicador_p->bottom_detail[$i]) ? $indicador_p->bottom_detail[$i]['detail'] : $empty_table;
                            $area->component = array_key_exists('component', $indicador_p->bottom_detail[$i]) ? $indicador_p->bottom_detail[$i]['component'] : null;
                        
                        }
                        
                        $areas [] = $area;

                    }

                    $bottom_detail[$i]['detail'] = $areas;
                    $i++;

                }

            }

            $i = 0;
        }

        
        $porcentaje = 0;

        if ($carga_trabajo > 0) {
            
            $porcentaje = round($suma_porcentajes / $num_indicadores, 1);

            $porcentaje = $porcentaje > 100 ? 100 : $porcentaje;

        }
       
        $response = [
            'indicadores' => $indicadores,
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail,
            'suma_porcentajes' => $suma_porcentajes,
            'tooltip' => $total_detail,
            'value' => $porcentaje,
            'component' => 'tables/DireccionDetalle',
            'text' => $indicador->nombre
        ];

        return $response;

    }

}