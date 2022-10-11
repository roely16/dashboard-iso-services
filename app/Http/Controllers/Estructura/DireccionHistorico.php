<?php 

namespace App\Http\Controllers\Estructura;
use App\Http\Controllers\Controller;

use App\Indicador;
use App\Proceso;

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

        // * Por cada indicador obtener los valores necesarios para realizar la sumatoria
        foreach ($lista_indicadores as &$indicador) {

            array_push($total_detail, ['title' => $indicador->proceso, 'value' => $indicador->data->total]);

            foreach ($indicador->bottom_detail as $detalle) {
                
                if ($i < count($data_structure['bottom_detail'])) {

                    array_push($data_structure['bottom_detail'][$i]['tooltip'], ['title' => $indicador->proceso, 'value' => $detalle['value']]);

                    $data_structure['bottom_detail'][$i]['value'] += $detalle['value'];

                    $i++;

                }

            }

            $i = 0;
        }

        // * Agregar las llaves correspondientes para mostrar el detalle del cálculo del porcentaje
        $data_structure['tooltip'] = $total_detail;
        $data_structure['value'] = $data_structure['total'];
        $data_structure['component'] = 'tables/DireccionDetalle';
        $data_structure['text'] = $indicador->nombre;

        return $data_structure;

    }

}