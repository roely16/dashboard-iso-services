<?php

namespace App\Models\Atencion;

use Illuminate\Database\Eloquent\Model;

class IngresoExpediente extends Model{

    protected $table = 'catastro.aav_ingreso_expediente';

    protected $primaryKey = 'id_transaccion';

    protected $connection = 'catastrousr';

}