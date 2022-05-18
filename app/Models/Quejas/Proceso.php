<?php

namespace App\Models\Quejas;

use Illuminate\Database\Eloquent\Model;

class Proceso extends Model{

    protected $table = 'SQ_PROCESO';

    protected $primaryKey = 'ID_PROCESO';

    protected $connection = 'rrhh';

}