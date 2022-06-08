<?php

namespace App\Models\Convenios;

use Illuminate\Database\Eloquent\Model;

class Bitacora extends Model{

    protected $table = 'mco_bitacora';

    protected $primaryKey = 'idbitacora';

    protected $connection = 'cobros-iusi';

}