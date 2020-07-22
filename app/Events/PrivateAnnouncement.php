<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class PrivateAnnouncement implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $channel;
    public $data;
    public function __construct($data, $channel)
    {
        $this->channel = $channel;
        $this->data    = $data;
        echo "Group.{$this->channel}";
        // Redis::publish("Group_{$this->channel}", json_encode(['content' => $data['content']]));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // return new PresenceChannel("Group.{$this->channel}");
        return new Channel("Group.{$this->channel}");
    }

    public function broadcastWith()
    {
        return [
            'channel' => $this->channel,
            'account' => $this->data['account'],
            'content' => $this->data['content'],
            'cdate'   => $this->data['cdate']
        ];
    }
}
