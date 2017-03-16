<?php

namespace MASNathan\LaravelDatabaseSettings;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class LaravelDatabaseSettingsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/settings.php' => config_path('settings.php'),
        ], 'config');

        $this->publishes($this->getMigrationToPublish(), 'migrations');

        if (config('settings') && config('database.default') && DB::connection()->getDatabaseName() && Schema::hasTable(config('settings.database.table'))) {
            $this->app->instance('config.local', $this->app['config']);
            $this->app->instance('config.database', new DatabaseRepository());
            $this->app->instance('config', new RepositoryManager());
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    protected function getMigrationToPublish()
    {
        $migrationMapping = [];
        foreach (glob(__DIR__ . '/../database/migrations/*.php') as $migrationSource) {
            $filename = pathinfo($migrationSource, PATHINFO_BASENAME);

            $explodedFilename = explode('_', $filename, 5);

            $migrationMapping[$migrationSource] = database_path(sprintf("migrations/%s_%s", date('Y_m_d_His'), end($explodedFilename)));
        }

        return $migrationMapping;
    }
}
