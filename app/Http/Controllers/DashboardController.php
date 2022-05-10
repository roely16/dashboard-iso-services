<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Proceso;
use App\Area;
use App\Indicador;

class DashboardController extends Controller{

    public function get_dashboard(Request $request){

        $id_proceso = $request->id_proceso ? $request->id_proceso : 11;

        $title = $this->get_title($id_proceso);

        $indicadores = $this->get_kpi($id_proceso);

        $response = [
            "title" => $title,
            "indicadores" => $indicadores
        ];

        return response()->json($response);

    }

    public function get_title($id_proceso){

        $area = Proceso::find($id_proceso)->area;

        $jefe = $area->empleados()->where('jefe', 1)->where('status', 'A')->first(['nombre', 'apellido', 'nit']);

        $avatar = $jefe->documentos()->where('idcat', 11)->first();
            
        $jefe->avatar = $avatar->ruta;
        
        $response = [
            "area" => $area,
            "jefe" => $jefe
        ];

        return $response;

    }

    public function get_kpi($id_proceso){

        $indicadores = Indicador::where('id_proceso', $id_proceso)->get();

        foreach ($indicadores as &$indicador) {
            
            // Ejecutar la función correspondiente al indicador
            
            $indicador->dark = $indicador->dark === 'S' ? true : false;

            $title = [
                "name" => $indicador->nombre,
                "icon" => [
                    "name" => $indicador->icon,
                    'color' => $indicador->icon_color,
                    'container' => $indicador->icon_container_color
                ]
            ];

            $indicador->title = $title;

            if ($indicador->controlador) {
                $result = app('App\Http\Controllers' . $indicador->controlador)->{$indicador->funcion}($indicador);
            }
            
        }

        return $indicadores;

    }

}