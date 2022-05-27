<?php 

namespace App\Models\Compras;

use Illuminate\Database\Eloquent\Model;

class Gestion extends Model{

    protected $table = 'adm_gestiones';

    protected $primaryKey = 'gestionid';

    protected $connection = 'rrhh';

}