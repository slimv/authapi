<?php

namespace App\Event\Events\Database\Model\Group;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;
use App\Model\Group;

class GroupDeleting
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
