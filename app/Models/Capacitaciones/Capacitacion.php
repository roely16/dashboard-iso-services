<?php 

namespace App\Models\Capacitaciones;

use Illuminate\Database\Eloquent\Model;

class Capacitacion extends Model{

    protected $table = 'rh_capacitacion';

    protected $primaryKey = 'id_capacitacion';

    protected $connection = 'rrhh';

}