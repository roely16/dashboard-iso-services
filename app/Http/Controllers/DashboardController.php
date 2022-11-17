<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Proceso;
use App\Indicador;

class DashboardController extends Controller{

    public function get_dashboard(Request $request){
            
        $id_proceso = $request->id_proceso ? $request->id_proceso : 11;
        $date = $request->date;

        $data_request = (object) [
            "id_proceso" => $id_proceso,
            "date" => $date
        ];

        $title = $this->get_title($data_request);
        $indicadores = $this->get_kpi($data_request);

        $response = [
            "title" => $title,
            "indicadores" => $indicadores
        ];

        return response()->json($response, 200);
        
    }

    public function get_title($data){

        $area = Proceso::find($data->id_proceso)->area;

        $jefe = $area->empleados()->where('jefe', 1)->where('status', 'A')->first(['nombre', 'apellido', 'nit']);

        $avatar = $jefe->documentos()->where('idcat', 11)->first();
            
        $jefe->avatar = $avatar->ruta;
        
        $response = [
            "area" => $area,
            "jefe" => $jefe
        ];

        return $response;

    }

    public function get_kpi($data){

        $indicadores = Indicador::where('id_proceso', $data->id_proceso)
                        ->where('OCULTAR', null)
                        ->orderBy('ORDEN', 'ASC')
                        ->get();

        foreach ($indicadores as &$indicador) {
            
            // Ejecutar la función correspondiente al indicador
            
            $indicador->dark = $indicador->dark === 'S' ? true : false;
            $indicador->date = $data->date;

            $title = [
                "name" => $indicador->nombre,
                "icon" => [
                    "name" => $indicador->icon,
                    'color' => $indicador->icon_color,
                    'container' => $indicador->icon_container_color
                ],
                'id_proceso' => $indicador->id_proceso
            ];

            $indicador->title = $title;

            // * Obtener la data necesario para generar la información de cada indicador
            $indicador->kpi_data = $this->get_info($indicador);

            // * Obtener el listado de congelamientos
            $request_list = (object) app('App\Http\Controllers\ConfigController')->get_freeze_list($indicador->kpi_data);

            $indicador->list_freeze = $request_list->list;
            $indicador->last_update = $request_list->last_update;

            try {
                
                if ($indicador->controlador) {
                    app('App\Http\Controllers' . $indicador->controlador)->{$indicador->funcion}($indicador);
                }

            } catch (\Throwable $th) {
                
                $error = [
                    'message' => $th->getMessage(),
                    'file' => $th->getFile(),
                    'line' => $th->getLine()
                ];

                $indicador->error = $error;

            }
            
        }

        return $indicadores;

    }

    public function get_info($indicador){

        $proceso = Proceso::find($indicador->id_proceso);
        $area = $proceso->area;
        $dependencia = $proceso->dependencia;
            
        $data = (object) [
            'codarea' => $area->codarea,
            'date' => $indicador->date,
            'dependencia' => $dependencia,
            'data_controlador' => $indicador->data_controlador,
            'controlador' => $indicador->controlador,
            'id_proceso' => $proceso->id,
            'id_indicador' => $indicador->id,       
            'config' => $indicador->config,
            'nombre_historial' => $indicador->nombre_historial,
            'subarea_historial' => $indicador->subarea_historial,
            'campos' => $indicador->orden_campos ? explode(',', $indicador->orden_campos) : null,
            'estructura_controlador' => $indicador->estructura_controlador,
            'nombre_medicion' => $proceso->nombre_medicion
        ];
        
        return $data;

    }

}