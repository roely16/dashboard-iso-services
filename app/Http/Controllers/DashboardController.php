<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Proceso;
use App\Area;

class DashboardController extends Controller{

    public function get_dashboard(Request $request){

        $id_proceso = $request->id_proceso ? $request->id_proceso : 7;

        $get_process = $request->id_proceso ? true : false;

        $title = $this->get_title($id_proceso, $get_process);

        $response = [
            "title" => $title
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

}