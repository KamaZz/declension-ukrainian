<?php

namespace UkrainianDeclension\Providers;

use Illuminate\Support\ServiceProvider;
use UkrainianDeclension\Contracts\DeclensionerContract;
use UkrainianDeclension\Contracts\DeclensionGroupIdentifierContract;
use UkrainianDeclension\Services\Declensioner;
use UkrainianDeclension\Services\DeclensionGroupIdentifier;

class DeclensionUkrainianServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(DeclensionGroupIdentifierContract::class, DeclensionGroupIdentifier::class);

        $this->app->singleton(DeclensionerContract::class, function ($app) {
            return new Declensioner(
                $app->make(DeclensionGroupIdentifierContract::class)
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
} 