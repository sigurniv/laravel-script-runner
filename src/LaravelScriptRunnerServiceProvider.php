<?php

namespace Sigurniv\LaravelScriptRunner;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Database\MigrationServiceProvider;
use Sigurniv\LaravelScriptRunner\Console\Migrations\InstallCommand;
use Sigurniv\LaravelScriptRunner\Console\Migrations\MigrateCommand;
use Sigurniv\LaravelScriptRunner\Console\Migrations\MigrateMakeCommand;

class LaravelScriptRunnerServiceProvider extends MigrationServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/config.php', 'script-runner');

        $this->registerRepository();

        $this->registerMigrator();

        $this->registerCreator();

        $this->registerAllCommands();
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__ . '/config/config.php' => config_path('script-runner.php'),
        ]);

        $this->publishes([
            __DIR__ . '/script-runner-migrations' => database_path('script-runner-migrations'),
        ]);
    }

    /**
     * Register the migration repository service.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton('script-runner-migration.repository', function ($app) {
            $table = $app['config']['script-runner.migration_table'];

            return new DatabaseMigrationRepository($app['db'], $table);
        });
    }

    /**
     * Register the migrator service.
     *
     * @return void
     */
    protected function registerMigrator()
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->singleton('script-runner-migrator', function ($app) {
            $repository = $app['script-runner-migration.repository'];

            return new Migrator($repository, $app['db'], $app['files']);
        });
    }

    /**
     * Register the migration creator.
     *
     * @return void
     */
    protected function registerCreator()
    {
        $this->app->singleton('script-runner-migration.creator', function ($app) {
            return new MigrationCreator($app['files']);
        });
    }

    protected function registerAllCommands()
    {
        $this->app->singleton('command.script-runner-migration.install', function ($app) {
            return new InstallCommand($app['script-runner-migration.repository']);
        });

        $this->app->singleton('command.script-runner-migration.migrate', function ($app) {
            return new MigrateCommand($app['script-runner-migrator']);
        });

        $this->app->singleton('command.script-runner-migration.make', function ($app) {
            // Once we have the migration creator registered, we will create the command
            // and inject the creator. The creator is responsible for the actual file
            // creation of the migrations, and may be extended by these developers.
            $creator = $app['script-runner-migration.creator'];

            $composer = $app['composer'];

            return new MigrateMakeCommand($creator, $composer);
        });

        $this->commands('command.script-runner-migration.install');
        $this->commands('command.script-runner-migration.migrate');
        $this->commands('command.script-runner-migration.make');
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'script-runner-migrator',
            'script-runner-migration.repository',
            'script-runner-migration.creator',
        ];
    }
}
