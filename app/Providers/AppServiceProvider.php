<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
//        $this->app->when([CardController::class])
//            ->needs(CardService::class)
//            ->give(function () {
//                $isMailWorking = config('app');
//                if($isMailWorking){
//                    return new CardService(new GeneralMailService());
//                } else {
//                    return new CardService(new FakeGeneralMailService());
//                }
//            });
    }
}
