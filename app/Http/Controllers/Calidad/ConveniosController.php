<?php 

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use App\Empleado;
use App\Models\Convenios\Convenio;

use Illuminate\Support\Facades\DB;

class ConveniosController extends Controller{

    public function data($data){

        // * Obtener los servicios no conformes
        $no_conformes = (object) app('App\Http\Controllers\NoConformesController')->process($data);
        
        // echo json_encode($no_conformes);

        // exit;

        $total = DB::connection('cobros-iusi')->select(" SELECT 
                                                            t1.idpersona,
                                                            to_char(t1.fecha, 'DD/MM/YYYY HH24:MI:SS') as fecha, 
                                                            t2.nit as usuario
                                                        FROM mco_persona t1
                                                        INNER JOIN mco_bitacora t2
                                                        ON t1.idpersona = t2.idpersona
                                                        WHERE to_char(t2.fecha, 'YYYY-MM') = '$data->date'
                                                        AND t2.idestado = 7");

        $validos = [];
        $rechazados = [];

        foreach ($total as $item) {
            
            // * Buscar el nombre del usuario
            $empleado = Empleado::find($item->usuario);

            $item->usuario_opera = $empleado->usuario;

            // * Buscar el número de convenio
            $convenio = Convenio::where('idpersona', $item->idpersona)->first();

            $item->expediente = $convenio ? $convenio->no_convenio . '-' . $convenio->anio : null;

            // * Buscar si tiene registro en la bitacora
            $bitacora = DB::connection('cobros-iusi')->select(" SELECT *
                                                                FROM mco_bitacora
                                                                WHERE idpersona = $item->idpersona
                                                                AND idestado = 8");         

            if (count($bitacora) > 0) {
                
                $rechazados [] = $item;

            }else{
                
                $validos [] = $item;

            }

        }

        $headers = [
            [
                'text' => 'Convenio',
                'value' => 'expediente',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Fecha',
                'value' => 'fecha',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Usuario',
                'value' => 'usuario_opera',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Resultado',
                'value' => 'resultado',
                'sortable' => false,
                'width' => '25%'
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
                'text' => 'Rechazadas',
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