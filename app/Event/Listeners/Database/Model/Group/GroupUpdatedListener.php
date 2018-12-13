<?php

namespace App\Event\Listeners\Database\Model\Group;

use App\Event\Events\Database\Model\Group\GroupUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class GroupUpdatedListener
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
     * @param  GroupUpdated  $event
     * @return void
     */
    public function handle(GroupUpdated $event)
    {
        if($event->group) {
            $closureUpdatedResult = $event->group->onClosureModelUpdated($event->groupData);   
        }
    }
}
