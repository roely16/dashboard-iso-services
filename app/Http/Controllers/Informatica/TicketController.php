<?php 

namespace App\Http\Controllers\Informatica;
use App\Http\Controllers\Controller;

use App\Models\Tickets\Ticket;

use Illuminate\Support\Facades\DB;

class TicketController extends Controller {

    public function create($indicador){

        $data = (object) [
            'date' => $indicador->date
        ];

        $result = (object) $this->data($data);

        $chart = $this->chart($result);

        $total = [
            'data' => $result,
            'total' => [
                'value' =>  $result->total . "%",
            ],
            'chart' => $chart
        ];

        $indicador->content = $total;
        $indicador->bottom_detail = $result->bottom_detail;

        return $indicador;

    }

    public function chart($data){

        $bottom_detail = $data->bottom_detail;

        $chart = [
            'type' => "Doughnut",
            'chartData' => [
                'datasets'=> [
                    [
                        'data' => [
                            $bottom_detail[2]['value'],
                            $bottom_detail[3]['value']
                        ],
                        'backgroundColor' => [
                            "rgb(128,232,155)",
                            "rgba(54, 162, 235, 0.1)",
                        ],
                    ]
                ],
            ],
            'chartOptions' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'display' => false,
                    ],
                    'tooltips' => [
                        'enabled' => false
                    ],
                ],
                'scales' => [
                    'y' => [
                        'display' => false,
                    ],
                    'x' => [
                        'display' => false,
                    ],
                ],
            ],
        ];

        return $chart;

    }

    public function data($data){

        $mes_anterior = date('Y-m', strtotime($data->date . ' -1 month'));

        // * Los tickets pendientes en el mes actual son aquellos que fueron ingresados en el mes anterior y que fueron finalizados en el mes actual o bien que aún no han sido finalizados
            
        // Tickets pendientes del mes anterior
        $anteriores = DB::connection('tickets')->select("SELECT 
                                                            tk.id,
                                                            tk.trackid,
                                                            tk.name,
                                                            tk.category,
                                                            tk.subject,
                                                            tk.message,
                                                            DATE_FORMAT(dt, '%d/%m/%Y %H:%i:%s') AS dt,
                                                            DATE_FORMAT(fecha_cierre, '%d/%m/%Y') AS fecha_cierre,
                                                            us.name AS tecnico
                                                        FROM tks_tickets tk
                                                        LEFT JOIN tks_users us
                                                        ON tk.owner = us.id
                                                        WHERE DATE_FORMAT(dt, '%Y-%m') = '$mes_anterior'
                                                        AND (
                                                            DATE_FORMAT(fecha_cierre, '%Y-%m') = '$data->date'
                                                            OR fecha_cierre IS NULL
                                                        )
                                                        AND category IN (
                                                            SELECT id
                                                            FROM tks_categories 
                                                            WHERE reportar = 1
                                                        )
                                                        ORDER BY tk.id ASC");

        //* Se toman aquellos tickets ingresados únicamente en el mes actual 

        // Tickets ingresados en el mes 
        $ingresados = Ticket::select(
                        'tks_tickets.id',
                        'trackid',
                        'tks_tickets.name',
                        'category',
                        'subject',
                        'message',
                        DB::raw("date_format(dt, '%d/%m/%Y %H:%i:%s') as dt"),
                        'tks_users.name as tecnico',
                        DB::raw("date_format(fecha_cierre, '%d/%m/%Y %H:%i:%s') as fecha_cierre")
                    )
                    ->join('tks_categories', 'tks_tickets.category', '=', 'tks_categories.id')
                    ->join('tks_users', 'tks_tickets.owner', '=', 'tks_users.id')
                    ->whereRaw("date_format(dt, '%Y-%m') = '$data->date'")
                    ->where('tks_categories.reportar', 1)
                    ->orderBy('tks_tickets.id', 'ASC')
                    ->get();
        
        // !Verificar si se debe de limitar a los ticketes atendidos en el mes actual y en el anterior
        
        // Tickets resueltos en el mes
        $resueltos = Ticket::select(
                        'tks_tickets.id',
                        'trackid',
                        'tks_tickets.name',
                        'category',
                        'subject',
                        'message',
                        DB::raw("date_format(dt, '%d/%m/%Y %H:%i:%s') as dt"),
                        'tks_users.name as tecnico',
                        DB::raw("date_format(fecha_cierre, '%d/%m/%Y %H:%i:%s') as fecha_cierre")
                    )
                    ->join('tks_categories', 'tks_tickets.category', '=', 'tks_categories.id')
                    ->join('tks_users', 'tks_tickets.owner', '=', 'tks_users.id')
                    ->whereRaw("date_format(fecha_cierre, '%Y-%m') = '$data->date'")
                    ->where('tks_categories.reportar', 1)
                    ->where('status', '3')
                    ->orderBy('tks_tickets.id', 'ASC')
                    ->get();
        
        // *Los pendientes son todos aquellos tickets que fueron ingresados en el mes actual o bien en el anterior y que aún no tienen fecha de cierre

        $pendientes = DB::connection('tickets')->select("SELECT 
                                                            tk.id,
                                                            tk.trackid,
                                                            tk.status,
                                                            tk.name,
                                                            tk.category,
                                                            tk.subject,
                                                            tk.message,
                                                            DATE_FORMAT(dt, '%d/%m/%Y %H:%i:%s') AS dt,
                                                            DATE_FORMAT(fecha_cierre, '%d/%m/%Y') AS fecha_cierre,
                                                            us.name AS tecnico, 
                                                            tk.registros
                                                        FROM tks_tickets tk
                                                        LEFT JOIN tks_users us
                                                        ON tk.owner = us.id
                                                        WHERE (
                                                            DATE_FORMAT(dt, '%Y-%m') = '$mes_anterior'
                                                            OR DATE_FORMAT(dt, '%Y-%m') = '$data->date'
                                                        )
                                                        AND (
                                                            fecha_cierre IS NULL
                                                        )
                                                        AND category IN (
                                                            SELECT id
                                                            FROM tks_categories 
                                                            WHERE reportar = 1
                                                        )
                                                        ORDER BY tk.id ASC");

        $total_pendientes = (count($ingresados) + count($anteriores)) - count($resueltos);

        $headers = [
            [
                'text' => 'ID',
                'value' => 'id',
                'sortable' => false,
                'width' => '10%'
            ],
            [
                'text' => 'Usuario',
                'value' => 'name',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Fecha',
                'value' => 'dt',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Técnico',
                'value' => 'tecnico',
                'sortable' => false,
                'width' => '25%'
            ],
            [
                'text' => 'Estado',
                'value' => 'estado',
                'sortable' => false,
                'width' => '15%'
            ]
        ];

        $bottom_detail = [
            [
                'text' => 'Anteriores',
                'value' => count($anteriores),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $anteriores
                    ],
                ],
                'component' => 'tables/TableTickets'
                
            ],
            [
                'text' => 'Ingresados',
                'value' => count($ingresados),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $ingresados
                    ],
                ],
                'component' => 'tables/TableTickets'
            ],
            [
                'text' => 'Resueltos',
                'value' => count($resueltos),
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $resueltos
                    ],
                ],
                'component' => 'tables/TableTickets'
            ],
            [
                'text' => 'Pendientes',
                'value' => count($pendientes),
                'component' => 'tables/TableProcesos',
                'detail' => [
                    'table' => [
                        'headers' => $headers,
                        'items' => $pendientes
                    ],
                ],
                'component' => 'tables/TableTickets'
            ]
        ];

        $porcentaje = round((100 - ((count($pendientes) / count($resueltos)) * 100)), 1);

        $response = [
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }
}