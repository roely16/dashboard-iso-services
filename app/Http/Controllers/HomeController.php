<?php 

namespace App\Http\Controllers;

use App\TipoProceso;

class HomeController extends Controller{

    public function get_menu(){

        try {
            
            $tipos_procesos = TipoProceso::all();

            foreach ($tipos_procesos as &$tipo) {
                
                foreach ($tipo->procesos as $proceso) {
                
                    $proceso->selected = false;

                }

            }

            return response()->json($tipos_procesos);

        } catch (\Throwable $th) {
            
        }

    }

}