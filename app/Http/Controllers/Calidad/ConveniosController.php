<?php 

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;

class ConveniosController extends Controller{

    public function data(){

        $bottom_detail = [
            [
                'text' => 'Total',
                'value' => 0
            ],
            [
                'text' => 'Válidas',
                'value' => 0
            ],
            [
                'text' => 'SNC',
                'value' => 0
            ],
            [
                'text' => 'Rechazadas',
                'value' => 0
            ]
        ];

        $response = [
            'total' => 100,
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }

}