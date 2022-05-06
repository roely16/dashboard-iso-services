<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentoEmpleado extends Model{

    protected $table = 'RH_RUTA_PDF';

    protected $primaryKey = 'ID_RUTAS';

    protected $connection = 'rrhh';

}