<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Historico extends Model{

    protected $table = 'ISO_DASHBOARD_HISTORICO';

    protected $primaryKey = 'ID';

    protected $connection = 'portales';

}