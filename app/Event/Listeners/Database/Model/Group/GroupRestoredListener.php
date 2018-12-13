<?php

namespace App\Event\Listeners\Database\Model\Group;

use App\Event\Events\Database\Model\Group\GroupRestored;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class GroupRestoredListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  GroupRestored  $event
     * @return void
     */
    public function handle(GroupRestored $event)
    {
        if($event->group) {
            $closureDeletingResult = $event->group->onClosureModelRestored(); 
        }
    }
}
