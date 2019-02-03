<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->header('Authorization')) {
                $key = explode(' ',$request->header('Authorization'));

                $client = new Client();
                $response = $client->get(env('SERVICE_ADDRESS_USER').'/users/validate/'.$key[(count($key)-1)]);
                return $response->getStatusCode()==200;
            }
        });
    }
}
