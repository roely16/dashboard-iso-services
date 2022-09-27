<?php 

namespace App\Http\Controllers\Estructura;
use App\Http\Controllers\Controller;

class DireccionAcciones extends Controller{

    public function create($data){

        $data_structure = $data->data_structure;
        $historial = $data->historial;

        $data_structure['eficacia']['value'] = round($historial['campo_1'], 1);

        $data_structure['bottom_detail'][0]['value'] = $historial['campo_2'];
        $data_structure['bottom_detail'][1]['value'] = $historial['campo_3'];
        $data_structure['bottom_detail'][2]['value'] = $historial['campo_4'];
        $data_structure['bottom_detail'][3]['value'] = round($historial['porcentaje'], 1) . '%';

        return $data_structure;

    }

}