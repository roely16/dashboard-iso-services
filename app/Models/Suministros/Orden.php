<?php

namespace App\Models\Suministros;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model{

    protected $table = 'adm_ordenes';

    protected $primaryKey = 'ordenid';

    protected $connection = 'rrhh';

}