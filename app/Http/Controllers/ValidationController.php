<?php 

namespace App\Http\Controllers;

class ValidationController extends Controller{

    public function check_case($indicador){

        try {
            
            $current_date = date('Y-m');
            $data = $indicador->kpi_data;

            // * Para procesar un mes anterior cambiar la dirección de la validación

            if (strtotime($indicador->date) < strtotime($current_date)) {
                
                // * Si es un mes posterior realizar solicitud al controlador encargado de obtener el historial, desde la fuente que aplique

                $result = (object) app('App\Http\Controllers\ConfigController')->get_history($data);

                // * Validar si existe resultado de al menos una fuente
                if (property_exists($result, 'history_empty')) {
                    
                    // * No se encontro información
                    $response = [
                        'current_month' => false,
                        'history_empty' => true,
                        'data' => null
                    ];

                    return $response;

                }

                // * Se encontro información y se debe de retornar
                $response = [
                    'current_month' => false,
                    'history_empty' => false,
                    'data' => $result
                ];

                return $response;

            }

            // * Es un mes actual y se debe de realizar una consulta normal

            $response = [
                'current_month' => true,
                'history_empty' => true,
                'data' => null
            ];

            return $response;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ];

            $indicador->error = $error;

            return $indicador;

        }

        
    }

}