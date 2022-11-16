<?php

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class SIMAController extends Controller{

    public function data($data){

        if (property_exists($data, 'get_structure')) {
            
            return config('calidad_json.CALIDAD');

        }

        $total = DB::connection('catastrousr')->select("    SELECT
                                                                CONCAT(DOC.DOCUMENTO, CONCAT('-', DOC.ANIO)) AS EXPEDIENTE,
                                                                TO_CHAR(DOC.FECHA, 'DD/MM/YYYY HH24:MI:SS') AS FECHA,
                                                                DOC.USUARIO AS USUARIO_OPERA
                                                            FROM CDO_DOCUMENTO DOC
                                                            INNER JOIN CDO_DETDOCUMENTO DET
                                                            ON DOC.DOCUMENTO = DET.DOCUMENTO
                                                            AND DOC.ANIO = DET.ANIO
                                                            AND DOC.CODIGOCLASE = DET.CODIGOCLASE
                                                            AND DOC.CODIGOCLASE = 3
                                                            AND DET.CODTRAMITE = 192
                                                            AND TO_CHAR(DOC.FECHA_ISO, 'YYYY-MM') = '$data->date'
                                                            -- AND DOC.CALIDAD = 2
                                                            ORDER BY DOC.DOCUMENTO ASC
                                                        ");

        $validos = [];
        $rechazados = [];  
        $total_finalizados = [];

        foreach ($total as $item) {
            
            // Verificar el resultado
            $result = DB::connection('catastrousr')->select("SELECT 
                                                                COUNT(ACIERTO) AS ACIERTO, COUNT(ERROR) AS ERROR
                                                            FROM CDO_Q_DOCUMENTO
                                                            WHERE CONCAT(DOCUMENTO, CONCAT('-', ANIO)) = '$item->expediente'
                                                            ");

            if ($result) {
                
                $result = $result[0];

                // if ($result->resultado === 'ACIERTO') {
                    
                //     $validos [] = $item;

                // }else{
                    
                //     $rechazados [] = $item;

                // }

                // * Es válido cuando tiene dos ciertos y 0 errores
                if ($result->acierto == 2 && $result->error == 0) {
                    
                    $validos [] = $item;
                    $total_finalizados [] = $item;

                }elseif($result->error > 0){

                    $rechazados [] = $item;
                    $total_finalizados [] = $item;

                }

            }

        }

        // Servicios No Conformes
        $no_conformes = (object) app('App\Http\Controllers\NoConformesController')->process($data);

        $exp_no_conformes = [];

        foreach ($no_conformes as $snc) {
            
            $exp_no_conformes [] = $snc->expediente;

        }

        $filter_validos = [];

        foreach ($validos as $valido) {
            
            if (!in_array($valido->expediente, $exp_no_conformes)) {
                
                $filter_validos [] = $valido;

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
                "text" => "Total",
                "value" => count($total_finalizados),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $total_finalizados
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'down'
            ],
            [
                "text" => "Válidas",
                "value" => count($filter_validos),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $filter_validos
                    ]
                ],
                'component' => 'tables/TableDetail',
                'divide' => 'up'
            ],
            [
                "text" => "SNC",
                "value" => count($no_conformes),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $no_conformes
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
            [
                "text" => "Correcciones",
                "value" => count($rechazados),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $rechazados
                    ]
                ],
                'component' => 'tables/TableDetail'
            ],
        ];

        if (count($total_finalizados) > 0) {
            
            $porcentaje = round((count($filter_validos) / count($total_finalizados)) * 100, 2);

        }else{

            $porcentaje = 100;

        }

        $response = [
            'total' => $porcentaje,
            'validas' => count($filter_validos),
            'total_calidad' => count($total_finalizados),
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}