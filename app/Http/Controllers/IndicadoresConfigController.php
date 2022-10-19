<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\IndicadorExcept;
use App\Proceso;
use App\Indicador;

use Illuminate\Support\Facades\DB;

class IndicadoresConfigController extends Controller{

    public function get_list(){

        try {
            
            $list = IndicadorExcept::orderBy('id', 'desc')->get();

            $headers = [
                [
                    'text' => 'ID',
                    'value' => 'id'
                ],
                [
                    'text' => 'Periodo',
                    'value' => 'periodo'
                ],
                [
                    'text' => 'Procesos',
                    'value' => 'procesos'
                ],
                [
                    'text' => 'Acción',
                    'value' => 'action' 
                ]
            ];

            $response = [
                'items' => $list,
                'headers' => $headers
            ];

            return response()->json($response, 200);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

    public function new_config(){

        try {
            
            $types = [
                [
                    'text' => 'Calidad',
                    'value' => 'calidad'
                ],
                [
                    'text' => 'Eficacia',
                    'value' => 'eficacia'
                ],
                [
                    'text' => 'Satisfacción',
                    'value' => 'satisfaccion'
                ],
                [
                    'text' => 'Quejas',
                    'value' => 'quejas'
                ]
            ];

            // * Obtener los indicadores por cada tipo

            foreach ($types as &$type) {
                
                $value = $type['value'];

                $indicadores = DB::connection('portales')->select(" SELECT 
                                                                        t1.id, 
                                                                        t1.nombre, 
                                                                        t2.nombre as proceso
                                                                    from iso_dashboard_indicador t1
                                                                    inner join iso_dashboard_proceso t2
                                                                    on t1.id_proceso = t2.id
                                                                    where tipo = '$value'");

                foreach ($indicadores as &$indicador) {
                    
                    $indicador->selected = false;

                }

                $type['indicadores'] = $indicadores;

            }

            return response()->json($types);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

    public function create(Request $request){

        try {
            
            // * Obtener los indicadores que no formaran parte del mes y la fecha
            $exceptions = $request->exception;
            $date = $request->date;

            // * Registrar los indicadores que no se deberán de tomar en cuenta en un mes en especifico
            foreach ($exceptions as $exception) {
                
                $new_exception = new IndicadorExcept();
                $new_exception->mes = $date;
                $new_exception->id_indicador = $exception;
                $new_exception->registrado_por = 'SYSTEM';
                $new_exception->save();

            }
            return response()->json($exceptions);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }
    }

    public function edit(Request $request){

        try {
            


        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

    public function delete(Request $request){

        try {
            


        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }
    
}