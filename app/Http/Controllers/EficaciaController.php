<?php

namespace App\Http\Controllers;

class EficaciaController extends Controller{

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
            'type' => "Bar",
            'chartData' => [
                'labels' => ["January", "February", "March"],
                'datasets'=> [
                    [
                        'data' => [40, 20, 12],
                        'backgroundColor' => [
                            "rgb(255, 99, 132)",
                            "rgb(54, 162, 235)",
                            "rgb(255, 205, 86)",
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

}