<?php
/**
 * Event when Group model is deleted.
 * Group model is working using closure approach (https://coderwall.com/p/lixing/closure-tables-for-browsing-trees-in-sql) which require
 * remove closure data when group is deleted
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

class GroupDeleted
{
    use SerializesModels;

    public $group;
    public $groupId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
        $this->groupId = $group->id;
    }
}
