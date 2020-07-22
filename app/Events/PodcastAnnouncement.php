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

class PodcastAnnouncement implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $sendId;
    public $content;
    public $account;
    public $data;
    public function __construct($data)
    {
        $this->data   = $data;
        // Redis::publish("announcement_{$id}", json_encode(['content' => $content]));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("publicAnnouncement");
    }

    public function broadcastWith()
    {
        return [
            'channel' => '0',
            'account' => $this->data['account'],
            'content' => $this->data['content'],
            'cdate'   => $this->data['cdate']
        ];
    }
}
