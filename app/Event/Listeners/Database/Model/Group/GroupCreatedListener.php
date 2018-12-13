<?php
/**
 * Handler when Group model is created.
 * Group model is working using closure approach (https://coderwall.com/p/lixing/closure-tables-for-browsing-trees-in-sql) which require
 * update into model Rank and Closure data when Group is created or updated.
 */
namespace App\Event\Listeners\Database\Model\Group;

use App\Event\Events\Database\Model\Group\GroupCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class GroupCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * When model is created we will need to update the Group data include Rank and Closure 
     * For more information look up this article: https://coderwall.com/p/lixing/closure-tables-for-browsing-trees-in-sql
     *
     * @param  GroupCreated  $event
     * @return void
     */
    public function handle(GroupCreated $event)
    {
        if($event->group) {
            $closureGenerateResult = $event->group->onClosureModelCreated();   
        }
    }
}
