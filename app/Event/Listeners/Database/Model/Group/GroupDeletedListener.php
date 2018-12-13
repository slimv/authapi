<?php
/**
 * Event when Group model is deleted.
 * Group model is working using closure approach (https://coderwall.com/p/lixing/closure-tables-for-browsing-trees-in-sql) which require
 * remove closure data when group is deleted
 */
namespace App\Event\Listeners\Database\Model\Group;

use App\Event\Events\Database\Model\Group\GroupDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class GroupDeletedListener
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
     * @param  GroupDeleted  $event
     * @return void
     */
    public function handle(GroupDeleted $event)
    {
        if($event->group) {
            if($event->group->isForceDeleting()) {
                $closureDeletingResult = $event->group->onClosureModelDeleted();   
            } else {
                $closureDeletingResult = $event->group->onClosureModelSoftDeleted();  
            }
        }
    }
}
