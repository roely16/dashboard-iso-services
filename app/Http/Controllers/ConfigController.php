<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Empleado;
use App\Proceso;
use App\Area;
use App\Historico;

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
            $colaborador = Empleado::select('nombre', 'apellido', 'nit', 'codarea', 'usuario')
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

            foreach ($procesos as $process) {
                
                $process->loading = false;

            }

            return response()->json($procesos);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

    public function save_data(Request $request){

        try {
            
            // Crear un nuevo registro historico
            $indicador = $request->id;
            $id_proceso = $request->id_proceso;
            $fecha = $request->date;
            $registrado_por = $request->registrado_por;

            // Crear archivo JSON
            $json_name = uniqid() . '.json';
            $file = fopen('json/' . $json_name, "w");
            $txt = "John Doe\n";
            fwrite($file, json_encode($request->all()));
            fclose($file);

            // Registrar en BD
            $historico = new Historico();
            $historico->id_proceso = $id_proceso;
            $historico->id_indicador = $indicador;
            $historico->fecha = $fecha;
            $historico->registrado_por = $registrado_por;
            $historico->path = 'json/' . $json_name;
            $historico->save();

            return response()->json($historico, 200);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage(), 400);

        }

    }

    public function get_dashboard_data(Request $request){

        try {

            $custom_request = new Request();
            $custom_request->replace($request->all());

            $kpi = app('App\Http\Controllers\DashboardController')->get_dashboard($custom_request);

            foreach ($kpi->original['indicadores'] as &$item) {
                
                $item->editable = true;

            }

            return response()->json($kpi->original);

        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage());

        }

    }

    public function get_preview_data(Request $request){

        try {
            
            

        } catch (\Throwable $th) {
           
            return response()->json();

        }

    }

}