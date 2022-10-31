<?php

namespace App\Http\Controllers\Estructura;
use App\Http\Controllers\Controller;

use App\Models\Atencion\IngresoExpediente;

class AtencionEficacia extends Controller{

    public function create($data){

        $data_structure = $data->data_structure;

        $historial = $data->historial;

        $data_structure['cumplimiento'] = round(($historial['campo_2'] / $historial['campo_1']) * 100, 1);

        $data_structure['total'] = round(($historial['campo_2'] / $historial['campo_1']) * 100, 1);

        $data_structure['mensual'] = round(($historial['campo_3'] / $historial['campo_1']) * 100, 1);
        $data_structure['trimestral'] = round(($historial['campo_4'] / $historial['campo_1']) * 100, 1);

        $data_structure['menor_10']['value'] = $historial['campo_2'];
        $data_structure['menor_20']['value'] = $historial['campo_3'];
        $data_structure['menor_45']['value'] = $historial['campo_4'];

        $data_structure['bottom_detail'][0]['value'] = $historial['campo_1'];
        $data_structure['bottom_detail'][2]['value'] = $historial['suspendido'];

        $ingreso_expedientes = IngresoExpediente::whereRaw("to_char(fecha, 'YYYY-MM') = '$data->date'")
                                ->orderBy('id_ingreso_expediente', 'asc')
                                ->get();

        $data_structure['bottom_detail'][1]['value'] = count($ingreso_expedientes);

        return $data_structure;

    }

}