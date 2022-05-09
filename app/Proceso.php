<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proceso extends Model{

    protected $table = 'ISO_DASHBOARD_PROCESO';

    protected $primaryKey = 'ID';

    public function tipo_proceso(){

        return $this->belongsTo('App\TipoProceso');

    }

    public function area(){

        return $this->setConnection('rrhh')->hasOne('App\Area', 'codarea', 'codarea');

    }

    public function indicadores(){

        return $this->hasMany('App\Indicador', 'id_proceso', 'id');

    }

}