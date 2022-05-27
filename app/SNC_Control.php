<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SNC_Control extends Model{

    protected $table = 'snc_control';

    protected $primaryKey = 'id';

    protected $connection = 'catastrousr';

}