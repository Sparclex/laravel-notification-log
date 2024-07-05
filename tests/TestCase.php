<?php

namespace Okaufmann\LaravelNotificationLog\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Okaufmann\LaravelNotificationLog\LaravelNotificationLogServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'okaufmannn\\LaravelNotificationLog\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->setupDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelNotificationLogServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-notification-log_table.php.stub';
        $migration->up();
        */
    }

    protected function setupDatabase(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $sentMessageLogsTableMigration = require __DIR__.'/../database/migrations/create_sent_message_logs_table.php.stub';

        $sentMessageLogsTableMigration->up();

        $sentNotificationLogsTableMigration = require __DIR__.'/../database/migrations/create_sent_notification_logs_table.php.stub';

        $sentNotificationLogsTableMigration->up();
    }
}
