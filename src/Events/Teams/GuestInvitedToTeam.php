<?php

namespace Laravel\Spark\Events\Teams;

class GuestInvitedToTeam
{
    /**
     * The team instance.
     *
     * @var \Laravel\Spark\Team
     */
    public $team;

    /**
     * The invitation instance.
     *
     * @var mixed
     */
    public $invitation;

    /**
     * Create a new event instance.
     *
     * @param  \Laravel\Spark\Team  $team
     * @param  mixed  $invitation
     * @return void
     */
    public function __construct($team, $invitation)
    {
        $this->team = $team;
        $this->invitation = $invitation;
    }
}
