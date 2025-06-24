<?php

namespace UkrainianDeclension\Providers;

use Illuminate\Support\ServiceProvider;
use UkrainianDeclension\Contracts\DeclensionerContract;
use UkrainianDeclension\Contracts\DeclensionGroupIdentifierContract;
use UkrainianDeclension\Services\AdjectiveDeclensioner;
use UkrainianDeclension\Services\Declensioner;
use UkrainianDeclension\Services\DeclensionGroupIdentifier;
use UkrainianDeclension\Services\PhraseDeclensioner;

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

        $this->app->singleton(AdjectiveDeclensioner::class, AdjectiveDeclensioner::class);

        $this->app->singleton(DeclensionerContract::class, function ($app) {
            $declensioner = new Declensioner(
                $app->make(DeclensionGroupIdentifierContract::class)
            );

            $phraseDeclensioner = new PhraseDeclensioner(
                $declensioner,
                $app->make(AdjectiveDeclensioner::class)
            );

            $declensioner->setPhraseDeclensioner($phraseDeclensioner);

            return $declensioner;
        });

        $this->app->alias(DeclensionerContract::class, 'declensioner');
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