<?php

namespace App\Models\Convenios;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model{

    protected $table = 'mco_persona';

    protected $primaryKey = 'idpersona';

    protected $connection = 'cobros-iusi';

}