<?php

namespace App\Models\Convenios;

use Illuminate\Database\Eloquent\Model;

class Convenio extends Model{

    protected $table = 'mco_convenio';

    protected $primaryKey = 'idconvenio';

    protected $connection = 'cobros-iusi';

}