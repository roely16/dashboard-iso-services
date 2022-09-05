<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Empleado;
use App\Proceso;
use App\Area;

class ConfigController extends Controller{

    public function login(Request $request){

        try {
            
            $access_users = ['HECHUR', 'JGUTIERREZ'];

            // * Validar que el usuario exista dentro de la lista de permitidos
            if (!in_array(strtoupper($request->user), $access_users)) {
                
                // * Responder excepción
                return response()->json('Usuario no permitido', 400);

            }

            // * Validar contraseña
            $colaborador = Empleado::select('nombre', 'apellido', 'nit', 'codarea')
                            ->whereRaw(DB::raw("upper(usuario) like upper('$request->user')"))
                            ->whereRaw(DB::raw("DESENCRIPTAR(pass) = upper('$request->password')"))
                            ->first();


            // * Si la clave es incorrecta
            if (!$colaborador) {
                return response()->json('Usuario no permitido', 400);
            }

            // * Obtener el área del colaborador
            $area = Area::find($colaborador->codarea);

            $colaborador->area = $area->descripcion;

            return response()->json($colaborador);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

    public function get_process(){

        try {
            
            // * Obtener la lista de procesos
            $procesos = Proceso::orderBy('id', 'asc')->get();

            return response()->json($procesos);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

}