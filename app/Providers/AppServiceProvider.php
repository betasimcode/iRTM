<?php

namespace App\Providers;

use App\Services\Workspace\WorkspaceContext;
use App\Services\Workspace\WorkspaceContract;
use App\Services\Workspace\WorkspaceResolver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(
            WorkspaceContract::class,
            function ($app) {

                $resolver = $app->make(
                    WorkspaceResolver::class
                );

                $workspace = $resolver->current(
                    $app['auth']->user()
                );

                return new WorkspaceContext(
                    $workspace
                );
            }
        );

        $this->app->scoped(
            \App\Services\TeamCenter\TeamCenterContext::class,
            function () {

                $user = auth()->user();

                return new \App\Services\TeamCenter\TeamCenterContext(
                    user: $user,
                    team: $user->team,
                );
            }
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
