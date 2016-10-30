<?php

namespace MASNathan\LaravelDatabaseSettings\Tests;

use App\Console\Kernel;
use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use MASNathan\LaravelDatabaseSettings\LaravelDatabaseSettingsServiceProvider;
use Illuminate\Foundation\Testing\TestCase;

abstract class DatabaseTestCase extends TestCase
{

    public function createApplication()
    {
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        // preload the settings.php config file
        $app['config']->set('settings', include __DIR__ .'/../config/settings.php');

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');

        $app->register(LaravelDatabaseSettingsServiceProvider::class);

        return $app;
    }

    /**
     * run package database migrations
     *
     * @return void
     */
    public function migrate()
    {
        $fileSystem = new Filesystem();
        $classFinder = new ClassFinder();

        foreach($fileSystem->files(__DIR__ . "/../database/migrations") as $file)
        {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);

            (new $migrationClass)->up();
        }
    }


    /**
     * Setup the DB before each test.
     */
    public function setUp()
    {
        parent::setUp();

        // This should only do work for Sqlite DBs in memory.
        $this->migrate();

        // We'll run all tests through a transaction,
        // and then rollback afterward.
        DB::beginTransaction();
    }

    /**
     * Rollback transactions after each test.
     */
    public function tearDown()
    {
        DB::rollback();

        parent::tearDown();
    }
}
