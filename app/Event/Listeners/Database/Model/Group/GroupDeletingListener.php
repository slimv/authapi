<?php

namespace App\Event\Listeners\Database\Model\Group;

use App\Event\Events\Database\Model\Group\GroupDeleting;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class GroupDeletingListener
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
     * @param  GroupDeleting  $event
     * @return void
     */
    public function handle(GroupDeleting $event)
    {
        if($event->group) {
            if($event->group->isForceDeleting()) {
                $closureDeletingResult = $event->group->onClosureModelDeleting();   
            } else {
                
            }
        }
    }
}
