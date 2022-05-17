<?php

namespace App\Models\Satisfaccion;

use Illuminate\Database\Eloquent\Model;

class ModeloEncabezado extends Model{

    protected $table = 'MSA_MODELO_ENCABEZADO';

    protected $primaryKey = 'ID_MEDICION';

    protected $connection = 'rrhh';

}