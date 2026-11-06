<?php

namespace App\Providers;

use Aws\CommandInterface;
use Aws\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('filesystems.disks.public.driver') === 's3') {
            // Supabase controls public access at the bucket level, not with object ACLs.
            Storage::disk('public')->getClient()->getHandlerList()->appendInit(
                Middleware::mapCommand(function (CommandInterface $command) {
                    unset($command['ACL']);

                    return $command;
                }), 'omit-object-acl'
            );
        }
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
