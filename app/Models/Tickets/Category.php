<?php 

namespace App\Models\Tickets;

use Illuminate\Database\Eloquent\Model;

class Category extends Model{

    protected $table = 'tks_categories';

    protected $primaryKey = 'id';

    protected $connection = 'tickets';

}