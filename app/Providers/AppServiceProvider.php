<?php

namespace App\Providers;

use App\Services\AI\AiClientInterface;
use App\Services\AI\AiRequestLogger;
use App\Services\AI\LmStudioClient;
use App\Services\AI\OpenRouterClient;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiClientInterface::class, function (Application $app): AiClientInterface {
            $config = config('ai');

            if (! is_array($config)) {
                throw new InvalidArgumentException('AI configuration is missing.');
            }

            return match ($config['provider'] ?? null) {
                'openrouter' => new OpenRouterClient($config, $app->make(AiRequestLogger::class)),
                'lmstudio' => new LmStudioClient($config, $app->make(AiRequestLogger::class)),
                default => throw new InvalidArgumentException(
                    'Unsupported AI provider ['.($config['provider'] ?? 'null').'].',
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
