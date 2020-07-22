<?php

namespace App\Listeners;

use App\Events\PodcastAnnouncement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PostAnnouncement
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
     * @param  PodcastAnnouncement  $event
     * @return void
     */
    public function handle(PodcastAnnouncement $event)
    {
        //
    }
}
