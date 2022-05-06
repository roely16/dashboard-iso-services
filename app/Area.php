<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model{

    protected $table = 'RH_AREAS';

    protected $primaryKey = 'CODAREA';

    protected $connection = 'rrhh';

    public function empleados(){

        return $this->hasMany('App\Empleado', 'codarea', 'codarea');

    }

}