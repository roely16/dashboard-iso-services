<?php

namespace App\Models\Satisfaccion;

use Illuminate\Database\Eloquent\Model;

class Encabezado extends Model{

    protected $table = 'MSA_MEDICION_ENCABEZADO';

    protected $primaryKey = null;

    protected $connection = 'rrhh';

    public function detalle(){

        return $this->hasMany('App\Models\Satisfaccion\Detalle', 'correlativo', 'correlativo');

    }

}