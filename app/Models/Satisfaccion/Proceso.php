<?php

namespace App\Models\Satisfaccion;

use Illuminate\Database\Eloquent\Model;

class Proceso extends Model{

    protected $table = 'MSA_PROCESO';

    protected $primaryKey = 'IDPROCESO';

    protected $connection = 'rrhh';

    public function modelos(){

        return $this->hasMany('App\Models\Satisfaccion\ModeloEncabezado', 'idproceso', 'idproceso');

    }

}