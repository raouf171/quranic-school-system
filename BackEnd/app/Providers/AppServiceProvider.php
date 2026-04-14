<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Interfaces\StudentRepositoryInterface ;
use App\Repositories\Interfaces\HalaqaRepositoryInterface ;
use App\Repositories\Interfaces\PaymentRepositoryInterface ; 


use App\Repositories\PaymentRepository ; 
use App\Repositories\HalaqaRepository ; 
use App\Repositories\StudentRepository ;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            StudentRepositoryInterface::class,
            StudentRepository::class
        );


        $this->app->bind(
            HalaqaRepositoryInterface::class,
            HalaqaRepository::class
        );

        $this->app->bind(
            PaymentRepositoryInterface::class,
            PaymentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
