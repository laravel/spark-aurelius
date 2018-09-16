<?php

namespace Laravel\Spark\Listeners\Profile;

class UpdateContactInformationOnStripe
{
    /**
     * Handle the event.
     *
     * @param  \Laravel\Spark\Events\Profile\ContactInformationUpdated  $event
     */
    public function handle($event)
    {
        if ($event->user->hasBillingProvider()) {
            $customer = $event->user->asStripeCustomer();

            $customer->email = $event->user->email;

            $customer->save();
        }
        
        foreach ($event->user->ownedTeams as $team) {
            if ($team->hasBillingProvider()) {
                $customer = $team->asStripeCustomer();

                $customer->email = $event->user->email;

                $customer->save();
            }
        }
    }
}
