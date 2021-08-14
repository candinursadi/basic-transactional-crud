<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'transactions'], function () use ($router) {
    $router->get('/', ['uses' => 'TransactionController@index', "as" => 'transactions']);
    $router->get('/{id}', ['uses' => 'TransactionController@detail', "as" => 'transactions.detail']);
    $router->post('/', ['uses' => 'TransactionController@store', "as" => 'transactions.store']);
    $router->put('/{id}', ['uses' => 'TransactionController@update', "as" => 'transactions.update']);
    $router->delete('/{id}', ['uses' => 'TransactionController@destroy', "as" => 'transactions.destroy']);
});
