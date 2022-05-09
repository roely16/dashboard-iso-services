<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Indicador extends Model{

    protected $table = 'ISO_DASHBOARD_INDICADOR';

    protected $primaryKey = 'ID';

    public function proceso(){

        return $this->belongsTo('App\Proceso');

    }

}