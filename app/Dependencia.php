<?php


namespace App;

use Illuminate\Database\Eloquent\Model;

class Dependencia extends Model{

    protected $table = 'CATASTRO.CDO_DEPENDENCIAS';

    protected $primaryKey = 'codigo';

    protected $connection = 'catastrousr';

}