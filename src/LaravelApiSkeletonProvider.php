<?php

namespace Stepanenko3\LaravelApiSkeleton;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Sentry\State\HubInterface;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stepanenko3\LaravelApiSkeleton\Commands\ContainerActionMakeCommand;
use Stepanenko3\LaravelApiSkeleton\Commands\ContainerControllerMakeCommand;
use Stepanenko3\LaravelApiSkeleton\Commands\ContainerDtoMakeCommand;
use Stepanenko3\LaravelApiSkeleton\Commands\ContainerModelMakeCommand;
use Stepanenko3\LaravelApiSkeleton\Commands\ContainerResourceMakeCommand;
use Stepanenko3\LaravelApiSkeleton\DTO\DTO;
use Stepanenko3\LaravelApiSkeleton\Http\Schemas\Schema as SchemasSchema;
use Stepanenko3\LaravelApiSkeleton\Services\Performance\ClockworkTracker;
use Stepanenko3\LaravelApiSkeleton\Services\Performance\PerformanceTracker;
use Stepanenko3\LaravelApiSkeleton\Services\Performance\SentryTracker;

class LaravelApiSkeletonProvider extends PackageServiceProvider
{
    public function configurePackage(
        Package $package,
    ): void {
        $package
            ->name('laravel-api-skeleton')
            ->hasConfigFile(['api-skeleton', 'headers'])
            ->hasCommands([
                ContainerModelMakeCommand::class,
                ContainerResourceMakeCommand::class,
                ContainerActionMakeCommand::class,
                ContainerDtoMakeCommand::class,
                ContainerControllerMakeCommand::class,
            ])
            ->hasMigrations([
                'create_otp_table',
            ])
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->startWith(function (InstallCommand $command): void {
                        $command->info('Hello, and welcome to my great new package!');
                    })
                    ->publishConfigFile(['api-skeleton', 'headers'])
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('stepanenko3/laravel-api-skeleton')
                    ->endWith(function (InstallCommand $command): void {
                        $command->info('Have a great day!');
                    });
            });
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(
            abstract: PerformanceTracker::class,
            concrete: function ($app) {
                $sentryTracker = new SentryTracker(
                    $app->make(HubInterface::class),
                );

                $clockworkTracker = new ClockworkTracker();

                return new PerformanceTracker(
                    trackers: [
                        $sentryTracker,
                        $clockworkTracker,
                    ],
                );
            },
        );

        foreach ([
            DTO::class,
            SchemasSchema::class,
        ] as $abstract) {
            $this->app->beforeResolving($abstract, function ($class, $parameters, $app): void {
                if ($app->has($class)) {
                    return;
                }

                $app->bind(
                    $class,
                    fn ($container) => $class::fromRequest($container['request']),
                );
            });
        }
    }

    public function boot(): void
    {
        parent::boot();

        Schema::defaultStringLength(191);

        Carbon::setLocale(config('app.locale'));
        setlocale(LC_TIME, config('app.locale'));

        $this->preventLazyLoading();
        $this->bootMacros();
    }

    private function bootMacros(): void
    {
        Collection::make(glob(__DIR__ . '/Macros/*.php'))
            ->mapWithKeys(
                fn (string $path) => [
                    $path => pathinfo($path, PATHINFO_FILENAME),
                ],
            )
            ->each(
                function ($macro, $path): void {
                    require_once $path;
                },
            );
    }

    private function preventLazyLoading(): void
    {
        Model::preventLazyLoading(!app()->environment('production'));

        // But in production, log the violation instead of throwing an exception.
        if (app()->environment('production')) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation): void {
                $class = $model::class;

                Log::warning("Attempted to lazy load [{$relation}] on model [{$class}].");
            });
        }
    }
}
