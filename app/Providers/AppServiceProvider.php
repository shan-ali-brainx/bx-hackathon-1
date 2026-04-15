<?php

namespace App\Providers;

use App\Services\AiModelProcessingService;
use App\Services\ClientBriefParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiModelProcessingService::class);
        $this->app->singleton(ClientBriefParser::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
