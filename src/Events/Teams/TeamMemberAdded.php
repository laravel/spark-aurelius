<?php

namespace Laravel\Spark\Events\Teams;

class TeamMemberAdded
{
    /**
     * The team instance.
     *
     * @var \Laravel\Spark\Team
     */
    public $team;

    /**
     * The team member instance.
     *
     * @var mixed
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  \Laravel\Spark\Team  $team
     * @param  mixed  $user
     * @return void
     */
    public function __construct($team, $user)
    {
        $this->team = $team;
        $this->user = $user;
    }
}
