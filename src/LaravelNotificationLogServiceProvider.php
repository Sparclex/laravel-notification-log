<?php

namespace Okaufmann\LaravelNotificationLog;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\ChannelManager as BaseChannelManager;
use Illuminate\Support\Facades\Event;
use Okaufmann\LaravelNotificationLog\Commands\CompressAllMessages;
use Okaufmann\LaravelNotificationLog\Commands\DecompressAllMessages;
use Okaufmann\LaravelNotificationLog\Listeners\MessageEventListener;
use Okaufmann\LaravelNotificationLog\Notifications\ChannelManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelNotificationLogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-notification-log')
            ->hasConfigFile()
            //->hasViews()
            ->hasMigrations(['create_notification_logs_sent_messages_table', 'create_notification_logs_sent_notifications_table'])
            ->hasCommands([
                CompressAllMessages::class,
                DecompressAllMessages::class,
            ]);
    }

    public function packageBooted()
    {
        Event::subscribe(MessageEventListener::class);

        $existingCallback = Mailable::$viewDataCallback;

        Mailable::buildViewDataUsing(function ($mailable) use ($existingCallback) {
            $existingData = $existingCallback ? call_user_func($existingCallback, $mailable) : [];

            return array_merge($existingData, [
                '__laravel_notification_log_mailable' => get_class($mailable),
                '__laravel_notification_log_queued' => in_array(ShouldQueue::class, class_implements($mailable)),
            ]);
        });
    }

    public function packageRegistered()
    {
        $this->app->bind(BaseChannelManager::class, ChannelManager::class);
    }
}
