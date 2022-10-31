<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Proceso;

use App\Mail\DataFreeze;
use Illuminate\Support\Facades\Mail;

class FreezeController extends Controller{

    public function massive_freeze($date = null){

        try {
            
            // * Validar que sea el último día del mes 
            $last_day_month = date('Y-m-t');
            $current_day_month = date('Y-m-d');

            if ($last_day_month == $current_day_month) {
                
                $start_time = date('d-m-Y H:i:s');

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
                
                $finish_time = date('d-m-Y H:i:s');

                // * Enviar emails de confirmación
                $emails = ['gerson.roely@gmail.com', 'jgutierrezgomez@gmail.com'];

                $data_email = (object) [
                    'date' => $date,
                    'start_time' => $start_time,
                    'finish_time' => $finish_time
                ];

                Mail::to($emails)->send(new DataFreeze($data_email));

                return response()->json($procesos);

            }

            return response()->json($current_day_month);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

}