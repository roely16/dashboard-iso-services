<?php

namespace App\Models\Atencion;

use Illuminate\Database\Eloquent\Model;

class ISOAtencion extends Model{

    protected $table = 'iso_atencion_usuario';

    protected $primaryKey = null;

    protected $connection = 'portales';

}