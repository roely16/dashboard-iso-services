<?php 

namespace App\Http\Controllers;

use App\TipoProceso;

class HomeController extends Controller{

    public function get_menu(){

        try {
            
            $tipos_procesos = TipoProceso::all();

            foreach ($tipos_procesos as &$tipo) {

                $procesos = $tipo->procesos()->where('ocultar', null)->get();
                
                foreach ($procesos as $proceso) {
                
                    $proceso->selected = false;

                }

                $tipo->procesos = $procesos;

            }

            return response()->json($tipos_procesos);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage());

        }

    }

}