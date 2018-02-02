<?php

namespace Laravel\Spark\Listeners\Teams\Subscription;

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
        if (! Spark::chargesTeamsPerMember()) {
            return;
        }

        $event->team->subscription()->updateQuantity(
            $event->team->users->count()
        );
    }
}
