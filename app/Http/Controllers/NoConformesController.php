<?php

namespace App\Http\Controllers;

use App\SQProceso;
use App\SNC_Control;

use Illuminate\Support\Facades\DB;

class NoConformesController extends Controller{

    public function process($data){

        // * Obtener el id_proceso de la tabla SQ_PROCESO
        $sq_proceso = SQProceso::where('rh_areas_codarea', $data->codarea)
                        ->first();

        $snc = SNC_Control::select(
                    'id',
                    'id_proceso',
                    'usuario as usuario_opera',
                    DB::raw("to_char(fecha_documento, 'DD/MM/YYYY HH24:MI:SS') as fecha"),
                    DB::raw("concat(documento, concat('-', anio)) as expediente")
                )   
                ->where('id_proceso', $sq_proceso->id_proceso)
                ->whereRaw("to_char(fecha_asignacion, 'YYYY-MM') = '$data->date'")
                ->get();

        return $snc;

    }

}