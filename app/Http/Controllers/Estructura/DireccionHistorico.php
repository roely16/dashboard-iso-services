<?php 

namespace App\Http\Controllers\Estructura;
use App\Http\Controllers\Controller;

use App\Indicador;
use App\Proceso;
use App\IndicadorExcept;

class DireccionHistorico extends Controller{

    public function create($data){
        
        try {
            
            $data_structure = $data->data_structure;

            $historial = $data->historial;
            $request_data = $data->data;

            // * Obtener la información del indicador
            $indicador = Indicador::find($request_data->id_indicador);

            // * Obtener la lista de indicadores en base al tipo
            $lista_indicadores = Indicador::where('tipo', strtolower($indicador->subarea_historial))
                                    ->whereNotIn('id', IndicadorExcept::where('mes', $data->date)->pluck('id_indicador')->toArray())
                                    ->orderBy('id_proceso', 'asc')
                                    ->get();

            // * Obtener la data para cada indicador 
            foreach ($lista_indicadores as &$indicador) {
                
                $indicador->date = $data->date;

                // * Obtener el nombre del proceso 
                $proceso = Proceso::find($indicador->id_proceso);
                $indicador->proceso = $proceso->nombre;

                // * Obtener la información para el indicador
                $kpi_data = app('app\Http\Controllers\DashboardController')->get_info($indicador);

                $indicador->kpi_data = $kpi_data;

                app('App\Http\Controllers' . $indicador->controlador)->{$indicador->funcion}($indicador);

            }

            $data_structure['lista_indicadores'] = $data->data_structure;

            $i = 0;

            $total_detail = [];

            $total_calculado = 0;

            // * Por cada indicador obtener los valores necesarios para realizar la sumatoria
            foreach ($lista_indicadores as &$indicador) {

                array_push($total_detail, ['title' => $indicador->proceso, 'value' => $indicador->data->total]);

                $total_calculado += $indicador->data->total;

                if (!$indicador->not_sum_bottom) {
                    
                    foreach ($indicador->bottom_detail as $detalle) {
                    
                        if ($i < count($data_structure['bottom_detail'])) {
        
                            array_push($data_structure['bottom_detail'][$i]['tooltip'], ['title' => $indicador->proceso, 'value' => $detalle['value']]);
        
                            $data_structure['bottom_detail'][$i]['value'] += $detalle['value'];
        
                            $i++;
        
                        }
        
                    }
                    
                }

                $i = 0;
            }

            $data_structure['total'] = round($total_calculado / count($lista_indicadores), 2);

            // * Agregar las llaves correspondientes para mostrar el detalle del cálculo del porcentaje
            $data_structure['tooltip'] = $total_detail;
            $data_structure['value'] = $data_structure['total'];
            $data_structure['component'] = 'tables/DireccionDetalle';
            $data_structure['text'] = $indicador->nombre;

            // Validar si es el indicador de quejas y se deberán de agregar las de los demás procesos que no tienen indicador de quejas 

            if ($indicador->nombre === 'Quejas') {
                
                $exceptions = [
                    [
                        'name' => 'Liquidaciones',
                        'codarea' => 38,
                        'date' => $data->date
                    ],
                    [
                        'name' => 'Adquisiciones',
                        'codarea' => 28,
                        'date' => $data->date
                    ]
                ];
    
                // * Por cada proceso realizar el proceso de busqueda de quejas 
                foreach ($exceptions as &$exception) {
                    
                    $exception = (object) $exception;

                    $result = app('App\Http\Controllers\QuejasController')->data($exception);

                    // * Por cada dato obtenido, agregar al detalle del indicador 
                    foreach ($result['bottom_detail'] as $item) {
                        
                        foreach ($data_structure['bottom_detail'] as &$detail) {
                        
                            if($item['text'] == $detail['text']){

                                $detail['value'] += intval($item['value']);

                                array_push($detail['tooltip'], ['title' => $exception->name, 'value' => $item['value']]);

                            }

                        }

                    }
                    
                }

            }
            
            return $data_structure;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'data' => $data
            ];

            dd($error);

        }

    }

}