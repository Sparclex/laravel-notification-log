<?php

namespace Okaufmann\LaravelNotificationLog;

use Illuminate\Notifications\ChannelManager as BaseChannelManager;
use Illuminate\Support\Facades\Event;
use Okaufmann\LaravelNotificationLog\Listeners\MessageEventListener;
use Okaufmann\LaravelNotificationLog\Manager\ChannelManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelNotificationLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-notification-log')
            ->hasConfigFile()
            ->hasMigrations(['create_notification_logs_sent_notifications_table']);
    }

    public function packageBooted()
    {
        Event::subscribe(MessageEventListener::class);
    }

    public function packageRegistered()
    {
        $this->app->bind(BaseChannelManager::class, ChannelManager::class);
    }
}
