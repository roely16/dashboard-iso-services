<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SQProceso extends Model{

    protected $table = 'sq_proceso';

    protected $primaryKey = 'id_proceso';

    protected $connection = 'rrhh';

}