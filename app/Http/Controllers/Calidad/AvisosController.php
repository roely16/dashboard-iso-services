<?php

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use App\Models\Avisos\ISOAvisos;

class AvisosController extends Controller{

    public function data($data){

        $total = ISOAvisos::whereRaw("to_char(fecha, 'YYYY-MM') = '$data->date'")->get();

        $validos = [];
        $rechazados = [];       

        foreach ($total as &$item) {
            
            if ($item['resultado'] === 'VALIDO') {
                
                $validos [] = $item;

            }else{

                $rechazados [] = $item;

            }

        }
        
        // Obtener los servicios no conformes 

        $no_conformes = app('App\Http\Controllers\NoConformesController')->process($data);

        $bottom_detail = [
            [
                "text" => "Total",
                "value" => count($total),
            ],
            [
                "text" => "Válidas",
                "value" => count($validos),
            ],
            [
                "text" => "SNC",
                "value" => count($no_conformes),
            ],
            [
                "text" => "Rechazadas",
                "value" => count($rechazados),
            ],
        ];

        $response = [
            'total' => 100,
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}