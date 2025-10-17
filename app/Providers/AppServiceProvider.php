<?php

namespace App\Providers;

use App\Services\Ai\AiClientInterface;
use App\Services\Ai\FakeAiClient;
use App\Services\Ai\HttpAiClient;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AiClientInterface::class, function ($app) {
            return match (config('ai.driver')) {
                'fake' => $app->make(FakeAiClient::class),
                default => $app->make(HttpAiClient::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentNamespace('dashboard.components', 'dashboard');
    }
}
