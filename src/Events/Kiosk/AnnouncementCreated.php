<?php

namespace Laravel\Spark\Events\Kiosk;

use Laravel\Spark\Announcement;

class AnnouncementCreated
{
    /**
     * The announcement instance.
     *
     * @var \Laravel\Spark\Announcement
     */
    public $announcement;

    /**
     * Create a new event instance.
     *
     * @param  \Laravel\Spark\Announcement  $announcement
     * @return void
     */
    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }
}
