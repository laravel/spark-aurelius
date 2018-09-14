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
        if (! $event->user->hasBillingProvider())
        {
            if (! $event->user->currentTeam->hasBillingProvider())
            {
                return;
            }

            $customer = $event->user->currentTeam->asStripeCustomer();
        }
        
        else
        {
            $customer = $event->user->asStripeCustomer();
        }

        $customer->email = $event->user->email;
        
        $customer->save();
    }
}
