<?php
/**
 * Event when Group model is created.
 * Group model is working using closure approach (https://coderwall.com/p/lixing/closure-tables-for-browsing-trees-in-sql) which require
 * update into model Rank and Closure data when Group is created or updated.
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

class GroupCreated
{
    use SerializesModels;

    public $group;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
    }
}
