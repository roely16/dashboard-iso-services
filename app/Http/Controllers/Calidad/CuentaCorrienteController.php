<?php

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

class CuentaCorrienteController extends Controller{

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('calidad_json.CALIDAD');

        }

        // * Obtener los servicios no conformes
        $no_conformes = (object) app('App\Http\Controllers\NoConformesController')->process($data);

        $total = DB::connection('catastrousr')->select("SELECT 
                                                            documento, 
                                                            anio, 
                                                            concat(documento, concat('-', anio)) as expediente,
                                                            count(acierto) as acierto, 
                                                            count(error) as error
                                                        FROM catastro.cdo_calidad_cc
                                                        WHERE to_char(fecha, 'YYYY-MM') = '$data->date'
                                                        GROUP BY documento, anio");

        $validos = [];
        $rechazados = [];

        foreach ($total as $item) {
            
            // * Buscar información extra del expediente
            $detail = DB::connection('catastrousr')->select("   SELECT 
                                                                    to_char(doc.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha,
                                                                    band.user_aplic as usuario
                                                                FROM catastro.cdo_documento doc, catastro.cdo_bandeja band
                                                                WHERE band.codigoclase = doc.codigoclase
                                                                AND band.documento = doc.documento
                                                                AND band.anio = doc.anio
                                                                AND band.codtramite IN (322, 323)
                                                                AND band.dependencia NOT IN (100, 99)
                                                                AND band.user_aplic NOT IN ('GCHAJCHALAC')
                                                                AND doc.documento = '$item->documento'
                                                                AND doc.anio = '$item->anio'
                                                            ");

            if (count($detail) > 0) {
                
                $detail = $detail[0];

                $item->fecha = $detail->fecha;
                $item->usuario_opera = $detail->usuario;

            }

            if (intval($item->error) > 0) {
                
                $rechazados [] = $item;

            }else{

                $validos [] = $item;

            }

        }

        $exp_total = [];

        foreach ($total as $expediente) {
            
            $exp_total [] = $expediente->expediente;

        }
        
        // * Por cada elemento del total de expediente verificar si existe entre los SNC
        foreach ($no_conformes as $snc) {
            
            // * Se valida que el número de expediente del snc no exista entre el total de expedientes 
            if (!in_array($snc->expediente, $exp_total)) {
                
                // * Si no existe este se deberá de agregar
                $total [] = $snc;

            }

        }

        $headers = [
            [
                'text' => 'Expediente',
                'value' => 'expediente',
                'sortable' => false
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha',
                'sortable' => false
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario_opera',
                'sortable' => false
            ]
        ];

        $bottom_detail = [
            [
                'text' => 'Total',
                'value' => count($total),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $total
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                'text' => 'Válidas',
                'value' => count($validos),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $validos
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
                'text' => 'Correcciones',
                'value' => count($rechazados),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $rechazados
                    ]
                ],
                'component' => 'tables/TableDetail'
            ]
        ];

        if (count($total) > 0) {
            
            $porcentaje = round((count($validos) / count($total)) * 100, 1);

        }else{

            $porcentaje = 100;

        }

        $response = [
            'total' => $porcentaje,
            'validas' => count($validos),
            'total_calidad' => count($total),
            'bottom_detail' => $bottom_detail
        ];

        return $response;
        

    }

}