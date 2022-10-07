<?php 

namespace App\Http\Controllers\Estructura;
use App\Http\Controllers\Controller;

use App\Indicador;

class DireccionHistorico extends Controller{

    public function create($data){

        $data_structure = $data->data_structure;
        $historial = $data->historial;
        $request_data = $data->data;

        $data_structure['total'] = $historial['porcentaje'];

        // * Obtener la información del indicador
        $indicador = Indicador::find($request_data->id_indicador);

        // * Obtener la lista de indicadores en base al tipo
        $lista_indicadores = Indicador::where('tipo', strtolower($indicador->subarea_historial))
                                ->orderBy('id_proceso', 'asc')
                                ->get();

        // * Obtener la data para cada indicador 
        foreach ($lista_indicadores as &$indicador) {
            
            $indicador->date = $data->date;

            // * Obtener la información para el indicador
            $kpi_data = app('app\Http\Controllers\DashboardController')->get_info($indicador);

            $indicador->kpi_data = $kpi_data;

            app('App\Http\Controllers' . $indicador->controlador)->{$indicador->funcion}($indicador);

        }

        $data_structure['lista_indicadores'] = $data->data_structure;

        $i = 0;

        // * Por cada indicador obtener los valores necesarios para realizar la sumatoria
        foreach ($lista_indicadores as &$indicador) {

                foreach ($indicador->bottom_detail as $detalle) {
                    
                    if ($i < count($data_structure['bottom_detail'])) {

                        $data_structure['bottom_detail'][$i]['value'] += $detalle['value'];

                        $i++;

                    }

                }

            $i = 0;
        }

        return $data_structure;

    }

}