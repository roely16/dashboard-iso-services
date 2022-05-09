<?php

namespace App\Http\Controllers;

class CalidadController extends Controller{

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

        return $indicador;

    }

    public function chart($indicador){

        $chart = [
            'type' => "Pie",
            'chartData' => [
                'labels' => ["January", "February", "March"],
                'datasets'=> [
                    [
                        'data' => [40, 20, 12]
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

}