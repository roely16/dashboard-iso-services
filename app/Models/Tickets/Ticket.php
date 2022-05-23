<?php 

namespace App\Models\Tickets;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model{

    protected $table = 'tks_tickets';

    protected $primaryKey = 'id';

    protected $connection = 'tickets';

}