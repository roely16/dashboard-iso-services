<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Proceso;
use App\Area;
use App\Indicador;

class DashboardController extends Controller{

    public function get_dashboard(Request $request){

        $id_proceso = $request->id_proceso ? $request->id_proceso : 7;

        $get_process = $request->id_proceso ? true : false;

        $title = $this->get_title($id_proceso, $get_process);

        $indicadores = $this->get_kpi($id_proceso);

        $response = [
            "title" => $title,
            "indicadores" => $indicadores
        ];

        return response()->json($response);

    }

    public function get_title($id_proceso, $get_process){

        if ($get_process) {
            
            $area = Proceso::find($id_proceso)->area;
            
        }else{

            $area = Area::find($id_proceso);

        }

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