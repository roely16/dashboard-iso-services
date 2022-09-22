<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Empleado;
use App\Proceso;
use App\Area;
use App\Historico;
use App\Historial;
use App\Indicador;

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

            $backup_data = $request->data;

            // * Actualizar el detalle inferior
            $backup_data['bottom_detail'] = $request->bottom_detail;

            // * Actualizar el total 
            $backup_data['total'] = $request->content['total']['value'];

            // Crear archivo JSON
            $json_name = uniqid() . '.json';
            $file = fopen('json/' . $json_name, "w");
            $txt = "John Doe\n";
            fwrite($file, json_encode($backup_data));
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

    public function get_history($data){

        // * Es un mes anterior por lo que es necesario verificar en el historico
        $historico = Historico::where('id_proceso', $data->id_proceso)
                        ->where('id_indicador', $data->id_indicador)
                        ->where('fecha', $data->date)
                        ->orderBy('id', 'desc')
                        ->first();

        // * Si se encuentra un registro historico
        if ($historico) {

            $json_data = json_decode(file_get_contents($historico->path), true);

            return $json_data;

        }

        // * Armar el objeto en base a la información del dashboard anterior

        // * Obtener un objeto vacio con la nueva estructura para colocar la información del dashboard anterior

        $data->get_structure = true; 

        $controller = $data->data_controlador ? $data->data_controlador : $data->controlador;

        $data_structure =  app('App\Http\Controllers' . $controller)->data($data);

        // * Obtener la información del historico del dashboard anterior

        $split_date = explode('-', $data->date);
        $year = $split_date[0];
        $month = intval($split_date[1]);

        $historial_anterior = Historial::where('anio', $year)
                                ->where('mes', $month)
                                ->where('area', $data->nombre_historial)
                                ->where('sub_area', $data->subarea_historial)
                                ->first();

        $data_structure['total'] = $historial_anterior->porcentaje;
        
        $i = 1;
        $e = 0;

        foreach ($data_structure['bottom_detail'] as &$item) {
            
            $current_field = property_exists($data, 'campos') ? $data->campos[$e] : ('campo_' . $i);

            $item['value'] = $historial_anterior->{$current_field};

            $i++;
            $e++;

        }

        return $data_structure;

    }

    public function test_cat(Request $request){

        try {
            
            // * Ubicar el proceso
            $proceso = Proceso::find($request->id_proceso);

            $split_date = explode('-', $request->date);
            $year = $split_date[0];
            $month = intval($split_date[1]);

            // * Obtener el historial anterior
            $historial = Historial::where('area', $proceso->nombre_historial)
                            ->where('anio', $year)
                            ->where('mes', $month)
                            ->get();

            // * Obtener indicadores del proceso
            $indicadores = Indicador::where('id_proceso', $proceso->id)->get();

            return response()->json($indicadores);
            
        } catch (\Throwable $th) {
            
            return response()->json($th->getMessage());

        }

    }

}