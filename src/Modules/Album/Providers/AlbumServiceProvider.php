<?php

namespace Modules\Album\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Album\Entities\Album;
use Modules\Album\Entities\AlbumFile;
use Modules\Album\Http\Middleware\EnsureAdmin;
use Modules\Album\Policies\AlbumFilePolicy;
use Modules\Album\Policies\AlbumPolicy;

class AlbumServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Album';

    protected string $moduleNameLower = 'album';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        $this->registerPolicies();
        $this->registerMiddlewareAliases();
        $this->mapRoutes();
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');

        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'),
            $this->moduleNameLower
        );
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Album::class, AlbumPolicy::class);
        Gate::policy(AlbumFile::class, AlbumFilePolicy::class);
    }

    protected function registerMiddlewareAliases(): void
    {
        /** @var \Illuminate\Routing\Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('album.admin', EnsureAdmin::class);
    }

    protected function mapRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(module_path($this->moduleName, 'Routes/api.php'));

        Route::middleware('web')
            ->group(module_path($this->moduleName, 'Routes/web.php'));
    }

    public function provides(): array
    {
        return [];
    }
}
