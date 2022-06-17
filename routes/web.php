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

$router->get('/test_view', function(){

    $result = Ticket::where('id', 9)->get();

    return response()->json($result);

});
