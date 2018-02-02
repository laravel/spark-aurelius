<?php

namespace Laravel\Spark\Listeners\Subscription;

use Laravel\Spark\Spark;

class UpdateSubscriptionQuantity
{
    /**
     * Handle the event.
     *
     * @param  mixed  $event
     * @return void
     */
    public function handle($event)
    {
        if (! Spark::chargesUsersPerTeam()) {
            return;
        }

        $event->user->subscription()->updateQuantity(
            max(1, $event->user->ownedTeams()->count())
        );
    }
}
