<?php

namespace App\Models\Convenios;

use Illuminate\Database\Eloquent\Model;

class Indicador extends Model{

    protected $table = 'mco_indicador';

    protected $primaryKey = 'idindicador';

    protected $connection = 'cobros-iusi';

}