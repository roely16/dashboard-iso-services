<?php

namespace App\Models\Quejas;

use Illuminate\Database\Eloquent\Model;

class Queja extends Model{

    protected $table = 'SQ_QUEJA';

    protected $primaryKey = 'ID_QUEJA';

    protected $connection = 'rrhh';

}