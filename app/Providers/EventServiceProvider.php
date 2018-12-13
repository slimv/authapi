<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Event\Events\Database\Model\Group\GroupCreated' => [
            'App\Event\Listeners\Database\Model\Group\GroupCreatedListener'
        ],
        'App\Event\Events\Database\Model\Group\GroupDeleted' => [
            'App\Event\Listeners\Database\Model\Group\GroupDeletedListener'
        ],
        'App\Event\Events\Database\Model\Group\GroupDeleting' => [
            'App\Event\Listeners\Database\Model\Group\GroupDeletingListener'
        ],
        'App\Event\Events\Database\Model\Group\GroupUpdated' => [
            'App\Event\Listeners\Database\Model\Group\GroupUpdatedListener'
        ],
        'App\Event\Events\Database\Model\Group\GroupRestored' => [
            'App\Event\Listeners\Database\Model\Group\GroupRestoredListener'
        ],

        'Laravel\Passport\Events\AccessTokenCreated' => [
            'App\Event\Listeners\Passport\OnUserLoginListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
