<?php

namespace Laravel\Spark\Listeners\Teams;

use Laravel\Spark\Spark;
use Laravel\Spark\Events\Teams\UserInvitedToTeam;
use Laravel\Spark\Contracts\Repositories\NotificationRepository;

class CreateInvitationNotification
{
    /**
     * The notification repository instance.
     *
     * @var \Laravel\Spark\Contracts\Repositories\NotificationRepository
     */
    protected $notifications;

    /**
     * Create a new listener instance.
     *
     * @param  \Laravel\Spark\Contracts\Repositories\NotificationRepository  $notifications
     * @return void
     */
    public function __construct(NotificationRepository $notifications)
    {
        $this->notifications = $notifications;
    }

    /**
     * Handle the event.
     *
     * @param  \Laravel\Spark\Events\Teams\UserInvitedToTeam  $event
     * @return void
     */
    public function handle(UserInvitedToTeam $event)
    {
        $this->notifications->create($event->user, [
            'icon' => 'fa-users',
            'body' => __('teams.you_have_been_invited_to_join_team', ['teamName' => $event->team->name]),
            'action_text' => __('View Invitations'),
            'action_url' => '/settings#/'.Spark::teamsPrefix(),
        ]);
    }
}
