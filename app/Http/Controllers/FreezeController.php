<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Proceso;

class FreezeController extends Controller{

    public function massive_freeze($date = null){

        try {
            
            if (!$date) {
                
                $date = date('Y-m');
                
            }

            // * Obtener la lista de indicadores
            $procesos = Proceso::orderBy('id')->get();

            foreach ($procesos as $proceso) {
                
                $custom_request = new Request();
                $custom_request->replace(['date' => $date, 'id_proceso' => $proceso->id]);

                $kpi = app('App\Http\Controllers\ConfigController')->get_dashboard_data($custom_request);

                // * Por cada indicador ejecutar el proceso de congelamiento
                foreach ($kpi->original['indicadores'] as $indicador) {
                    
                    $indicador_array = json_decode(json_encode($indicador), true);

                    $indicador_array['registrado_por'] = 'SYSTEM';

                    $freeze_request = new Request();
                    $freeze_request->replace($indicador_array);

                    app('App\Http\Controllers\ConfigController')->save_data($freeze_request);

                }

            }

            return response()->json($procesos);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

}