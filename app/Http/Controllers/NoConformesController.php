<?php

namespace App\Http\Controllers;

use App\SQProceso;
use App\SNC_Control;

class NoConformesController extends Controller{

    public function process($data){

        // * Obtener el id_proceso de la tabla SQ_PROCESO
        $sq_proceso = SQProceso::where('rh_areas_codarea', $data->codarea)
                        ->first();

        $snc = SNC_Control::where('id_proceso', $sq_proceso->id_proceso)
                ->whereRaw("to_char(fecha_asignacion, 'YYYY-MM') = '$data->date'")
                ->get();

        return $snc;

    }

}