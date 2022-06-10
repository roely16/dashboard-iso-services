<?php

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use App\Models\Atencion\ISOAtencion;

use Illuminate\Support\Facades\DB;

class ContribuyenteController extends Controller{

    public function data($data){

        // Obtener los servicios no conformes
        $no_conformes = (object) app('App\Http\Controllers\NoConformesController')->process($data);

        $historial = ISOAtencion::select(
                        'usuario_trabajo',
                        DB::raw("to_char(fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha"),
                        'intento',
                        'resultado',
                        'usuario',
                        'historial'
                    )
                    ->whereRaw("to_char(fecha, 'YYYY-MM') = '$data->date'")
                    ->get();

        $historial_array = [];
        $validas = [];
        $rechazadas = [];

        $total = 0;

        foreach ($historial as $item) {
            
            $array_string = explode(';', $item->historial);

            $num_expendientes = (count($array_string)) / 18;

            // Quitar el primer elemento
            array_shift($array_string);

            $start = 0;

            for ($i = 1; $i <= $num_expendientes; $i++) { 
                
                $element = (object) [
                    'expediente' => $array_string[$start],
                    'usuario_opera' => $array_string[$start + 1],
                    'fecha' => $item->fecha,
                    'respuestas' => (object) [
                        'respuesta_3' => substr($array_string[$start + 3], 0, 2),
                        'respuesta_4' => substr($array_string[$start + 4], 0, 2),
                        'respuesta_10' => substr($array_string[$start + 10], 0, 2),
                        'respuesta_11' => substr($array_string[$start + 11], 0, 2)
                    ]
                ];

                // Verificar si el expediente es válido en base a los resultados obtenidos 
                /* 
                    * El primer elemento debe de tener como respuesta SI
                    * El segundo elemento debe de tener como respuesta SI
                    * El tercer elemento debe de ser diferente a NO
                    * El cuarto elemento debe de ser diferente a NO
                */

                if ($element->respuestas->respuesta_3 == 'SI' && $element->respuestas->respuesta_4 == 'SI' && $element->respuestas->respuesta_10 != 'NO' && $element->respuestas->respuesta_11 != 'NO') {
                    
                    $validas [] = $element;

                }else{

                    $rechazadas [] = $element;

                }

                $historial_array [] = $element;

                $start =+ 18;
        
            }

            $total = $total + (count($array_string)) / 18;

        }

        // * Si el o los servicios no conformes no existen en el total de expedientes sujetos a procesos de calidad, agregarlos 
        
        // * Se obtiene un array con los números de expediente de los servicios no conformes
               
        $exp_total = [];

        foreach ($historial_array as $expediente) {
            
            $exp_total [] = $expediente->expediente;

        }
        
        // * Por cada elemento del total de expediente verificar si existe entre los SNC
        foreach ($no_conformes as $snc) {
            
            // * Se valida que el número de expediente del snc no exista entre el total de expedientes 
            if (!in_array($snc->expediente, $exp_total)) {
                
                // * Si no existe este se deberá de agregar
                $historial_array [] = $snc;

            }

        }

        $headers = [
            [
                'text' => 'Expediente',
                'value' => 'expediente'
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha'
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario_opera'
            ]
        ];

        $bottom_detail = [
            [
                'text' => 'Total',
                'value' => count($historial_array),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $historial_array
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Válidas',
                'value' => count($validas),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $validas
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'SNC',
                'value' => count($no_conformes),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $no_conformes
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Rechazadas',
                'value' => count($rechazadas),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $rechazadas
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ];

        if (count($historial_array) > 0) {
            
            $porcentaje = round((count($validas) / count($historial_array)) * 100, 1);

        }else{

            $porcentaje = 100;

        }

        $response = [
            'total' => $porcentaje,
            'validas' => count($validas),
            'total_calidad' => count($historial_array),
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}