<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Models\Tickets\Ticket;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/get_menu', 'HomeController@get_menu');

$router->post('/get_dashboard', 'DashboardController@get_dashboard');

$router->post('/tecnicos_rechazos', 'Calidad\ConveniosController@tecnicos_rechazos');

$router->post('/chart_rechazo_tecnico', 'Calidad\ConveniosController@chart_rechazo_tecnico');

$router->post('/login_config', 'ConfigController@login');

$router->post('/get_process', 'ConfigController@get_process');

$router->post('/get_preview_data', 'ConfigController@get_preview_data');

$router->post('/save_data', 'ConfigController@save_data');

// * Ruta alternativa para acceder a los datos del dashboard
$router->post('/get_dashboard_config', 'ConfigController@get_dashboard_data');

$router->get('/test_view', function(){

    $result = Ticket::where('id', 9)->get();

    return response()->json($result);

});
