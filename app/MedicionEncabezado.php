<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicionEncabezado extends Model{

    protected $table = 'MSA_MEDICION_ENCABEZADO';

    protected $primaryKey = 'ID_MEDICION';

    protected $connection = 'rrhh';

}