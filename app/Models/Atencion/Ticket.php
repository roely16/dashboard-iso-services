<?php

namespace App\Models\Atencion;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model{

    protected $table = 'sgc.indicador_tickets_final';

    protected $primaryKey = 'id_transaccion';

    protected $connection = 'catastrousr';

}