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
$router->get('/', function () use ($router) {
	echo "TrackKnowledge User API";
});
$router->get('users',  ['middleware'=>'auth', 'uses' => 'UserController@all']);
$router->get('users/{id}', ['middleware'=>'auth', 'uses' => 'UserController@get']);
$router->post('users', ['uses' => 'UserController@create']);
$router->post('users/authenticate', ['uses' => 'UserController@authenticate']);
$router->get('users/{id}/activate', ['uses' => 'UserController@activate']);
$router->get('users/validate/{id}', ['uses' => 'UserController@testToken']);
