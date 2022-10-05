<?php

namespace App\Http\Controllers\Calidad;
use App\Http\Controllers\Controller;

use App\MCAProcesos;

use Illuminate\Support\Facades\DB;

class LiquidacionesPrediosController extends Controller{

    public function data($data){

        return config('calidad_json.CALIDAD_PREDIOS');

    }

}

?>
