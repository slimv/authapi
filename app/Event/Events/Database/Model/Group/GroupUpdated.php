<?php
/**
 * Event when Group model is updated.
 * Group model is working using closure approach (https://coderwall.com/p/lixing/closure-tables-for-browsing-trees-in-sql) which require
 * update closure when parent is updated
 */
namespace App\Event\Events\Database\Model\Group;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Model\Group;
use Illuminate\Support\Facades\Log;

class GroupUpdated
{
    use SerializesModels;

    public $group;
    public $groupData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Group $group)
    {
        $this->groupData = $group->getOriginal();
        $this->group = $group;
    }
}
