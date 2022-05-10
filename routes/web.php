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

use App\MCAProcesos;

$router->get('/', function () use ($router) {

    return $router->app->version();

});

$router->get('/get_menu', 'HomeController@get_menu');

$router->post('/get_dashboard', 'DashboardController@get_dashboard');

$router->get('/test_view', function(){

    $result = MCAProcesos::where('documento', 353)->get();

    return response()->json($result);

});
