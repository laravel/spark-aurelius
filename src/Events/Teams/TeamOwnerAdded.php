<?php

namespace Laravel\Spark\Events\Teams;

class TeamOwnerAdded
{
    /**
     * The team instance.
     *
     * @var \Laravel\Spark\Team
     */
    public $team;

    /**
     * The team owner instance.
     *
     * @var mixed
     */
    public $owner;

    /**
     * Create a new event instance.
     *
     * @param  \Laravel\Spark\Team  $team
     * @param  mixed  $owner
     * @return void
     */
    public function __construct($team, $owner)
    {
        $this->team = $team;
        $this->owner = $owner;
    }
}
