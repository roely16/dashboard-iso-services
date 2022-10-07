<?php

namespace App\Http\Controllers;

class PreviousController extends Controller{

    public function update_previous($data){

        $data_history = (object) [
            'date' => date('Y-m', strtotime($data->date . " -1 month")),
            'id_proceso' => $data->id_proceso,
            'id_indicador' => $data->id_indicador,
            'data_controlador' => $data->data_controlador,
            'controlador' => $data->controlador,
            'nombre_historial' => $data->nombre_historial,
            'subarea_historial' => $data->subarea_historial,
            'campos' => $data->campos,
        ];
        
        $result = app('App\Http\Controllers\ConfigController')->get_history($data_history);

        $response = [
            'value' => $result['bottom_detail'][3]['value'],
            'items' => $result['bottom_detail'][3]['detail']['table']['items']
        ];

        return $response; 

    }


}