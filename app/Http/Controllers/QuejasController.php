<?php

namespace App\Http\Controllers;

class QuejasController extends Controller{

    public function create($indicador){

        $chart = $this->chart($indicador);

        $total = [
            'total' => [
                'value' => "100%",
                'style' => ["text-h1", "font-weight-bold"],
            ],
            'chart' => $chart
        ];

        $indicador->content = $total;

        $indicador->bottom_detail = $this->bottom_detail();

        return $indicador;

    }

    public function chart($indicador){

        $chart = [
            'type' => "Pie",
            'chartData' => [
                'labels' => ["January", "February", "March"],
                'datasets'=> [
                    [
                        'data' => [90, 10],
                        'backgroundColor' => [
                            'rgb(54, 162, 235)',
                            'rgba(255, 205, 86, 0)'
                        ]
                    ]
                ],
            ],
            'chartOptions' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => [
                        'display' => false,
                    ],
                ],
            ],
        ];

        return $chart;

    }

    public function bottom_detail(){

        $result = [
            [
                "text" => "Total",
                "value" => 605,
            ],
            [
                "text" => "Válidas",
                "value" => 602,
            ],
            [
                "text" => "SNC",
                "value" => 3,
            ],
            [
                "text" => "Rechazadas",
                "value" => 0,
            ],
        ];

        return $result;

    }

}