<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoProceso extends Model{

    protected $table = 'ISO_DASHBOARD_TIPO_PROCESO';

    protected $primaryKey = 'ID';

    public function procesos(){

        return $this->hasMany('App\Proceso', 'id_tipo_proceso', 'id');

    }

}