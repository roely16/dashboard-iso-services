<?php

namespace App\Models\Vales;

use Illuminate\Database\Eloquent\Model;

class Vale extends Model{

    protected $table = 'adm_vales';

    protected $primaryKey = 'valeid';

    protected $connection = 'rrhh';

}