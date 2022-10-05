<?php 

namespace App\Http\Controllers\Informatica;
use App\Http\Controllers\Controller;

use App\Models\Tickets\Ticket;

use Illuminate\Support\Facades\DB;

use App\Proceso;

class TicketController extends Controller {

    public function create($indicador){

        try {
            
            $data = $indicador->kpi_data;

            // * Validar si es una consulta de un mes posterior o actual 
            $result = (object) app('App\Http\Controllers\ValidationController')->check_case($indicador);

            $result = $result->data ? $result->data : (object) $this->data($data);

            $chart = $this->chart($result);

            $total = [
                'data' => $result,
                'total' => [
                    'value' =>  $result->total,
                ],
                'chart' => $chart
            ];

            $indicador->content = $total;
            $indicador->bottom_detail = $result->bottom_detail;
            $indicador->data = $result;

            return $indicador;

        } catch (\Throwable $th) {
            
            $error = [
                'message' => $th->getMessage()
            ];

            $indicador->error = $error;

            return $indicador;

        }
        
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
        $mes_posterior = date('Y-m', strtotime($data->date . ' +1 month'));
        $dos_meses_adelante = date('Y-m', strtotime($data->date . ' +2 month'));

        // * Los tickets pendientes en el mes actual son aquellos que fueron ingresados en el mes anterior y que fueron finalizados en el mes actual o bien que aún no han sido finalizados
            
        // Tickets pendientes del mes anterior
        $anteriores = DB::connection('tickets')->select("SELECT 
                                                            tk.id,
                                                            tk.trackid,
                                                            tk.name,
                                                            tk.category,
                                                            tk.subject,
                                                            tk.message,
                                                            tk.status,
                                                            DATE_FORMAT(dt, '%d/%m/%Y %H:%i:%s') AS dt,
                                                            DATE_FORMAT(fecha_cierre, '%d/%m/%Y %H:%i:%s') AS fecha_cierre,
                                                            us.name AS tecnico
                                                        FROM tks_tickets tk
                                                        LEFT JOIN tks_users us
                                                        ON tk.owner = us.id
                                                        where ((
                                                            fecha_cierre > '$data->date'
                                                            and fecha_cierre < '$mes_posterior'
                                                            and dt < '$data->date'
                                                        ) or (
                                                            dt < '$data->date'
                                                            and status not in ('3')
                                                            and fecha_cierre is null
                                                        ))
                                                        and category in (
                                                            select id
                                                            from tks_categories 
                                                            where reportar = 1
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
                        'status',
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
                        'status',
                        DB::raw("date_format(dt, '%d/%m/%Y %H:%i:%s') as dt"),
                        'tks_users.name as tecnico',
                        DB::raw("date_format(fecha_cierre, '%d/%m/%Y %H:%i:%s') as fecha_cierre")
                    )
                    ->join('tks_categories', 'tks_tickets.category', '=', 'tks_categories.id')
                    ->join('tks_users', 'tks_tickets.owner', '=', 'tks_users.id')
                    ->whereRaw("date_format(fecha_cierre, '%Y-%m') = '$data->date'")
                    ->where('tks_categories.reportar', 1)
                    // ->where('status', '3')
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
                                                            DATE_FORMAT(fecha_cierre, '%d/%m/%Y %H:%i:%s') AS fecha_cierre,
                                                            us.name AS tecnico, 
                                                            tk.registros
                                                        FROM tks_tickets tk
                                                        LEFT JOIN tks_users us
                                                        ON tk.owner = us.id
                                                        where ((
                                                            fecha_cierre > '$mes_posterior'
                                                            and fecha_cierre < '$dos_meses_adelante'
                                                            and dt < '$mes_posterior'
                                                        ) or (
                                                            dt < '$mes_posterior'
                                                            and status not in ('3')
                                                            and fecha_cierre is null
                                                        ))
                                                        and category in (
                                                            select id
                                                            from tks_categories 
                                                            where reportar = 1
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
                'width' => '15%'
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
                'width' => '15%'
            ],
            [
                'text' => 'Estado',
                'value' => 'estado',
                'sortable' => false,
                'width' => '15%'
            ],
            [
                'text' => 'Estatus',
                'value' => 'status',
                'sortable' => false,
                'width' => '15%'
            ],
            [
                'text' => 'Finalización',
                'value' => 'fecha_cierre',
                'sortable' => false,
                'width' => '25%'
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
                'component' => 'tables/TableTickets',
                'fullscreen' => true
                
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
                'component' => 'tables/TableTickets',
                'fullscreen' => true
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
                'component' => 'tables/TableTickets',
                'fullscreen' => true
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
                'component' => 'tables/TableTickets',
                'fullscreen' => true
            ]
        ];

        $carga_trabajo = count($anteriores) + count($ingresados);

        if ($carga_trabajo > 0) {
            
            $porcentaje = round(count($resueltos) / $carga_trabajo * 100, 1);

        }else{

            $porcentaje = 0;

        }

        $porcentaje = $porcentaje;

        $response = [
            'total' => $porcentaje,
            'bottom_detail' => $bottom_detail
        ];

        return $response;

    }
}