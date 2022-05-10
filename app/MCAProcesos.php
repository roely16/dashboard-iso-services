<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MCAProcesos extends Model{

    protected $table = 'SGC.MCA_PROCESOS_VW';

    protected $primaryKey = null;

    protected $connection = 'catastrousr';

}