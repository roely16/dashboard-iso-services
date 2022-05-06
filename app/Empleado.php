<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model{

    protected $table = 'RH_EMPLEADOS';

    protected $primaryKey = 'NIT';

    protected $connection = 'rrhh';

    public function documentos(){

        return $this->hasMany('App\DocumentoEmpleado', 'nit', 'nit');

    }

}