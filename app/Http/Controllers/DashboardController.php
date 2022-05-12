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

        $indicadores = Indicador::where('id_proceso', $data->id_proceso)->get();

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

            if ($indicador->controlador) {
                $result = app('App\Http\Controllers' . $indicador->controlador)->{$indicador->funcion}($indicador);
            }
            
        }

        return $indicadores;

    }

}