<?php 

namespace App\Models\Compras;

use Illuminate\Database\Eloquent\Model;

class FondoCompra extends Model{

    protected $table = 'adm_fondo_compra';

    protected $primaryKey = 'id_fondo_compra';

    protected $connection = 'rrhh';

}